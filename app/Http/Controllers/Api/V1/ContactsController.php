<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactsController extends Controller
{
    public function getContacts()
    {
        $contacts = Contact::paginate(9);

        $data = [
            'current_page' => $contacts->currentPage(),
            'data' => $contacts->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'type' => $contact->type,
                    'value' => $contact->value,
                    'image' => $contact->image_url,
                ];
            }),
            'first_page_url' => $contacts->url(1),
            'from' => $contacts->firstItem(),
            'last_page' => $contacts->lastPage(),
            'last_page_url' => $contacts->url($contacts->lastPage()),
            'next_page_url' => $contacts->nextPageUrl(),
            'path' => $contacts->url($contacts->currentPage()),
            'per_page' => $contacts->perPage(),
            'prev_page_url' => $contacts->previousPageUrl(),
            'to' => $contacts->lastItem(),
            'total' => $contacts->total(),
        ];

        return response()->json(['status' => true,'message' => null,'data' => $data]);
    }
}
