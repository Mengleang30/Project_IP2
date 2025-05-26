<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Payment;
use App\Models\CouponUser;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Model
{
    use HasApiTokens , HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'picture',
        'google_id',
    ];
    protected $dates = ['deleted_at'];


    protected $hidden = [
        'password',
    ];

    // public function isAdmin()
    // {
    //     return $this->role === 'admin';
    // }
    // public function isUser()
    // {
    //     return $this->role === 'user';
    // }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    // public function couponUsers()
    // {
    //     return $this->hasMany(CouponUser::class);
    // }
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_customers')
            ->withPivot('used_times', 'limit')
            ->withTimestamps();
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function cart() {
        return $this->hasOne(Cart::class);
    }
    public function wishlist()
{
    return $this->hasMany(Wishlist::class);
}
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }


    // relationship with books for review
    // public function reviews()
    // {
    //     return $this->hasMany(Review::class);
    // }

}
