<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'date_order',
        'total_price',
        'order_details',
    ];

    protected $casts = [
        'order_details' => 'array',
    ];
}
