<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($payment);
$title = $isEdit ? 
    I18n::t('navigation.payments') . ' - ' . I18n::t('actions.edit') . ' #' . str_pad($payment['id'], 4, '0', STR_PAD_LEFT) :
    I18n::t('navigation.payments') . ' - ' . I18n::t('actions.create');

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= $isEdit ? 'Edit Payment #' . str_pad($payment['id'], 4, '0', STR_PAD_LEFT) : 'Record New Payment' ?></h1>
        <a href="<?= $isEdit ? '/payments/' . $payment['id'] : '/payments' ?>" class="btn btn-secondary">
            <?= I18n::t('actions.back') ?>
        </a>
    </div>

    <?php if ($isEdit): ?>
        <div class="alert alert-info mb-4">
            <strong>Note:</strong> Only payment method and notes can be edited for data integrity. To change the amount, please reverse this payment and create a new one.
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $isEdit ? '/payments/' . $payment['id'] : '/payments' ?>" id="paymentForm">
        <?= Helpers::csrfField() ?>
        
        <div class="row">
            <!-- Left Column - Main Form -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><?= $isEdit ? 'Edit Payment Details' : 'Payment Information' ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (!$isEdit): ?>
                            <!-- Invoice Selection (Create mode only) -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">
                                            <?= I18n::t('navigation.client') ?> <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="client_id" name="client_id" onchange="loadClientInvoices()" required>
                                            <option value="">Select a client...</option>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?= $client['id'] ?>" 
                                                        <?= (isset($invoice) && $invoice['client_id'] == $client['id']) || Helpers::old('client_id') == $client['id'] ? 'selected' : '' ?>>
                                                    <?= Helpers::escape($client['name']) ?> (<?= ucfirst($client['type']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= Helpers::getError('client_id') ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="invoice_id" class="form-label">
                                            Invoice <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="invoice_id" name="invoice_id" onchange="loadInvoiceDetails()" required>
                                            <option value="">Select an invoice...</option>
                                            <?php if (isset($invoice)): ?>
                                                <option value="<?= $invoice['id'] ?>" selected>
                                                    Invoice #<?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?> - 
                                                    <?= Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) ?> due
                                                </option>
                                            <?php endif; ?>
                                        </select>
                                        <?= Helpers::getError('invoice_id') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Details Display -->
                            <div id="invoice-details" style="display: <?= isset($invoice) ? 'block' : 'none' ?>;">
                                <div class="p-3 mb-3" style="background-color: #f8f9fa; border-radius: 0.375rem; border: 1px solid #dee2e6;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Invoice Total:</strong> <span id="invoice-total"><?= isset($invoice) ? Helpers::formatCurrency($invoice['grand_total']) : '' ?></span></p>
                                            <p><strong>Already Paid:</strong> <span id="invoice-paid"><?= isset($invoice) ? Helpers::formatCurrency($invoice['paid_total']) : '' ?></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Outstanding Balance:</strong> 
                                                <span id="invoice-balance" style="color: #dc3545; font-weight: bold;">
                                                    <?= isset($invoice) ? Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) : '' ?>
                                                </span>
                                            </p>
                                            <p><strong>Invoice Status:</strong> 
                                                <span id="invoice-status" class="badge">
                                                    <?= isset($invoice) ? ucfirst($invoice['status']) : '' ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Amount -->
                            <div class="form-group">
                                <label for="amount" class="form-label">
                                    Payment Amount <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0.01" 
                                       max="<?= isset($invoice) ? $invoice['grand_total'] - $invoice['paid_total'] : '' ?>"
                                       value="<?= isset($invoice) ? $invoice['grand_total'] - $invoice['paid_total'] : Helpers::old('amount') ?>"
                                       required>
                                <small class="text-muted">
                                    Maximum amount: <span id="max-amount"><?= isset($invoice) ? Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) : '$0.00' ?></span>
                                </small>
                                <?= Helpers::getError('amount') ?>
                            </div>
                        <?php else: ?>
                            <!-- Edit mode - show read-only invoice info -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Client</label>
                                        <input type="text" class="form-control" value="<?= Helpers::escape($payment['client_name']) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Invoice</label>
                                        <input type="text" class="form-control" 
                                               value="Invoice #<?= str_pad($payment['invoice_id'], 4, '0', STR_PAD_LEFT) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Payment Amount</label>
                                <input type="text" class="form-control" 
                                       value="<?= Helpers::formatCurrency($payment['amount']) ?>" readonly>
                                <small class="text-muted">Payment amounts cannot be changed. To adjust, reverse this payment and create a new one.</small>
                            </div>
                        <?php endif; ?>

                        <!-- Payment Method -->
                        <div class="form-group">
                            <label for="method" class="form-label">
                                Payment Method <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="method" name="method" required>
                                <option value="">Select payment method...</option>
                                <option value="cash" <?= ($isEdit && $payment['method'] == 'cash') || Helpers::old('method') == 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="check" <?= ($isEdit && $payment['method'] == 'check') || Helpers::old('method') == 'check' ? 'selected' : '' ?>>Check</option>
                                <option value="bank_transfer" <?= ($isEdit && $payment['method'] == 'bank_transfer') || Helpers::old('method') == 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="credit_card" <?= ($isEdit && $payment['method'] == 'credit_card') || Helpers::old('method') == 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                                <option value="debit_card" <?= ($isEdit && $payment['method'] == 'debit_card') || Helpers::old('method') == 'debit_card' ? 'selected' : '' ?>>Debit Card</option>
                                <option value="online_payment" <?= ($isEdit && $payment['method'] == 'online_payment') || Helpers::old('method') == 'online_payment' ? 'selected' : '' ?>>Online Payment</option>
                                <option value="other" <?= ($isEdit && $payment['method'] == 'other') || Helpers::old('method') == 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <?= Helpers::getError('method') ?>
                        </div>

                        <!-- Payment Note -->
                        <div class="form-group">
                            <label for="note" class="form-label">Note (Optional)</label>
                            <textarea class="form-control" id="note" name="note" rows="3" 
                                      placeholder="Reference number, check number, transaction ID, or other details..."><?= $isEdit ? Helpers::escape($payment['note'] ?? '') : Helpers::old('note') ?></textarea>
                            <small class="text-muted">Add any relevant details like check numbers, transaction IDs, or payment references.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Summary & Actions -->
            <div class="col-md-4">
                <?php if (!$isEdit): ?>
                    <!-- Payment Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Payment Summary</h4>
                        </div>
                        <div class="card-body">
                            <div id="payment-summary" style="display: <?= isset($invoice) ? 'block' : 'none' ?>;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Invoice Total:</span>
                                    <span id="summary-total"><?= isset($invoice) ? Helpers::formatCurrency($invoice['grand_total']) : '$0.00' ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Previously Paid:</span>
                                    <span id="summary-paid"><?= isset($invoice) ? Helpers::formatCurrency($invoice['paid_total']) : '$0.00' ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Outstanding Balance:</span>
                                    <span id="summary-balance" style="color: #dc3545; font-weight: bold;">
                                        <?= isset($invoice) ? Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) : '$0.00' ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-3" style="font-size: 1.1rem;">
                                    <strong>This Payment:</strong>
                                    <strong id="summary-payment" style="color: #28a745;">$0.00</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between" style="font-size: 1.2rem; font-weight: bold;">
                                    <span>Remaining Balance:</span>
                                    <span id="summary-remaining" style="color: #dc3545;">$0.00</span>
                                </div>
                            </div>
                            
                            <div id="no-invoice-selected" style="display: <?= isset($invoice) ? 'none' : 'block' ?>;">
                                <p class="text-muted text-center">Select an invoice to see payment summary</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions Card -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <?= $isEdit ? 'Update Payment' : 'Record Payment' ?>
                        </button>
                        <a href="<?= $isEdit ? '/payments/' . $payment['id'] : '/payments' ?>" class="btn btn-secondary btn-block">
                            <?= I18n::t('actions.cancel') ?>
                        </a>
                        
                        <?php if (!$isEdit): ?>
                            <hr>
                            <small class="text-muted">
                                <strong>Note:</strong> Recording this payment will:
                                <ul class="mt-2 mb-0" style="padding-left: 1rem;">
                                    <li>Update the invoice balance</li>
                                    <li>Change invoice status if fully paid</li>
                                    <li>Create a permanent payment record</li>
                                </ul>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.form-label {
    font-weight: 600;
    color: #374151;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e3e6f0;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.badge {
    padding: 0.375rem 0.75rem;
}

