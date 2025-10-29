<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Import Carbon for date/time manipulation

class RoomCategory extends Model
{
    use HasFactory;

    // The room categories are seeded, so we don't typically need $fillable
    // or $guarded here unless you plan on editing them via the app.

    /**
     * Calculates the price for a booking duration based on business rules.
     * This method contains the core pricing logic (surcharge and discount).
     */
    public function calculatePriceForDuration($checkInDate, $checkOutDate)
    {
        // Personalized variable names and explicit parsing for robustness
        $checkInDate = Carbon::parse($checkInDate);
        $checkOutDate = Carbon::parse($checkOutDate);
        $totalBasePrice = 0;
        $totalSurchargedPrice = 0;
        $durationInNights = $checkInDate->diffInDays($checkOutDate);

        // 1. Calculate Daily Price (includes weekend surcharge)
        // Safety check: Ensure we only iterate over full nights booked (up to but not including check-out date).
        for ($date = clone $checkInDate; $date->lt($checkOutDate); $date->addDay()) {
            $pricePerNight = $this->base_price; 
            
            // As per requirements 2 and 5, apply the 20% surcharge on Friday and Saturday.
            if ($date->isFriday() || $date->isSaturday()) {
                $pricePerNight *= 1.20; // 120% of base price
            }

            $totalBasePrice += $this->base_price;
            $totalSurchargedPrice += round($pricePerNight);
        }
        
        $finalPrice = $totalSurchargedPrice;

        // 2. Apply Consecutive Nights Discount (10% for 3 or more nights)
        if ($durationInNights >= 3) {
            // Apply 10% discount to the total price (after surcharge)
            $finalPrice *= 0.90; 
        }

        return [
            'nights' => $durationInNights,
            'total_base_price' => round($totalBasePrice),
            'final_price' => round($finalPrice),
        ];
    }
}
