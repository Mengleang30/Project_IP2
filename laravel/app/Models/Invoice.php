<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payment_method',
        'invoice_date',
    ];
    protected $dates = ['deleted_at'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
