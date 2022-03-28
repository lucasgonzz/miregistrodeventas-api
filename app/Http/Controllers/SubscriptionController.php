<?php

namespace App\Http\Controllers;

use App\Plan;
use App\Subscription;
use App\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    function store(Request $request) {
        $ACCESS_TOKEN="APP_USR-3668585670354328-100112-aaf8232034f567d14919bc3e1c9234f4-163250661"; 
        $curl = curl_init(); 
        $fields = [
            'preapproval_plan_id' => $request->preapproval_plan_id,
            'card_token_id' => $request->card_token_id,
            'payer_email' => $request->payer_email
        ];
        curl_setopt($curl, CURLOPT_URL, 'https://api.mercadopago.com/preapproval');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$ACCESS_TOKEN,
        ]);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $response = curl_exec($curl);
        $json_data = json_decode($response, true);

        $user = User::find($this->userId());
        $user->expired_at = null;
        $user->save();
        foreach ($user->employees as $employee) {
            $employee->expired_at = null;
            $employee->save();
        }
        $message = $this->checkPaymentStatus($json_data);
        if ($json_data['status'] == 'authorized') {
            $actual_subscription = Subscription::where('user_id', $this->userId())->first();
            if (!is_null($actual_subscription)) {
                $this->cancelActualSubscription();
            }
            $subscription = Subscription::create([
                'preapproval_id' => $json_data['id'],
                'preapproval_plan_id' => $json_data['preapproval_plan_id'],
                'status' => $json_data['status'],
                'payer_email' => $json_data['payer_email'],
                'user_id' => $this->userId(),
            ]);
            $plan = Plan::where('preapproval_plan_id', $json_data['preapproval_plan_id'])->first();
            $user->plan_id = $plan->id;
            return response()->json(['ok' => true, 'message' => $message, 'response' => $json_data, 'subscription' => $subscription], 200);
        } else {
            return response()->json(['ok' => false, 'message' => $message, 'response' => $json_data], 200);
        }
    }

    function deleteAll() {
        $subscriptions = Subscription::all();
        foreach ($subscriptions as $subscription) {
            $this->cancelActualSubscription($subscription);
        } 
    }

    function subscriptionsFromPlan($plan_id) {
        $ACCESS_TOKEN="APP_USR-3668585670354328-100112-aaf8232034f567d14919bc3e1c9234f4-163250661"; 
        $curl = curl_init(); 
        $plan = Plan::find($plan_id);
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/preapproval/search?preapproval_plan_id='.$plan->preapproval_plan_id.'&sort=date_created:desc',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$ACCESS_TOKEN,
            ],
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ]);
        $response = curl_exec($curl); 
        $json_data = json_decode($response, true);
        return response()->json(['subscriptions' => $json_data['results']], 200);
    }

    function cancelActualSubscription($subscription = null) {
        $ACCESS_TOKEN="APP_USR-3668585670354328-100112-aaf8232034f567d14919bc3e1c9234f4-163250661"; 
        $curl = curl_init(); 
        $fields = ['status' => 'cancelled'];
        if (is_null($subscription)) {
            $subscription = Subscription::where('user_id', $this->userId())->first();
        }
        $preapproval_id = $subscription->preapproval_id;
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/preapproval/'.$preapproval_id,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$ACCESS_TOKEN,
            ],
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ]);
        $response = curl_exec($curl); 
        $json_data = json_decode($response, true);
        $subscription->delete();
    }

    function delete() {
        $this->cancelActualSubscription();
        $user = Auth()->user();
        $user->delete();
        return response(null, 200);
    }

    function checkPaymentStatus($data) {
        $message = '';
        if ($data['status'] == 'authorized') {
            $message = '¡Listo! Se acreditó tu pago.';
        } else if ($data['status'] == 400) {
            $message = 'Error, intentelo de nuevo, por favor';
        } else {
            switch ($data['code']) {
                case "accredited": 
                    $message = '¡Listo! Se acreditó tu pago.';
                    break; 
                case "cc_rejected_insufficient_amount": 
                    $message = 'Tu tarjeta no tiene fondos suficientes.';
                    break; 
                case "pending_contingency": 
                    $message = 'Estamos procesando tu pago. No te preocupes, menos de 2 días hábiles te avisaremos por e-mail si se acreditó.';
                    break; 
                case "pending_review_manual": 
                    $message = 'Estamos procesando tu pago. No te preocupes, menos de 2 días hábiles te avisaremos por e-mail si se acreditó o si necesitamos más información.';
                    break; 
                case "cc_rejected_bad_filled_card_number": 
                    $message = 'Revisa el número de tarjeta.';
                    break; 
                case "cc_rejected_bad_filled_date": 
                    $message = 'Revisa la fecha de vencimiento.';
                    break; 
                case "cc_rejected_bad_filled_other": 
                    $message = 'Revisa los datos.';
                    break; 
                case "cc_rejected_bad_filled_security_code": 
                    $message = 'Revisa el código de seguridad de la tarjeta.';
                    break; 
                case "cc_rejected_blacklist": 
                    $message = 'No pudimos procesar tu pago.';
                    break; 
                case "cc_rejected_call_for_authorize": 
                    $message = 'Debes autorizar ante tu banco el pago.';
                    break; 
                case "cc_rejected_card_disabled": 
                    $message = 'Llama a tu banco para activar tu tarjeta o usa otro medio de pago.';
                    break; 
                case "cc_rejected_card_error": 
                    $message = 'No pudimos procesar tu pago.';
                    break; 
                case "cc_rejected_duplicated_payment": 
                    $message = 'Ya hiciste un pago por ese valor. Si necesitas volver a pagar usa otra tarjeta u otro medio de pago.';
                    break; 
                case "cc_rejected_high_risk": 
                    $message = 'Tu pago fue rechazado. Elige otro de los medios de pago, te recomendamos con medios en efectivo.';
                    break; 
                case "cc_rejected_invalid_installments": 
                    $message = 'Tu tarjeta no procesa pagos en esas cuotas.';
                    break; 
                case "cc_rejected_max_attempts": 
                    $message = 'Llegaste al límite de intentos permitidos. Elige otra tarjeta u otro medio de pago.';
                    break; 
                case "cc_rejected_other_reason": 
                    $message = 'Tu Tarjeta no procesó el pago.';
                    break; 
            }   
        }
        return $message;
    }
}
