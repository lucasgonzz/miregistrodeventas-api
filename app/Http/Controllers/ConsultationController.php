<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Consultation;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    function index() {
    	return Consultation::where('user_id', Auth::user()->id)
    						->doesnthave('answer')
    						->orderBy('id', 'DESC')
    						->with('article.images')
    						->get();
    }
}
