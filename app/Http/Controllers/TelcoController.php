<?php

namespace App\Http\Controllers;

use App\Integrations\Api\Telco;
use Illuminate\Http\Request as Request;

class TelcoController extends Controller {

    protected $telco;

    public function __construct(Telco $telco)
    {
        $this->telco = $telco;
    }

    /**
     * Send message
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        // Validate body
        $this->validate($request, [
            'reply_to' => 'required|string',
            'message' => 'required|string'
        ]);
        $data = $request->json()->all();

        $response = $this->telco->sendMessage($data);

        return response()->json($response, 200);
    }
}