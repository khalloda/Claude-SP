<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.payments');
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= I18n::t('navigation.payments') ?></h1>
        <div style="display: flex; gap: 1rem;">
            <a href="/payments/create" class="btn btn-primary">
                + <?= I18n::t('actions.create') ?> Payment
            </a>
            <a href="/payments/export<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-secondary">
                Export CSV
            </a>
        </div>
    </div>

    <!-- Payment Summary Cards -->
    <?php if (!empty($summary)): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?= number_format($summary['summary']['total_payments'] ?? 0) ?></h3>
                        <p class="text-muted">Total Payments (30 days)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success"><?= Helpers::formatCurrency($summary['summary']['total_amount'] ?? 0) ?></h3>
                        <p class="text-muted">Total Amount</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-info"><?= Helpers::formatCurrency($summary['summary']['average_amount'] ?? 0) ?></h3>
                        <p class="text-muted">Average Payment</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-warning"><?= number_format($summary['summary']['unique_clients'] ?? 0) ?></h3>
                        <p class="text-muted">Paying Clients</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/payments">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Client name, note, method..."
                               value="<?= Helpers::escape($search ?? '') ?>"
                        >
                    </div>
                    
                    <div class="col-md-2">
                        <label for="method" class="form-label">Payment Method</label>
                        <select name="method" class="form-control" id="method">
                            <option value="">All Methods</option>
                            <option value="cash" <?= ($method ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="bank_transfer" <?= ($method ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="check" <?= ($method ?? '') === 'check' ? 'selected' : '' ?>>Check</option>
                            <option value="credit_card" <?= ($method ?? '') === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                            <option value="other" <?= ($method ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="<?= Helpers::escape($dateFrom ?? '') ?>"
                        >
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="<?= Helpers::escape($dateTo ?? '') ?>"
                        >
                    </div>
                    
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-secondary d-block"><?= I18n::t('actions.filter') ?></button>
                    </div>
                    
                    <?php if (!empty($search) || !empty($method) || !empty($dateFrom) || !empty($dateTo)): ?>
                        <div class="col-md-1">
                            <label>&nbsp;</label>
                            <a href="/payments" class="btn btn-secondary d-block">Clear</a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Client</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Note</th>
                            <th><?= I18n::t('common.action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments['data'])): ?>
                            <?php foreach ($payments['data'] as $payment): ?>
                                <tr>
                                    <td>
                                        <a href="/payments/<?= $payment['id'] ?>" style="text-decoration: none; color: #667eea;">
                                            #<?= str_pad($payment['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= Helpers::escape($payment['client_name'] ?? 'Unknown') ?></strong>
                                            <small style="display: block; color: #666;">
                                                <?= ucfirst($payment['client_type'] ?? 'unknown') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (isset($payment['invoice_id']) && $payment['invoice_id']): ?>
                                            <a href="/invoices/<?= $payment['invoice_id'] ?>" style="text-decoration: none; color: #28a745;">
                                                Invoice #<?= $payment['invoice_id'] ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #666;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: #28a745; font-weight: bold;">
                                            <?= Helpers::formatCurrency($payment['amount'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-method badge-<?= str_replace('_', '-', $payment['method'] ?? 'other') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $payment['method'] ?? 'Other')) ?>
                                        </span>
                                    </td>
                                    <td><?= Helpers::formatDate($payment['created_at'] ?? null) ?></td>
                                    <td>
                                        <span style="max-width: 200px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= Helpers::escape($payment['note'] ?? '') ?>">
                                            <?= Helpers::escape($payment['note'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <a href="/payments/<?= $payment['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                            
                                            <?php if (!isset($payment['is_system_generated']) || !$payment['is_system_generated']): ?>
                                                <a href="/payments/<?= $payment['id'] ?>/edit" class="btn btn-sm btn-primary"><?= I18n::t('actions.edit') ?></a>
                                                
                                                <form method="POST" action="/payments/<?= $payment['id'] ?>/delete" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this payment? This will affect invoice balances.')">
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
                                <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                                    No payments found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (isset($payments['last_page']) && $payments['last_page'] > 1): ?>
        <div class="pagination-wrapper">
            <?php
            $currentPage = $payments['current_page'];
            $lastPage = $payments['last_page'];
            $queryParams = $_GET;
            ?>
            
            <?php if ($currentPage > 1): ?>
                <?php 
                $queryParams['page'] = $currentPage - 1;
                ?>
                <a href="/payments?<?= http_build_query($queryParams) ?>" class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>
            
            <span class="mx-3">Page <?= $currentPage ?> of <?= $lastPage ?></span>
            
            <?php if ($currentPage < $lastPage): ?>
                <?php 
                $queryParams['page'] = $currentPage + 1;
                ?>
                <a href="/payments?<?= http_build_query($queryParams) ?>" class="btn btn-sm btn-secondary">Next</a>
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

.badge-method {
    font-size: 0.7rem;
}

.badge-cash { background-color: #28a745; }
.badge-bank-transfer { background-color: #007bff; }
.badge-check { background-color: #ffc107; color: #000; }
.badge-credit-card { background-color: #17a2b8; }
.badge-other { background-color: #6c757d; }

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

.col-md-3, .col-md-2, .col-md-1 {
    padding: 0.5rem;
}

.col-md-3 { flex: 0 0 25%; }
.col-md-2 { flex: 0 0 16.666%; }
.col-md-1 { flex: 0 0 8.333%; }

@media (max-width: 768px) {
    .col-md-3, .col-md-2, .col-md-1 {
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

.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}

.mb-4 {
    margin-bottom: 2rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 0.9rem;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-primary { background-color: #007bff; color: white; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-danger { background-color: #dc3545; color: white; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }

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
    vertical-align: top;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.table-responsive {
    overflow-x: auto;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
