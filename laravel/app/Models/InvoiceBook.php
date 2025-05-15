<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'book_id',
        'quantity',
        'unit_price',
        'total_price',
    ];
    protected $dates = ['deleted_at'];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
