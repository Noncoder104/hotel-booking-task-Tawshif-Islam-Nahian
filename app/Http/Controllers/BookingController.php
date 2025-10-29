<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\RoomCategory;
use Illuminate\Routing\Controller;

class BookingController extends Controller
{
   
    

// room category cjecking
private function checkAvailability($categoryId, $fromDate, $toDate) 
{
    
    $start_date_obj = $fromDate; 
    $end_date_obj = $toDate;     
    $MAX_ROOMS_PER_CATEGORY = 3; 

    
    for ($currentNight = clone $start_date_obj; $currentNight->lt($end_date_obj); $currentNight->addDay()) {
        
        $bookedRooms = Booking::where('room_category_id', $categoryId)
            ->whereDate('from_date', '<=',$currentNight )
            ->whereDate('to_date', '>', $currentNight)
            ->count();

        if ($bookedRooms >= $MAX_ROOMS_PER_CATEGORY) {
            return false;
        }
    }
    return true;
}
// initial login page: user provides credentials
public function create()
{
    return view('booking.create');
}

// Search: system shows avilable rooms with prices and updated prices 
public function search(Request $request)
{
    // Validation 
    $data = $request->validate([
        
    'user_name' => 'required|string|max:255',
    
    // bit complex regex validation lines
    'email' => ['required', 'email', 'regex:/^.+@.+\..+$/i'], 
    'phone' => ['required', 'string', 'regex:/^(\+)?\d{7,15}$/'], 

    // date validation and apply date rules
    'from_date' => ['required', 'date', 'after_or_equal:today'],
    'to_date' => ['required', 'date', 'after:from_date'],
    ]);

    // calculate room prices as per tje task given
    $categories = RoomCategory::all()->map(function ($category) use ($data) {
    
    $checkInCarbon = Carbon::parse($data['from_date']);
    $checkOutCarbon = Carbon::parse($data['to_date']);
    
    $category->is_available = $this->checkAvailability(
        $category->id,
        $checkInCarbon,
        $checkOutCarbon
    );
    
    // price calculation
    $category->pricing = $category->calculatePriceForDuration(
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

// Store: User confirms the booking 
public function store(Request $request)
{
    // Validating the final selection and user data
    $data = $request->validate([
        'room_category_id' => 'required|exists:room_categories,id',
        'user_name' => 'required|string|max:255',
        'email' => ['required', 'email', 'regex:/^.+@.+\..+$/i'],
        'phone' => ['required', 'string', 'regex:/^(\+)?\d{7,15}$/'],
        'from_date' => 'required|date|after_or_equal:today',
        'to_date' => 'required|date|after:from_date',
    ]);

    $category = RoomCategory::findOrFail($data['room_category_id']);
    $pricing = $category->calculatePriceForDuration(
    $data['from_date'], 
    $data['to_date']
);
    $checkInCarbon = Carbon::parse($data['from_date']);
    $checkOutCarbon = Carbon::parse($data['to_date']);
    // Final availability check 
    if (!$this->checkAvailability($category->id,$checkInCarbon, $checkOutCarbon)) {
        
        return back()->withErrors(['error' => 'No room available.']);
    }

    // creating the booking record
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

    
    return redirect()->route('booking.thankyou', $booking);
}

// Thank You page
public function thankyou(Booking $booking)
{
    $booking->load('category');
    return view('booking.thankyou', compact('booking'));
}
}

