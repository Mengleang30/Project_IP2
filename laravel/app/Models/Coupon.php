<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'code',
        'discount',
        'start_date',
        'end_date',
        'use_limit',
        'is_active',
    ];

    public function couponUsers()
    {
        return $this->hasMany(CouponUser::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_users');
    }
    public function getDiscountAttribute($value)
    {
        return $value . '%';
    }
}
