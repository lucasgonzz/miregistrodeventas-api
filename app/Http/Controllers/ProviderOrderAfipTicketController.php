<?php

namespace App\Http\Controllers;

use App\ProviderOrderAfipTicket;
use Illuminate\Http\Request;

class ProviderOrderAfipTicketController extends Controller
{
    function destroy($id) {
        $model = ProviderOrderAfipTicket::find($id);
        $model->delete();
        return response(null, 200);
    }
}
