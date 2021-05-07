<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

	function store($customer) {
		return Customer::create([
			'customer_id' => $customer->id,
			'email' => $customer->email,
		]);
	}

}
