<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerController extends Controller
{
    public function getBanners()
    {
        $banners = Banner::with(['category', 'product'])->get();


        foreach ($banners as $banner) {
            $data[] = [
                'id' => $banner->id,
                'image' => $banner->image,
                'category' => $banner->category ? [
                    'id' => $banner->category->id,
                    'image' => $banner->category->image,
                    'name' => $banner->category->name,
                ] : null,
                'product' => $banner->product ? [
                    'id' => $banner->product->id,
                    'name' => $banner->product->name,
                    'price' => $banner->product->price,
                ] : null,
            ];
        }

        return [
            'status' => true,
            'message' => null,
            'data' => $data,
        ];
    }
}
