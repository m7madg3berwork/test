<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DailyOrderCustomerController extends Controller
{
    function create(Request $request)
    {
        return view('backend.daily_order_customer.create');
    }

}
