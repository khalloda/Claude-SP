<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = $product['name'] . ' - Products - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= Helpers::escape($product['name']) ?></h1>
            <div>
                <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-primary">Edit Product</a>
                <a href="/products" class="btn btn-secondary">Back to Products</a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Product Details -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Product Code:</strong></td>
                        <td><?= Helpers::escape($product['code']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Classification:</strong></td>
                        <td><?= Helpers::escape($product['classification']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Brand:</strong></td>
                        <td><?= Helpers::escape($product['brand']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Color:</strong></td>
                        <td><?= Helpers::escape($product['color']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Car Make:</strong></td>
                        <td><?= Helpers::escape($product['car_make']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Car Model:</strong></td>
                        <td><?= Helpers::escape($product['car_model']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Cost Price:</strong></td>
                        <td><?= Helpers::formatCurrency($product['cost_price']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Sale Price:</strong></td>
                        <td><?= Helpers::formatCurrency($product['sale_price']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Profit Margin:</strong></td>
                        <td>
                            <?php 
                            $margin = $product['cost_price'] > 0 ? 
                                (($product['sale_price'] - $product['cost_price']) / $product['cost_price']) * 100 : 0;
                            ?>
                            <?= number_format($margin, 1) ?>%
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total Stock:</strong></td>
                        <td>
                            <span class="stock-indicator <?= $product['total_qty'] <= 5 ? 'low-stock' : '' ?>">
                                <?= number_format($product['total_qty']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Reserved (Quotes):</strong></td>
                        <td><?= number_format($product['reserved_quotes']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Reserved (Orders):</strong></td>
                        <td><?= number_format($product['reserved_orders']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Warehouse Locations -->
        <div style="margin-bottom: 2rem;">
            <h3>Warehouse Locations</h3>
            <?php if (!empty($locations)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Location</th>
                                <th>Quantity</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $location): ?>
                                <tr>
                                    <td><?= Helpers::escape($location['warehouse_name']) ?></td>
                                    <td><?= Helpers::escape($location['location_label']) ?></td>
                                    <td><?= number_format($location['qty']) ?></td>
                                    <td><?= Helpers::formatCurrency($location['qty'] * $product['cost_price']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #666;">No warehouse locations configured for this product.</p>
            <?php endif; ?>
        </div>

        <!-- Stock Movements -->
        <div>
            <h3>Recent Stock Movements</h3>
            <?php if (!empty($stockMovements)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Direction</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockMovements as $movement): ?>
                                <tr>
                                    <td><?= Helpers::formatDate($movement['created_at']) ?></td>
                                    <td>
                                        <span class="badge <?= $movement['direction'] === 'in' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= strtoupper($movement['direction']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($movement['qty']) ?></td>
                                    <td><?= Helpers::escape($movement['reason']) ?></td>
                                    <td>
                                        <?php if ($movement['ref_table'] && $movement['ref_id']): ?>
                                            <?= Helpers::escape($movement['ref_table']) ?> #<?= $movement['ref_id'] ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #666;">No stock movements recorded for this product.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stock-indicator {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    background-color: #28a745;
    color: white;
    font-size: 0.8rem;
    font-weight: 500;
}

.stock-indicator.low-stock {
    background-color: #dc3545;
}

.badge-success {
    background-color: #28a745;
}

.badge-danger {
    background-color: #dc3545;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
