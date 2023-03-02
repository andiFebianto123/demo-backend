<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'price',
        'quantity',
        'payment_amount',
        'product_id',
    ];

    function product(){
        return $this->belongsTo(Product::class);
    }
}
