<?php

namespace App\Models;
use App\Models\Category;
use App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['image', 'category', 'product'];

    public function category()
{
    return $this->belongsTo(Category::class);
}

public function product()
{
return $this->belongsTo(product::class);
}

}
