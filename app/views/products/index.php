<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.products') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= I18n::t('navigation.products') ?></h1>
            <a href="/products/create" class="btn btn-primary"><?= I18n::t('actions.create') ?></a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="/products" style="margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem;">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search products, codes, brands..." 
                    value="<?= Helpers::escape($search) ?>"
                    style="flex: 1;"
                >
                <button type="submit" class="btn btn-secondary"><?= I18n::t('actions.search') ?></button>
                <?php if (!empty($search)): ?>
                    <a href="/products" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th><?= I18n::t('common.name') ?></th>
                        <th>Classification</th>
                        <th>Brand</th>
                        <th>Cost Price</th>
                        <th>Sale Price</th>
                        <th>Stock</th>
                        <th><?= I18n::t('common.action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products['data'])): ?>
                        <?php foreach ($products['data'] as $product): ?>
                            <tr>
                                <td>
                                    <a href="/products/<?= $product['id'] ?>" style="text-decoration: none; color: #667eea;">
                                        <?= Helpers::escape($product['code']) ?>
                                    </a>
                                </td>
                                <td><?= Helpers::escape($product['name']) ?></td>
                                <td><?= Helpers::escape($product['classification']) ?></td>
                                <td><?= Helpers::escape($product['brand']) ?></td>
                                <td><?= Helpers::formatCurrency($product['cost_price']) ?></td>
                                <td><?= Helpers::formatCurrency($product['sale_price']) ?></td>
                                <td>
                                    <span class="stock-indicator <?= $product['total_qty'] <= 5 ? 'low-stock' : '' ?>">
                                        <?= number_format($product['total_qty']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="/products/<?= $product['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                        <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-primary"><?= I18n::t('actions.edit') ?></a>
                                        <form method="POST" action="/products/<?= $product['id'] ?>/delete" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this product?')">
                                            <?= Helpers::csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-danger"><?= I18n::t('actions.delete') ?></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                                No products found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($products['last_page'] > 1): ?>
            <div class="pagination-wrapper">
                <?php
                $currentPage = $products['current_page'];
                $lastPage = $products['last_page'];
                $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
                ?>
                
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="/products?page=<?= $currentPage - 1 ?><?= $searchParam ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="btn btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/products?page=<?= $i ?><?= $searchParam ?>" class="btn btn-secondary"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $lastPage): ?>
                        <a href="/products?page=<?= $currentPage + 1 ?><?= $searchParam ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; color: #666;">
                    Showing <?= $products['from'] ?> to <?= $products['to'] ?> of <?= $products['total'] ?> results
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stock-indicator {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    background-color: #28a745;
    color: white;
    font-size: 0.8rem;
}

.stock-indicator.low-stock {
    background-color: #dc3545;
}

.table-responsive {
    overflow-x: auto;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
