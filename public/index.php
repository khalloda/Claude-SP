<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.dashboard') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title"><?= I18n::t('navigation.dashboard') ?></h1>
    </div>
    <div class="card-body">
        <div class="dashboard-stats">
            <!-- Masters Stats -->
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['clients']) ?></div>
                <div class="stat-label"><?= I18n::t('navigation.clients') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/clients" class="btn btn-sm btn-primary">Manage</a>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['suppliers']) ?></div>
                <div class="stat-label"><?= I18n::t('navigation.suppliers') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/suppliers" class="btn btn-sm btn-primary">Manage</a>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['products']) ?></div>
                <div class="stat-label"><?= I18n::t('navigation.products') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/products" class="btn btn-sm btn-primary">Manage</a>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['warehouses']) ?></div>
                <div class="stat-label"><?= I18n::t('navigation.warehouses') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/warehouses" class="btn btn-sm btn-primary">Manage</a>
                </div>
            </div>
            
            <!-- Sales Stats -->
            <div class="stat-card" style="border-left: 4px solid #007bff;">
                <div class="stat-number" style="color: #007bff;"><?= number_format($stats['quotes'] ?? 0) ?></div>
                <div class="stat-label"><?= I18n::t('navigation.quotes') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/quotes" class="btn btn-sm btn-primary">Manage</a>
                </div>
            </div>
            
            <div class="stat-card" style="border-left: 4px solid #28a745;">
                <div class="stat-number" style="color: #28a745;"><?= number_format($stats['sales_orders'] ?? 0) ?></div>
                <div class="stat-label"><?= I18n::t('navigation.sales_orders') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/salesorders" class="btn btn-sm btn-success">Manage</a>
                </div>
            </div>
            
            <div class="stat-card" style="border-left: 4px solid #ffc107;">
                <div class="stat-number" style="color: #ffc107;"><?= number_format($stats['invoices'] ?? 0) ?></div>
                <div class="stat-label"><?= I18n::t('navigation.invoices') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/invoices" class="btn btn-sm btn-warning">Manage</a>
                </div>
            </div>
            
            <div class="stat-card" style="border-left: 4px solid #17a2b8;">
                <div class="stat-number" style="color: #17a2b8;">
                    <?= Helpers::formatCurrency($stats['payments_total'] ?? 0) ?>
                </div>
                <div class="stat-label"><?= I18n::t('navigation.payments') ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="/payments" class="btn btn-sm btn-info">Manage</a>
                </div>
            </div>
            
            <!-- Inventory Stats -->
            <div class="stat-card" style="border-left: 4px solid #dc3545;">
                <div class="stat-number" style="color: #dc3545;"><?= number_format($stats['low_stock']) ?></div>
                <div class="stat-label">Low Stock Items</div>
                <div style="margin-top: 0.5rem;">
                    <a href="/products?search=low_stock" class="btn btn-sm btn-danger">View</a>
                </div>
            </div>
            
            <div class="stat-card" style="border-left: 4px solid #28a745;">
                <div class="stat-number" style="color: #28a745;">
                    <?= Helpers::formatCurrency($stats['inventory_value']) ?>
                </div>
                <div class="stat-label">Inventory Value</div>
                <div style="margin-top: 0.5rem;">
                    <a href="/products" class="btn btn-sm btn-success">Details</a>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions" style="margin-top: 3rem;">
            <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="/clients/create" class="btn btn-primary">Add New Client</a>
                <a href="/suppliers/create" class="btn btn-primary">Add New Supplier</a>
                <a href="/products/create" class="btn btn-primary">Add New Product</a>
                <a href="/quotes/create" class="btn btn-success">Create Quote</a>
                <a href="/invoices/create" class="btn btn-warning">Create Invoice</a>
                <a href="/warehouses/create" class="btn btn-secondary">Add New Warehouse</a>
                <a href="/dropdowns" class="btn btn-secondary">Manage Dropdowns</a>
            </div>
        </div>
        
        <!-- Updated Phase Status -->
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 10px; color: white;">
            <h2 style="color: white; margin-bottom: 1rem;">🎉 Phase 3 Complete!</h2>
            <p style="margin-bottom: 1rem; font-size: 1.1rem;">
                <?= I18n::getLocale() === 'ar' ? 
                    'تم إكمال المرحلة الثالثة بنجاح! يمكنك الآن إدارة العملاء والموردين والمخازن والمنتجات وعروض الأسعار وأوامر البيع والفواتير والمدفوعات.' :
                    'Phase 3 is complete! You now have full sales workflow: Quotes → Sales Orders → Invoices → Payments with complete stock management.'
                ?>
            </p>
            <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1rem;">
                <a href="/quotes/create" class="btn" style="background: white; color: #28a745;">Create Quote</a>
                <a href="/salesorders" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid white;">View Orders</a>
                <a href="/invoices" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid white;">View Invoices</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
