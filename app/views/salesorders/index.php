<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.sales_orders');
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= I18n::t('navigation.sales_orders') ?></h1>
        <div style="display: flex; gap: 1rem;">
            <a href="/quotes" class="btn btn-secondary">
                View Quotes
            </a>
            <a href="/salesorders/create" class="btn btn-primary">
                + Create Sales Order
            </a>
        </div>
    </div>

    <!-- Sales Orders Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-primary">
                        <?= count(array_filter($salesOrders['data'] ?? [], fn($so) => ($so['status'] ?? '') === 'open')) ?>
                    </h3>
                    <p class="text-muted">Open Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?= count(array_filter($salesOrders['data'] ?? [], fn($so) => ($so['status'] ?? '') === 'delivered')) ?>
                    </h3>
                    <p class="text-muted">Delivered</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-danger">
                        <?= count(array_filter($salesOrders['data'] ?? [], fn($so) => ($so['status'] ?? '') === 'rejected')) ?>
                    </h3>
                    <p class="text-muted">Rejected</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        <?php 
                        $totalValue = array_sum(array_map(fn($so) => $so['grand_total'] ?? 0, $salesOrders['data'] ?? []));
                        echo Helpers::formatCurrency($totalValue);
                        ?>
                    </h3>
                    <p class="text-muted">Total Value</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/salesorders">
                <div class="row">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Client name, notes, order ID..."
                               value="<?= Helpers::escape($search ?? '') ?>"
                        >
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-control" id="status">
                            <option value="">All Statuses</option>
                            <option value="open" <?= ($status ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="shipped" <?= ($status ?? '') === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= ($status ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-secondary d-block"><?= I18n::t('actions.filter') ?></button>
                    </div>
                    
                    <?php if (!empty($search) || !empty($status)): ?>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <a href="/salesorders" class="btn btn-secondary d-block">Clear</a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Client</th>
                            <th>Quote</th>
                            <th><?= I18n::t('common.status') ?></th>
                            <th>Total</th>
                            <th><?= I18n::t('common.created_at') ?></th>
                            <th><?= I18n::t('common.action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($salesOrders['data'])): ?>
                            <?php foreach ($salesOrders['data'] as $salesOrder): ?>
                                <tr>
                                    <td>
                                        <a href="/salesorders/<?= $salesOrder['id'] ?>" style="text-decoration: none; color: #667eea;">
                                            #<?= str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>
                                                <a href="/clients/<?= $salesOrder['client_id'] ?? 0 ?>" style="text-decoration: none; color: #333;">
                                                    <?= Helpers::escape($salesOrder['client_name'] ?? 'Unknown Client') ?>
                                                </a>
                                            </strong>
                                            <small style="display: block; color: #666;">
                                                <?= ucfirst($salesOrder['client_type'] ?? 'individual') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (isset($salesOrder['quote_id']) && $salesOrder['quote_id']): ?>
                                            <a href="/quotes/<?= $salesOrder['quote_id'] ?>" style="text-decoration: none; color: #28a745;">
                                                Quote #<?= $salesOrder['quote_id'] ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #666;">Direct Order</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $salesOrder['status'] ?? 'unknown' ?>">
                                            <?= ucfirst($salesOrder['status'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td><?= Helpers::formatCurrency($salesOrder['grand_total'] ?? 0) ?></td>
                                    <td><?= Helpers::formatDate($salesOrder['created_at'] ?? null) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <a href="/salesorders/<?= $salesOrder['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                            
                                            <?php if (($salesOrder['status'] ?? '') === 'open'): ?>
                                                <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/deliver" style="display: inline;" 
                                                      onsubmit="return confirm('Mark this sales order as delivered?')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-success">Deliver</button>
                                                </form>
                                                
                                                <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/convert-to-invoice" style="display: inline;" 
                                                      onsubmit="return confirm('Convert this sales order to an invoice?')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-primary">To Invoice</button>
                                                </form>
                                                
                                                <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/reject" style="display: inline;" 
                                                      onsubmit="return confirm('Reject this sales order? This will release reserved stock.')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                </form>
                                            <?php elseif (($salesOrder['status'] ?? '') === 'delivered'): ?>
                                                <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/convert-to-invoice" style="display: inline;" 
                                                      onsubmit="return confirm('Convert this delivered order to an invoice?')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-primary">To Invoice</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($salesOrder['status'] ?? '', ['rejected'])): ?>
                                                <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/delete" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this sales order?')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger"><?= I18n::t('actions.delete') ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #666;">
                                    No sales orders found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($salesOrders['last_page']) && $salesOrders['last_page'] > 1): ?>
        <div class="pagination-wrapper">
            <?php
            $currentPage = $salesOrders['current_page'];
            $lastPage = $salesOrders['last_page'];
            $queryParams = $_GET;
            ?>
            
            <?php if ($currentPage > 1): ?>
                <?php 
                $queryParams['page'] = $currentPage - 1;
                ?>
                <a href="/salesorders?<?= http_build_query($queryParams) ?>" class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>
            
            <span class="mx-3">Page <?= $currentPage ?> of <?= $lastPage ?></span>
            
            <?php if ($currentPage < $lastPage): ?>
                <?php 
                $queryParams['page'] = $currentPage + 1;
                ?>
                <a href="/salesorders?<?= http_build_query($queryParams) ?>" class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.8rem;
    color: white;
}

.badge-open { background-color: #007bff; }
.badge-shipped { background-color: #17a2b8; }
.badge-delivered { background-color: #28a745; }
.badge-rejected { background-color: #dc3545; }
.badge-cancelled { background-color: #6c757d; }
.badge-unknown { background-color: #6c757d; }

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: none;
    margin-bottom: 2rem;
}

.card-body {
    padding: 1.5rem;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.5rem;
}

.col-md-3, .col-md-4, .col-md-2 {
    padding: 0.5rem;
}

.col-md-3 { flex: 0 0 25%; }
.col-md-4 { flex: 0 0 33.333%; }
.col-md-2 { flex: 0 0 16.666%; }

@media (max-width: 768px) {
    .col-md-3, .col-md-4, .col-md-2 {
        flex: 0 0 100%;
    }
}

.d-block {
    display: block;
    width: 100%;
}

.pagination-wrapper {
    margin-top: 2rem;
    text-align: center;
}

.mx-3 {
    margin: 0 1rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
