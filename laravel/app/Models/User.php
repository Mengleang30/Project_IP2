<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CouponUser;
use App\Models\Transaction;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function couponUsers()
    {
        return $this->hasMany(CouponUser::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function cart() {
        return $this->hasOne(Cart::class);
    }

    // relationship with books for review
    // public function reviews()
    // {
    //     return $this->hasMany(Review::class);
    // }

}
