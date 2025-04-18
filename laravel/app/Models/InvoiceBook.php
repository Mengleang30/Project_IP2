<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'book_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
