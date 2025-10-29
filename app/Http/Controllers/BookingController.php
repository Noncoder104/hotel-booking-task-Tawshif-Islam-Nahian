<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\RoomCategory;
use Illuminate\Routing\Controller;

class BookingController extends Controller
{
   
    

/**
 * Checks if a room category is available for all days in the range.
 */
private function checkAvailability($categoryId, $fromDate, $toDate)
{
    $start = Carbon::parse($fromDate);
    $end = Carbon::parse($toDate);
    $MAX_ROOMS_PER_CATEGORY = 3; // 3 rooms available per day 

    for ($currentNight = clone $start; $currentNight->lt($end); $currentNight->addDay()) {
        // Count existing bookings that overlap with this specific night
        $bookedRooms = Booking::where('room_category_id', $categoryId)
            ->whereDate('from_date', '<=',$currentNight )
            ->whereDate('to_date', '>', $currentNight)
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
        // Group required string inputs together
    'user_name' => 'required|string|max:255',
    
    // Separate complex regex validation lines
    'email' => ['required', 'email', 'regex:/^.+@.+\..+$/i'], 
    'phone' => ['required', 'string', 'regex:/^(\+)?\d{7,15}$/'], 

    // Group date validation and apply date rules
    'from_date' => ['required', 'date', 'after_or_equal:today'],
    'to_date' => ['required', 'date', 'after:from_date'],
    ]);

    // Calculate prices and check availability for all categories
    $categories = RoomCategory::all()->map(function ($category) use ($data) {
        $category->is_available = $this->checkAvailability(
            $category->id,
            $data['from_date'],
            $data['to_date']
        );
        $category->pricing = $category->calculatePriceForDuration(
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
    $pricing = $category->calculatePriceForDuration($category, $data['from_date'], $data['to_date']);

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
        'final_price' => $pricing['final_price'], // Storing final amount after all surcharge and discounts are applied
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

