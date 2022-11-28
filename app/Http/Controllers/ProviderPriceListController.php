<?php

namespace App\Http\Controllers;

use App\ProviderPriceList;
use Illuminate\Http\Request;

class ProviderPriceListController extends Controller
{
    function destroy($id) {
        $model = ProviderPriceList::find($id);
        $model->delete();
        return response(null, 200);
    }
}
