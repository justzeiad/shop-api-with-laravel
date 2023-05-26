<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getSetting()
    {
        $setting = Setting::first();

        if (!$setting) {
            return response()->json([
                'status' => false,
                'message' => 'Setting not found',
                'data' => null
            ], 404);
        }
        
        try {
            $setting = Setting::firstOrFail();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve setting',
                'data' => null
            ], 500);
        }
        
    
        return response()->json([
            'status' => true,
            'message' => null,
            'data' => [
                'about' => $setting->about,
                'terms' => $setting->terms,
            ]
        ], 200);
    }
}
