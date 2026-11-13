<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Auth::user()
            ->orders()
            ->with('items')
            ->latest()
            ->get();

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }
}

