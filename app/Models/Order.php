<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderDetail;

class Order extends Model
{
    use HasFactory;
    const PENDING = 'pending';
    const REJECTED = 'rejected';
    const APPROVED = 'approved';

    function order_details(){
        return $this->hasMany(OrderDetail::class);
    }
}
