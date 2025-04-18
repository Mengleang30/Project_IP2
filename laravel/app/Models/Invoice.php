<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payment_method',
        'invoice_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
