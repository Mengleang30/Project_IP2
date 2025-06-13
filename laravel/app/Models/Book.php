<?php

namespace App\Models;
use Carbon\Carbon;
use Dom\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\Fluent\Concerns\Has;

class Book extends Model
{
    use HasFactory, SoftDeletes;
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
    protected $dates = ['deleted_at'];


    protected $casts = [
        'languages' => 'array',
    ];




    // Relationships
    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class, 'book_categories', 'book_id', 'category_id');
    // }
    public function category(){
        return $this->belongsTo(Category::class);
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

    public function comments()
    {
        return $this->hasMany(Comment::class);
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
