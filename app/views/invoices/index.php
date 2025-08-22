<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.invoices') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= I18n::t('navigation.invoices') ?></h1>
            <div>
                <a href="/invoices/overdue" class="btn btn-warning">Overdue Invoices</a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Status Summary -->
        <?php if (!empty($statusSummary)): ?>
            <div class="status-summary" style="margin-bottom: 2rem;">
                <h3>Invoice Summary</h3>
                <div class="summary-grid">
                    <?php foreach ($statusSummary as $summary): ?>
                        <div class="summary-card">
                            <div class="summary-status">
                                <span class="badge badge-<?= $summary['status'] ?>"><?= ucfirst($summary['status']) ?></span>
                                <span class="summary-count"><?= $summary['count'] ?> invoices</span>
                            </div>
                            <div class="summary-amounts">
                                <div>Total: <?= Helpers::formatCurrency($summary['total_amount']) ?></div>
                                <div>Paid: <?= Helpers::formatCurrency($summary['paid_amount']) ?></div>
                                <div class="balance-amount">
                                    Balance: <?= Helpers::formatCurrency($summary['balance_amount']) ?>
                                </div>
                            </div>
                            <div class="summary-action">
                                <a href="/invoices?status=<?= $summary['status'] ?>" class="btn btn-sm btn-secondary">View</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <form method="GET" action="/invoices" style="margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search invoices, clients..." 
                    value="<?= Helpers::escape($search ?? '') ?>"
                    style="flex: 2; min-width: 200px;"
                >
                
                <select name="status" class="form-control" style="flex: 1; min-width: 150px;">
                    <option value="">All Statuses</option>
                    <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="partial" <?= $status === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="void" <?= $status === 'void' ? 'selected' : '' ?>>Void</option>
                </select>
                
                <button type="submit" class="btn btn-secondary"><?= I18n::t('actions.filter') ?></button>
                
                <?php if (!empty($search) || !empty($status)): ?>
                    <a href="/invoices" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Invoices Table -->
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
                                            <?= ucfirst($invoice['client_type'] ?? 'company') ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $invoice['status'] ?>">
                                        <?= ucfirst($invoice['status']) ?>
                                    </span>
                                </td>
                                <td><?= Helpers::formatCurrency($invoice['grand_total']) ?></td>
                                <td><?= Helpers::formatCurrency($invoice['paid_total']) ?></td>
                                <td>
                                    <?php 
                                    $balance = $invoice['balance'] ?? ($invoice['grand_total'] - $invoice['paid_total']);
                                    $balanceColor = $balance > 0 ? '#dc3545' : '#28a745';
                                    ?>
                                    <span style="color: <?= $balanceColor ?>; font-weight: bold;">
                                        <?= Helpers::formatCurrency($balance) ?>
                                    </span>
                                </td>
                                <td><?= Helpers::formatDate($invoice['created_at'] ?? date('Y-m-d H:i:s')) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <a href="/invoices/<?= $invoice['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                        
                                        <?php if ($invoice['status'] !== 'void' && $balance > 0): ?>
                                            <button onclick="showPaymentModal(<?= $invoice['id'] ?>, <?= $balance ?>)" 
                                                    class="btn btn-sm btn-success">Add Payment</button>
                                        <?php endif; ?>
                                        
                                        <?php if ($invoice['status'] !== 'void' && $invoice['paid_total'] == 0): ?>
                                            <form method="POST" action="/invoices/<?= $invoice['id'] ?>/void" style="display: inline;" 
                                                  onsubmit="return confirm('Void this invoice? This action cannot be undone.')">
                                                <?= Helpers::csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-danger">Void</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if (!in_array($invoice['status'], ['paid', 'partial'])): ?>
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
                
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="/invoices?page=<?= $currentPage - 1 ?><?= $params ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="btn btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/invoices?page=<?= $i ?><?= $params ?>" class="btn btn-secondary"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $lastPage): ?>
                        <a href="/invoices?page=<?= $currentPage + 1 ?><?= $params ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; color: #666;">
                    Showing <?= $invoices['from'] ?> to <?= $invoices['to'] ?> of <?= $invoices['total'] ?> results
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closePaymentModal()">&times;</span>
        <h2>Add Payment</h2>
        <form id="paymentForm" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label for="amount">Payment Amount *</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                <small id="maxAmount" style="color: #666;"></small>
            </div>
            
            <div class="form-group">
                <label for="method">Payment Method *</label>
                <select id="method" name="method" class="form-control" required>
                    <option value="cash">Cash</option>
                    <option value="check">Check</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="note">Note</label>
                <textarea id="note" name="note" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Add Payment</button>
                <button type="button" onclick="closePaymentModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.8rem;
    font-weight: 500;
    color: white;
}

.badge-open { background-color: #007bff; }
.badge-partial { background-color: #ffc107; color: #000; }
.badge-paid { background-color: #28a745; }
.badge-void { background-color: #6c757d; }

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

.summary-action {
    text-align: center;
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
    color: #495057;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-sm {
        margin-bottom: 0.25rem;
    }
}
</style>

<script>
function showPaymentModal(invoiceId, balance) {
    const modal = document.getElementById('paymentModal');
    const form = document.getElementById('paymentForm');
    const amountInput = document.getElementById('amount');
    const maxAmountText = document.getElementById('maxAmount');
    
    form.action = `/invoices/${invoiceId}/add-payment`;
    amountInput.value = balance.toFixed(2);
    amountInput.max = balance.toFixed(2);
    maxAmountText.textContent = `Maximum: $${balance.toFixed(2)}`;
    
    modal.style.display = 'block';
    amountInput.focus();
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

// Handle escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePaymentModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
