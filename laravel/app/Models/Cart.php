<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grand_total',
    ];
    protected $dates = ['deleted_at'];


    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function cartBooks()
    {
        return $this->hasMany(CartBook::class);
    }
}

