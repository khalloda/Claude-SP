<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Config\DB;

class DashboardController extends Controller
{
    public function index(): void
    {
        // Get quick statistics
        $stats = $this->getQuickStats();
        
        $this->view('dashboard/index', compact('stats'));
    }
    
    private function getQuickStats(): array
    {
        $stats = [];
        
        try {
            // Clients count
            $stmt = DB::query("SELECT COUNT(*) as count FROM sp_clients");
            $stats['clients'] = $stmt->fetch()['count'];
            
            // Suppliers count
            $stmt = DB::query("SELECT COUNT(*) as count FROM sp_suppliers");
            $stats['suppliers'] = $stmt->fetch()['count'];
            
            // Products count
            $stmt = DB::query("SELECT COUNT(*) as count FROM sp_products");
            $stats['products'] = $stmt->fetch()['count'];
            
            // Warehouses count
            $stmt = DB::query("SELECT COUNT(*) as count FROM sp_warehouses");
            $stats['warehouses'] = $stmt->fetch()['count'];
            
            // Low stock products (≤ 5 items)
            $stmt = DB::query("SELECT COUNT(*) as count FROM sp_products WHERE total_qty <= 5");
            $stats['low_stock'] = $stmt->fetch()['count'];
            
            // Total inventory value
            $stmt = DB::query("SELECT COALESCE(SUM(cost_price * total_qty), 0) as total_value FROM sp_products");
            $stats['inventory_value'] = $stmt->fetch()['total_value'];
            
        } catch (\Exception $e) {
            // If any query fails, return zeros
            $stats = [
                'clients' => 0,
                'suppliers' => 0,
                'products' => 0,
                'warehouses' => 0,
                'low_stock' => 0,
                'inventory_value' => 0
            ];
        }
        
        return $stats;
    }
}
