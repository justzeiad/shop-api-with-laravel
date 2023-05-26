<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'old_price',
        'price',
        'image',
        'category_id',
        'pro_count',
        'discount',
        'description',

    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
