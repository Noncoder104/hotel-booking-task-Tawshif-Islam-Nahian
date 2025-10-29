<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; 
class RoomCategory extends Model
{
    use HasFactory;

    
// core pricing logic (surcharge and discount)
    
    public function calculatePriceForDuration($checkInDate, $checkOutDate)
    {
        
        $checkInDate = Carbon::parse($checkInDate);
        $checkOutDate = Carbon::parse($checkOutDate);
        $totalBasePrice = 0;
        $totalSurchargedPrice = 0;
        $durationInNights = $checkInDate->diffInDays($checkOutDate);

        //calculate daily price including weekemd surcharge
        
        for ($date = clone $checkInDate; $date->lt($checkOutDate); $date->addDay()) {
            $pricePerNight = $this->base_price; 
            
            // as per requirements 2 and 5, apply the 20% surcharge on Friday and Saturday.
            if ($date->isFriday() || $date->isSaturday()) {
                $pricePerNight *= 1.20; 
            }

            $totalBasePrice += $this->base_price;
            $totalSurchargedPrice += round($pricePerNight);
        }
        
        $finalPrice = $totalSurchargedPrice;

        //apply consecutive night discount logic
        if ($durationInNights >= 3) {
    
            $finalPrice *= 0.90; 
        }

        return [
            'nights' => $durationInNights,
            'total_base_price' => round($totalBasePrice),
            'final_price' => round($finalPrice),
        ];
    }
}
