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
                <a href="/warehouses/create" class="btn btn-primary">Add New Warehouse</a>
                <a href="/dropdowns" class="btn btn-secondary">Manage Dropdowns</a>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
            <h2 style="color: #667eea;">🎉 Phase 2 Complete!</h2>
            <p style="color: #666; margin-top: 1rem;">
                <?= I18n::getLocale() === 'ar' ? 
                    'تم إكمال المرحلة الثانية بنجاح! يمكنك الآن إدارة العملاء والموردين والمخازن والمنتجات.' :
                    'Phase 2 is complete! You can now manage clients, suppliers, warehouses, and products with full CRUD functionality.'
                ?>
            </p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
