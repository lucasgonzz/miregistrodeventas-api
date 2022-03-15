<?php

namespace App\Http\Controllers;

use App\Subscription;
use App\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    function store(Request $request) {
        $ACCESS_TOKEN="APP_USR-7160319402643293-031013-4359e62dcb943c73711d5b27a6a86b3c-1087431208"; 
        $curl = curl_init(); 
        $fields = [
            'preapproval_plan_id' => $request->preapproval_plan_id,
            'card_token_id' => $request->card_token_id,
            'payer_email' => $request->payer_email
        ];
        $fields_string = json_encode($fields);
        curl_setopt($curl, CURLOPT_URL, 'https://api.mercadopago.com/preapproval');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$ACCESS_TOKEN,
        ]);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $response = curl_exec($curl); //ejecutar CURL
        $json_data = json_decode($response, true);

        $user = User::find($this->userId());
        $user->expired_at = null;
        $user->save();
        foreach ($user->employees as $employee) {
            $employee->expired_at = null;
            $employee->save();
        }
        // return response()->json(['ok' => false, 'response' => $json_data], 200);
        $message = $this->checkPaymentStatus($json_data);
        if ($json_data['status'] == 'authorized') {
            $subscription = Subscription::create([
                'preapproval_id' => $json_data['id'],
                'preapproval_plan_id' => $json_data['preapproval_plan_id'],
                'status' => $json_data['status'],
                'payer_email' => $json_data['payer_email'],
                'user_id' => $this->userId(),
            ]);
            return response()->json(['ok' => true, 'message' => $message, 'response' => $json_data, 'subscription' => $subscription], 200);
        } else {
            return response()->json(['ok' => false, 'message' => $message, 'response' => $json_data], 200);
        }
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
