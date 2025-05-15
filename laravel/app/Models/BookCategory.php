<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookCategory extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'category_id',
    ];
    protected $dates = ['deleted_at'];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_categories');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class ,'book_categories');
    }


}
