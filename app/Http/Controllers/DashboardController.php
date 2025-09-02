<?php

namespace App\Http\Controllers;

use App\Models\Tenant;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $tenantsExist = Tenant::exists();

        return view('dashboard', [
            'tenantsExist' => $tenantsExist,
        ]);
    }
}