/* Invoice status badges */
.badge-open { background-color: #007bff; }
.badge-partial { background-color: #ffc107; color: #000; }
.badge-paid { background-color: #28a745; }
.badge-void { background-color: #6c757d; }

/* RTL Support */
[dir="rtl"] .d-flex.justify-content-between {
    flex-direction: row-reverse;
}

[dir="rtl"] .text-right {
    text-align: left !important;
}
</style>

<script>
// Invoice and payment calculation functions
function loadClientInvoices() {
    const clientId = document.getElementById('client_id').value;
    const invoiceSelect = document.getElementById('invoice_id');
    
    // Clear current options
    invoiceSelect.innerHTML = '<option value="">Loading invoices...</option>';
    
    if (!clientId) {
        invoiceSelect.innerHTML = '<option value="">Select a client first</option>';
        hideInvoiceDetails();
        return;
    }
    
    // Make AJAX request to get client invoices
    fetch('/payments/get-client-invoices?client_id=' + clientId)
        .then(response => response.json())
        .then(data => {
            invoiceSelect.innerHTML = '<option value="">Select an invoice...</option>';
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(invoice => {
                    const balance = invoice.grand_total - invoice.paid_total;
                    if (balance > 0) { // Only show invoices with outstanding balance
                        const option = document.createElement('option');
                        option.value = invoice.id;
                        option.textContent = `Invoice #${String(invoice.id).padStart(4, '0')} - ${formatCurrency(balance)} due`;
                        option.dataset.grandTotal = invoice.grand_total;
                        option.dataset.paidTotal = invoice.paid_total;
                        option.dataset.status = invoice.status;
                        invoiceSelect.appendChild(option);
                    }
                });
                
                if (invoiceSelect.children.length === 1) {
                    invoiceSelect.innerHTML = '<option value="">No unpaid invoices found for this client</option>';
                }
            } else {
                invoiceSelect.innerHTML = '<option value="">No unpaid invoices found</option>';
            }
        })
        .catch(error => {
            console.error('Error loading invoices:', error);
            invoiceSelect.innerHTML = '<option value="">Error loading invoices</option>';
        });
}

function loadInvoiceDetails() {
    const invoiceSelect = document.getElementById('invoice_id');
    const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
    
    if (!selectedOption.value) {
        hideInvoiceDetails();
        return;
    }
    
    const grandTotal = parseFloat(selectedOption.dataset.grandTotal);
    const paidTotal = parseFloat(selectedOption.dataset.paidTotal);
    const balance = grandTotal - paidTotal;
    const status = selectedOption.dataset.status;
    
    // Update invoice details display
    document.getElementById('invoice-total').textContent = formatCurrency(grandTotal);
    document.getElementById('invoice-paid').textContent = formatCurrency(paidTotal);
    document.getElementById('invoice-balance').textContent = formatCurrency(balance);
    document.getElementById('invoice-status').textContent = status.charAt(0).toUpperCase() + status.slice(1);
    document.getElementById('invoice-status').className = 'badge badge-' + status;
    
    // Update payment amount field
    const amountInput = document.getElementById('amount');
    amountInput.max = balance;
    amountInput.value = balance.toFixed(2);
    document.getElementById('max-amount').textContent = formatCurrency(balance);
    
    // Update summary
    updatePaymentSummary(grandTotal, paidTotal, balance);
    
    // Show invoice details
    document.getElementById('invoice-details').style.display = 'block';
    document.getElementById('payment-summary').style.display = 'block';
    document.getElementById('no-invoice-selected').style.display = 'none';
}

function hideInvoiceDetails() {
    document.getElementById('invoice-details').style.display = 'none';
    document.getElementById('payment-summary').style.display = 'none';
    document.getElementById('no-invoice-selected').style.display = 'block';
    
    // Clear amount field
    const amountInput = document.getElementById('amount');
    amountInput.value = '';
    amountInput.max = '';
}

function updatePaymentSummary(grandTotal, paidTotal, balance) {
    document.getElementById('summary-total').textContent = formatCurrency(grandTotal);
    document.getElementById('summary-paid').textContent = formatCurrency(paidTotal);
    document.getElementById('summary-balance').textContent = formatCurrency(balance);
    
    // Update based on current payment amount
    updatePaymentAmount();
}

function updatePaymentAmount() {
    const amountInput = document.getElementById('amount');
    const paymentAmount = parseFloat(amountInput.value) || 0;
    const balance = parseFloat(amountInput.max) || 0;
    const remaining = balance - paymentAmount;
    
    document.getElementById('summary-payment').textContent = formatCurrency(paymentAmount);
    document.getElementById('summary-remaining').textContent = formatCurrency(remaining);
    
    // Update remaining balance color
    const remainingElement = document.getElementById('summary-remaining');
    remainingElement.style.color = remaining > 0 ? '#dc3545' : '#28a745';
}

function formatCurrency(amount) {
    return ' + amount.toFixed(2);
}

// Form validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    <?php if (!$isEdit): ?>
    const invoiceId = document.getElementById('invoice_id').value;
    const amount = parseFloat(document.getElementById('amount').value);
    const method = document.getElementById('method').value;
    
    if (!invoiceId) {
        e.preventDefault();
        alert('Please select an invoice.');
        return false;
    }
    
    if (!amount || amount <= 0) {
        e.preventDefault();
        alert('Please enter a valid payment amount.');
        return false;
    }
    
    if (!method) {
        e.preventDefault();
        alert('Please select a payment method.');
        return false;
    }
    
    const maxAmount = parseFloat(document.getElementById('amount').max);
    if (amount > maxAmount) {
        e.preventDefault();
        alert('Payment amount cannot exceed the outstanding balance.');
        return false;
    }
    <?php endif; ?>
});

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$isEdit): ?>
    // Update payment summary when amount changes
    document.getElementById('amount').addEventListener('input', updatePaymentAmount);
    
    // Load invoice details if pre-selected
    <?php if (isset($invoice)): ?>
    updatePaymentSummary(<?= $invoice['grand_total'] ?>, <?= $invoice['paid_total'] ?>, <?= $invoice['grand_total'] - $invoice['paid_total'] ?>);
    <?php endif; ?>
    <?php endif; ?>
    
    console.log('Payment Form Loaded');
    console.log('Edit Mode:', <?= $isEdit ? 'true' : 'false' ?>);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
