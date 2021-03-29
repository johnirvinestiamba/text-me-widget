<?php

namespace App\Http\Controllers;

use App\Integrations\Api\TextMe;
use Illuminate\Http\Request as Request;

class TextMeController extends Controller {

    protected $textMe;

    public function __construct(TextMe $textMe)
    {
        $this->textMe = $textMe;
    }

    /**
     * Send message
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        $this->validate($request, [
            'reply_to' => 'required|string',
            'message' => 'required|string',
            'destination' => 'required|string|regex:/^[^\s@]+@[^\s@]+$/'
        ]);
        $data = $request->json()->all();

        // Check if destination's domain is on the list
        if (!in_array(explode('@', $data['destination'])[1], config('domains.text_me'))) {
            return response()->json(['error' => 'Invalid destination.'], 400);
        }

        $message = 'This phone number is requesting to communicate via SMS' . "\n";
        $message = $message . 'Phone number: ' . $data['reply_to'] . "\n";
        $message = $message . 'About this topic: ' .  $data['message'];

        $data['message'] = $message;

        $response = $this->textMe->sendMessage($data);

        return response()->json($response, 200);
    }
}