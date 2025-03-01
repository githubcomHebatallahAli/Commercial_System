<?php

namespace App\Http\Controllers\User;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;

class ContactCreateController extends Controller
{
    public function createContactUs(ContactRequest $request)
{
       $Contact =Contact::create ([
            "name" => $request->name,
            "phoneNumber" => $request->phoneNumber,
            "message" => $request->message,
        ]);
       $Contact->save();
       return response()->json([
        'data' =>new ContactResource($Contact),
        'message' => "Contact Created Successfully."
    ]);

    }
}
