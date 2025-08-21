<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = $warehouse['name'] . ' - ' . I18n::t('navigation.warehouses') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= Helpers::escape($warehouse['name']) ?></h1>
            <div>
                <a href="/warehouses/<?= $warehouse['id'] ?>/edit" class="btn btn-primary"><?= I18n::t('actions.edit') ?></a>
                <a href="/warehouses" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Warehouse Details -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Capacity:</strong></td>
                        <td><?= $warehouse['capacity'] ? number_format($warehouse['capacity'], 0) . ' m²' : 'Not specified' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Responsible Person:</strong></td>
                        <td><?= Helpers::escape($warehouse['responsible_name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Responsible Email:</strong></td>
                        <td><?= Helpers::escape($warehouse['responsible_email']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Responsible Phone:</strong></td>
                        <td><?= Helpers::escape($warehouse['responsible_phone']) ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= I18n::t('common.created_at') ?>:</strong></td>
                        <td><?= Helpers::formatDate($warehouse['created_at']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div>
                    <strong><?= I18n::t('common.address') ?>:</strong><br>
                    <?= Helpers::escape($warehouse['address']) ?>
                </div>
                
                <div style="margin-top: 1rem;">
                    <strong>Warehouse Statistics:</strong><br>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">
                        <div>Total Products: <strong><?= count($products) ?></strong></div>
                        <div>Total Value: <strong><?= Helpers::formatCurrency($totalValue) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products in Warehouse -->
        <div style="margin-bottom: 2rem;">
            <h3>Products in this Warehouse</h3>
            <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Location</th>
                                <th>Quantity</th>
                                <th>Unit Value</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <a href="/products/<?= $product['id'] ?>" style="text-decoration: none; color: #667eea;">
                                            <?= Helpers::escape($product['code']) ?>
                                        </a>
                                    </td>
                                    <td><?= Helpers::escape($product['name']) ?></td>
                                    <td><?= Helpers::escape($product['location_label']) ?></td>
                                    <td><?= number_format($product['warehouse_qty']) ?></td>
                                    <td><?= Helpers::formatCurrency($product['cost_price']) ?></td>
                                    <td><?= Helpers::formatCurrency($product['cost_price'] * $product['warehouse_qty']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <td colspan="5">Total Warehouse Value:</td>
                                <td><?= Helpers::formatCurrency($totalValue) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #666;">No products stored in this warehouse yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.5rem;
}

.col-md-6 {
    flex: 0 0 50%;
    padding: 0.5rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
    text-align: left;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0;
    vertical-align: top;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
    }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
