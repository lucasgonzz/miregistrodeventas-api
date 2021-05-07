<?php

namespace App\Http\Controllers\Helpers;

use App\Buyer;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class TwilioHelper {

    static function sendNotification($buyer_id, $title, $message) {
        $buyers = Buyer::where('id', $buyer_id)
                        ->whereNotNull('notification_id');
        $identities = $buyers->pluck('notification_id')->toArray();
        $client = new Client(getenv('TWILIO_API_KEY'), getenv('TWILIO_API_SECRET'),
            getenv('TWILIO_ACCOUNT_SID'));
        try {
            $n = $client->notify->v1->services(getenv('TWILIO_NOTIFY_SERVICE_SID'))
                ->notifications
                ->create([
                    'title' => $title,
                    'body' => $message,
                    'identity' => $identities
                ]);
            Log::info($n->sid);
        } catch (TwilioException $e) {
            Log::error($e);
        }    
    }

}
