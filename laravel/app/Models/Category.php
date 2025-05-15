<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'name',
    ];
    protected $dates = ['deleted_at'];


    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_categories','category_id', 'book_id');
    }
    public function bookCategories()
    {
        return $this->hasMany(BookCategory::class);
    }
}
