<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\RoomCategory;
use Illuminate\Routing\Controller;

class BookingController extends Controller
{
    private function calculatePrice(RoomCategory $category, $fromDate, $toDate)
{
    $start = Carbon::parse($fromDate);
    $end = Carbon::parse($toDate);
    $totalBasePrice = 0;
    $totalSurchargedPrice = 0;
    $nights = $start->diffInDays($end); // Get number of nights

    // Calculate price per day, applying weekday/weekend rules [cite: 16]
    for ($date = clone $start; $date->lt($end); $date->addDay()) {
        $basePrice = $category->base_price;
        $surcharge = 0;

        // Apply weekend pricing rule: Friday and Saturday increase by 20% [cite: 12, 23]
        if ($date->isFriday() || $date->isSaturday()) {
            $surcharge = $basePrice * 0.20;
        }

        $totalBasePrice += $basePrice;
        $totalSurchargedPrice += ($basePrice + $surcharge);
    }

    $finalPrice = $totalSurchargedPrice;

    // Apply 10% discount for 3 or more consecutive nights 
    if ($nights >= 3) {
        $discountAmount = $totalSurchargedPrice * 0.10;
        $finalPrice -= $discountAmount;
    }

    return [
        'nights' => $nights,
        'total_base_price' => round($totalBasePrice), // Base price must be shown [cite: 25]
        'final_price' => round($finalPrice),         // Final price must be shown [cite: 25]
    ];
}

/**
 * Checks if a room category is available for all days in the range.
 */
private function checkAvailability($categoryId, $fromDate, $toDate)
{
    $start = Carbon::parse($fromDate);
    $end = Carbon::parse($toDate);
    $roomLimit = 3; // 3 rooms available per day 

    for ($date = clone $start; $date->lt($end); $date->addDay()) {
        // Count existing bookings that overlap with this specific night
        $bookedRooms = Booking::where('room_category_id', $categoryId)
            ->whereDate('from_date', '<=', $date)
            ->whereDate('to_date', '>', $date)
            ->count();

        // If all 3 rooms are booked for this date [cite: 19]
        if ($bookedRooms >= $roomLimit) {
            return false;
        }
    }
    return true;
}
// 1. Initial form: User provides Name, Email, Phone, Dates [cite: 27, 28]
public function create()
{
    return view('booking.create');
}

// 2. Search: System shows available room categories with updated prices [cite: 29]
public function search(Request $request)
{
    // Validation [cite: 13, 14]
    $data = $request->validate([
        'user_name' => 'required|string|max:255',
        'email' => ['required', 'email', 'regex:/^.+@.+\..+$/i'], // Basic regex validation [cite: 13]
        'phone' => ['required', 'string', 'regex:/^(\+)?\d{7,15}$/'], // Basic regex validation [cite: 13]
        'from_date' => 'required|date|after_or_equal:today', // Cannot be in the past [cite: 14]
        'to_date' => 'required|date|after:from_date',
    ]);

    // Calculate prices and check availability for all categories
    $categories = RoomCategory::all()->map(function ($category) use ($data) {
        $category->is_available = $this->checkAvailability(
            $category->id,
            $data['from_date'],
            $data['to_date']
        );
        $category->pricing = $this->calculatePrice(
            $category,
            $data['from_date'],
            $data['to_date']
        );
        return $category;
    });

    return view('booking.results', [
        'categories' => $categories,
        'bookingData' => $data,
    ]);
}

// 3. Store: User confirms the booking [cite: 32]
public function store(Request $request)
{
    // Validate the final selection and user data
    $data = $request->validate([
        'room_category_id' => 'required|exists:room_categories,id',
        'user_name' => 'required|string|max:255',
        'email' => ['required', 'email', 'regex:/^.+@.+\..+$/i'],
        'phone' => ['required', 'string', 'regex:/^(\+)?\d{7,15}$/'],
        'from_date' => 'required|date|after_or_equal:today',
        'to_date' => 'required|date|after:from_date',
    ]);

    $category = RoomCategory::findOrFail($data['room_category_id']);
    $pricing = $this->calculatePrice($category, $data['from_date'], $data['to_date']);

    // Final availability check (critical check)
    if (!$this->checkAvailability($category->id, $data['from_date'], $data['to_date'])) {
        // If availability check fails, users should see "No room available." [cite: 19]
        return back()->withErrors(['error' => 'No room available.']);
    }

    // Create the booking record
    $booking = Booking::create([
        'room_category_id' => $category->id,
        'user_name' => $data['user_name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'from_date' => $data['from_date'],
        'to_date' => $data['to_date'],
        'total_base_price' => $pricing['total_base_price'],
        'final_price' => $pricing['final_price'],
    ]);

    // After booking, the user is redirected to a Thank You page [cite: 33]
    return redirect()->route('booking.thankyou', $booking);
}

// 4. Thank You page: Displays booking details [cite: 33, 25]
public function thankyou(Booking $booking)
{
    $booking->load('category');
    return view('booking.thankyou', compact('booking'));
}
}

