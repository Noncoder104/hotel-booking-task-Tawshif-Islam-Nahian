<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    // Add the fillable property listing all columns you assign in the Controller
    protected $fillable = [
        'room_category_id',
        'user_name',
        'email',
        'phone',
        'from_date',
        'to_date',
        'total_base_price',
        'final_price',
    ];

    // Define the relationship to RoomCategory
    public function category()
    {
        return $this->belongsTo(RoomCategory::class, 'room_category_id');
    }
}
