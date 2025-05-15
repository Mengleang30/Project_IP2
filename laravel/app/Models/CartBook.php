<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartBook extends Model
{
    use HasFactory;
    protected $fillable = [
        'cart_id',
        'book_id',
        'quantity',
        'price',

    ];
    protected $dates = ['deleted_at'];


    // Relationships
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
