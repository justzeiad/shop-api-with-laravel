<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Faqs;

class faqsController extends Controller
{
    public function getFaqs()
    {
        $faqs = Faqs::paginate(5);

        return response()->json([
            'status' => true,
            'message' => null,
            'data' => $faqs,

            'first_page_url' => $faqs->url(1),
            'from' => $faqs->firstItem(),
            'last_page' => $faqs->lastPage(),
            'last_page_url' => $faqs->url($faqs->lastPage()),
            'next_page_url' => $faqs->nextPageUrl(),
            'path' => $faqs->url($faqs->currentPage()),
            'per_page' => $faqs->perPage(),
            'prev_page_url' => $faqs->previousPageUrl(),
            'to' => $faqs->lastItem(),
            'total' => $faqs->total(),
        ]);
    }
}
