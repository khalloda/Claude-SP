<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        // TODO: Add dashboard statistics in future phases
        $stats = [
            'clients' => 0,
            'products' => 0,
            'quotes' => 0,
            'invoices' => 0
        ];
        
        $this->view('dashboard/index', compact('stats'));
    }
}
