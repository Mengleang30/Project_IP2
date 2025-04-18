<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'author',
        'published_date',
        'price',
        'category_id',
        'discount',
        'quantity',
        'language',
        'url_image',
        'path_image',
        'description',
    ];

    protected $casts = [
        'languages' => 'array',
    ];




    // Relationships
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'book_categories');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function orderBooks()
    {
        return $this->hasMany(OrderBook::class);
    }
    public function invoiceBooks()
    {
        return $this->hasMany(InvoiceBook::class);
    }

    // Accessors and mutators
    public function getDiscountedPriceAttribute()
    {
        return $this->price - ($this->price * ($this->discount / 100));
    }
    // public function getPublishedDateAttribute($value)
    // {
    //     return Carbon::parse($value)->format('Y-m-d');
    // }
    // public function setPublishedDateAttribute($value)
    // {
    //     $this->attributes['published_date'] = Carbon::createFromFormat('Y-m-d', $value)->format('d-m-Y');
    // }



}
