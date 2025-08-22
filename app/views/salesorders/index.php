<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.sales_orders');

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= I18n::t('navigation.sales_orders') ?></h1>
        <div style="display: flex; gap: 1rem;">
            <a href="/quotes" class="btn btn-secondary">
                View Quotes
            </a>
        </div>
    </div>

    <!-- Sales Orders Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-primary">
                        <?= count(array_filter($salesOrders['data'] ?? [], fn($so) => $so['status'] === 'open')) ?>
                    </h3>
                    <p class="text-muted">Open Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?= count(array_filter($salesOrders['data'] ?? [], fn($so) => $so['status'] === 'delivered')) ?>
                    </h3>
                    <p class="text-muted">Delivered</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-danger">
                        <?= count(array_filter($salesOrders['data'] ?? [], fn($so) => $so['status'] === 'rejected')) ?>
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
                               value="<?= Helpers::escape($search ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="open" <?= Helpers::input('status') === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="delivered" <?= Helpers::input('status') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="rejected" <?= Helpers::input('status') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="<?= Helpers::input('date_from', '') ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-secondary"><?= I18n::t('actions.filter') ?></button>
                            <?php if (!empty($search) || !empty(Helpers::input('status')) || !empty(Helpers::input('date_from'))): ?>
                                <a href="/salesorders" class="btn btn-outline-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Orders Table -->
    <div class="card">
        <div class="card-header">
            <h4>Sales Orders</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($salesOrders['data'])): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Client</th>
                                <th><?= I18n::t('common.status') ?></th>
                                <th>Quote</th>
                                <th>Total</th>
                                <th><?= I18n::t('common.created_at') ?></th>
                                <th><?= I18n::t('common.action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salesOrders['data'] as $salesOrder): ?>
                                <tr>
                                    <td>
                                        <a href="/salesorders/<?= $salesOrder['id'] ?>" style="text-decoration: none; color: #667eea;">
                                            SO #<?= str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= Helpers::escape($salesOrder['client_name'] ?? 'Unknown') ?></strong>
                                            <small style="display: block; color: #666;">
                                                <?= ucfirst($salesOrder['client_type'] ?? 'company') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $salesOrder['status'] ?>">
                                            <?= ucfirst($salesOrder['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($salesOrder['quote_id'])): ?>
                                            <a href="/quotes/<?= $salesOrder['quote_id'] ?>" style="text-decoration: none; color: #667eea;">
                                                Q #<?= str_pad($salesOrder['quote_id'], 4, '0', STR_PAD_LEFT) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Direct Order</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Helpers::formatCurrency($salesOrder['grand_total']) ?></td>
                                    <td><?= Helpers::formatDate($salesOrder['created_at'] ?? date('Y-m-d H:i:s')) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                            <a href="/salesorders/<?= $salesOrder['id'] ?>" class="btn btn-sm btn-secondary">
                                                <?= I18n::t('actions.view') ?>
                                            </a>
                                            
                                            <?php if ($salesOrder['status'] === 'open'): ?>
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
                                            <?php elseif ($salesOrder['status'] === 'delivered'): ?>
                                                <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/convert-to-invoice" style="display: inline;" 
                                                      onsubmit="return confirm('Convert this delivered order to an invoice?')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-primary">To Invoice</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($salesOrder['status'], ['rejected'])): ?>
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
                        </tbody>
                    </table>
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
                        
                        <span class="mx-3">
                            Page <?= $currentPage ?> of <?= $lastPage ?>
                            (<?= $salesOrders['from'] ?>-<?= $salesOrders['to'] ?> of <?= $salesOrders['total'] ?> orders)
                        </span>
                        
                        <?php if ($currentPage < $lastPage): ?>
                            <?php 
                            $queryParams['page'] = $currentPage + 1;
                            ?>
                            <a href="/salesorders?<?= http_build_query($queryParams) ?>" class="btn btn-sm btn-secondary">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <h4>No sales orders found</h4>
                    <p>No sales orders match your current filters.</p>
                    <p>Sales orders are typically created by converting approved quotes.</p>
                    <a href="/quotes" class="btn btn-primary">View Quotes</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sales Order Process Flow Info -->
    <div class="card mt-4">
        <div class="card-header">
            <h4>Sales Order Process Flow</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                        <div class="text-center">
                            <div class="badge badge-sent" style="font-size: 0.9rem; padding: 0.5rem 1rem;">Quote</div>
                            <small class="d-block text-muted mt-1">Create & Send</small>
                        </div>
                        <span style="font-size: 1.5rem; color: #667eea;">→</span>
                        <div class="text-center">
                            <div class="badge badge-approved" style="font-size: 0.9rem; padding: 0.5rem 1rem;">Approved</div>
                            <small class="d-block text-muted mt-1">Client Approval</small>
                        </div>
                        <span style="font-size: 1.5rem; color: #667eea;">→</span>
                        <div class="text-center">
                            <div class="badge badge-open" style="font-size: 0.9rem; padding: 0.5rem 1rem;">Sales Order</div>
                            <small class="d-block text-muted mt-1">Convert to SO</small>
                        </div>
                        <span style="font-size: 1.5rem; color: #667eea;">→</span>
                        <div class="text-center">
                            <div class="badge badge-delivered" style="font-size: 0.9rem; padding: 0.5rem 1rem;">Delivered</div>
                            <small class="d-block text-muted mt-1">Mark as Delivered</small>
                        </div>
                        <span style="font-size: 1.5rem; color: #667eea;">→</span>
                        <div class="text-center">
                            <div class="badge badge-paid" style="font-size: 0.9rem; padding: 0.5rem 1rem;">Invoice</div>
                            <small class="d-block text-muted mt-1">Convert to Invoice</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Status badges */
.badge-open { background-color: #007bff; }
.badge-delivered { background-color: #28a745; }
.badge-rejected { background-color: #dc3545; }
.badge-sent { background-color: #17a2b8; }
.badge-approved { background-color: #28a745; }
.badge-paid { background-color: #6f42c1; }

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e3e6f0;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.pagination-wrapper {
    margin-top: 1.5rem;
    text-align: center;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

/* RTL Support */
[dir="rtl"] .d-flex.justify-content-between {
    flex-direction: row-reverse;
}

[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}

[dir="rtl"] .text-center {
    text-align: center !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sales Orders Index Loaded');
    console.log('Total Sales Orders:', <?= count($salesOrders['data'] ?? []) ?>);
    
    // Count status summary
    const orders = <?= json_encode($salesOrders['data'] ?? []) ?>;
    const statusCounts = {
        open: orders.filter(so => so.status === 'open').length,
        delivered: orders.filter(so => so.status === 'delivered').length,
        rejected: orders.filter(so => so.status === 'rejected').length
    };
    
    console.log('Status Summary:', statusCounts);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
