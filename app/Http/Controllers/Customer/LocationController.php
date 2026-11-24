<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    public function index()
    {
        $store   = current_store();
        $address = urlencode((string) $store->address);

        return view('customer.location', [
            'store'      => $store,
            'embed'      => "https://www.google.com/maps?q={$address}&output=embed",
            'directions' => "https://www.google.com/maps/dir/?api=1&destination={$address}",
            'hours'      => $store->opening_hours ?? [],
            'days'       => \App\Http\Controllers\Merchant\SettingController::DAYS,
        ]);
    }
}