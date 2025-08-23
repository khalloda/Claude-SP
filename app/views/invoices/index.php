<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.invoices');
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= I18n::t('navigation.invoices') ?></h1>
        <div style="display: flex; gap: 1rem;">
            <a href="/invoices/create" class="btn btn-primary">
                + <?= I18n::t('actions.create') ?> Invoice
            </a>
            <a href="/invoices/export<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-secondary">
                Export CSV
            </a>
        </div>
    </div>

    <!-- Invoice Status Summary -->
    <?php if (!empty($statusSummary)): ?>
        <div class="status-summary mb-4">
            <h5>Invoice Summary</h5>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-status">
                        <span class="badge badge-open">Open</span>
                        <span class="summary-count"><?= $statusSummary['open']['count'] ?? 0 ?> invoices</span>
                    </div>
                    <div class="summary-amounts">
                        <div>Total: <?= Helpers::formatCurrency($statusSummary['open']['total'] ?? 0) ?></div>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-status">
                        <span class="badge badge-partial">Partial</span>
                        <span class="summary-count"><?= $statusSummary['partial']['count'] ?? 0 ?> invoices</span>
                    </div>
                    <div class="summary-amounts">
                        <div>Total: <?= Helpers::formatCurrency($statusSummary['partial']['total'] ?? 0) ?></div>
                        <div class="balance-amount">Balance: <?= Helpers::formatCurrency($statusSummary['partial']['balance'] ?? 0) ?></div>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-status">
                        <span class="badge badge-paid">Paid</span>
                        <span class="summary-count"><?= $statusSummary['paid']['count'] ?? 0 ?> invoices</span>
                    </div>
                    <div class="summary-amounts">
                        <div>Total: <?= Helpers::formatCurrency($statusSummary['paid']['total'] ?? 0) ?></div>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-status">
                        <span class="badge badge-void">Void</span>
                        <span class="summary-count"><?= $statusSummary['void']['count'] ?? 0 ?> invoices</span>
                    </div>
                    <div class="summary-amounts">
                        <div>Total: <?= Helpers::formatCurrency($statusSummary['void']['total'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/invoices">
                <div style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                    <div style="flex: 2; min-width: 200px;">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Client name, invoice #, notes..." 
                               value="<?= Helpers::escape($search ?? '') ?>"
                        >
                    </div>
                    
                    <div style="flex: 1; min-width: 150px;">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-control" id="status">
                            <option value="">All Statuses</option>
                            <option value="open" <?= ($status ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="partial" <?= ($status ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                            <option value="paid" <?= ($status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="void" <?= ($status ?? '') === 'void' ? 'selected' : '' ?>>Void</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-secondary"><?= I18n::t('actions.filter') ?></button>
                    </div>
                    
                    <?php if (!empty($search) || !empty($status)): ?>
                        <div>
                            <a href="/invoices" class="btn btn-secondary">Clear</a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th><?= I18n::t('common.status') ?></th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th><?= I18n::t('common.created_at') ?></th>
                            <th><?= I18n::t('common.action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices['data'])): ?>
                            <?php foreach ($invoices['data'] as $invoice): ?>
                                <tr>
                                    <td>
                                        <a href="/invoices/<?= $invoice['id'] ?>" style="text-decoration: none; color: #667eea;">
                                            #<?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                        <?php if (isset($invoice['sales_order_id']) && $invoice['sales_order_id']): ?>
                                            <br><small style="color: #666;">SO #<?= $invoice['sales_order_id'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= Helpers::escape($invoice['client_name'] ?? 'Unknown') ?></strong>
                                            <small style="display: block; color: #666;">
                                                <?= ucfirst($invoice['client_type'] ?? 'unknown') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $invoice['status'] ?? 'unknown' ?>">
                                            <?= ucfirst($invoice['status'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td><?= Helpers::formatCurrency($invoice['grand_total'] ?? 0) ?></td>
                                    <td><?= Helpers::formatCurrency($invoice['paid_total'] ?? 0) ?></td>
                                    <td>
                                        <?php 
                                        $balance = ($invoice['grand_total'] ?? 0) - ($invoice['paid_total'] ?? 0);
                                        $balanceColor = $balance > 0 ? '#dc3545' : '#28a745';
                                        ?>
                                        <span style="color: <?= $balanceColor ?>; font-weight: <?= $balance > 0 ? 'bold' : 'normal' ?>">
                                            <?= Helpers::formatCurrency($balance) ?>
                                        </span>
                                    </td>
                                    <td><?= Helpers::formatDate($invoice['created_at'] ?? null) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <a href="/invoices/<?= $invoice['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                            
                                            <?php if (!in_array($invoice['status'] ?? '', ['paid', 'partial'])): ?>
                                                <a href="/invoices/<?= $invoice['id'] ?>/edit" class="btn btn-sm btn-primary"><?= I18n::t('actions.edit') ?></a>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($invoice['status'] ?? '', ['open', 'partial'])): ?>
                                                <button type="button" class="btn btn-sm btn-success" onclick="showPaymentModal(<?= $invoice['id'] ?>)">
                                                    Add Payment
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (($invoice['status'] ?? '') === 'open'): ?>
                                                <form method="POST" action="/invoices/<?= $invoice['id'] ?>/void" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to void this invoice? This action cannot be undone.')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger">Void</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (!in_array($invoice['status'] ?? '', ['paid', 'partial'])): ?>
                                                <form method="POST" action="/invoices/<?= $invoice['id'] ?>/delete" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this invoice?')">
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
                                    No invoices found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (isset($invoices['last_page']) && $invoices['last_page'] > 1): ?>
        <div class="pagination-wrapper">
            <?php
            $currentPage = $invoices['current_page'];
            $lastPage = $invoices['last_page'];
            $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
            $statusParam = !empty($status) ? '&status=' . urlencode($status) : '';
            $params = $searchParam . $statusParam;
            ?>
            
            <?php if ($currentPage > 1): ?>
                <a href="/invoices?page=<?= $currentPage - 1 ?><?= $params ?>" class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>
            
            <span class="mx-3">Page <?= $currentPage ?> of <?= $lastPage ?></span>
            
            <?php if ($currentPage < $lastPage): ?>
                <a href="/invoices?page=<?= $currentPage + 1 ?><?= $params ?>" class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closePaymentModal()">&times;</span>
        <h3>Add Payment</h3>
        <form id="paymentForm" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label for="amount" class="form-label">Amount *</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            </div>
            
            <div class="form-group">
                <label for="method" class="form-label">Payment Method *</label>
                <select class="form-control" id="method" name="method" required>
                    <option value="">Select Method</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="check">Check</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="note" class="form-label">Note</label>
                <textarea class="form-control" id="note" name="note" rows="3"></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Payment</button>
            </div>
        </form>
    </div>
</div>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.8rem;
    color: white;
}

.badge-open { background-color: #007bff; }
.badge-partial { background-color: #ffc107; color: #000; }
.badge-paid { background-color: #28a745; }
.badge-void { background-color: #6c757d; }
.badge-unknown { background-color: #6c757d; }

.status-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.summary-card {
    background: white;
    padding: 1rem;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.summary-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.summary-count {
    font-size: 0.9rem;
    color: #666;
}

.summary-amounts {
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.balance-amount {
    font-weight: bold;
    color: #dc3545;
}

/* Modal styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
}

.close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
}

.form-group {
    margin-bottom: 1rem;
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
}
</style>

<script>
function showPaymentModal(invoiceId) {
    const modal = document.getElementById('paymentModal');
    const form = document.getElementById('paymentForm');
    form.action = '/invoices/' + invoiceId + '/add-payment';
    modal.style.display = 'block';
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'none';
    // Reset form
    document.getElementById('paymentForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closePaymentModal();
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
