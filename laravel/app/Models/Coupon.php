<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    //
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'code',
        'discount',
        'start_date',
        'end_date',
        'usage_limit',
        'is_active',
    ];
    protected $dates = ['deleted_at'];


    public function users(){
        return $this->belongsToMany(User::class, 'coupon_customer')
            ->withPivot('used_times', 'limit')
            ->withTimestamps();
    }
    public function couponUsers()
    {
        return $this->hasMany(CouponUser::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'coupon_users');
    // }
    // public function getDiscountAttribute($value)
    // {
    //     return $value . '%';
    // }
}
