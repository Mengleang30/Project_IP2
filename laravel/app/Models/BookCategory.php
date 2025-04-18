<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'book_id',
        'category_id',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_categories');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class ,'book_categories');
    }


}
