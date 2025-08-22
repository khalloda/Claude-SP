<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.quotes') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= I18n::t('navigation.quotes') ?></h1>
            <a href="/quotes/create" class="btn btn-primary"><?= I18n::t('actions.create') ?> Quote</a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="/quotes" style="margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem;">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search quotes, clients..." 
                    value="<?= Helpers::escape($search ?? '') ?>"
                    style="flex: 1;"
                >
                <button type="submit" class="btn btn-secondary"><?= I18n::t('actions.search') ?></button>
                <?php if (!empty($search)): ?>
                    <a href="/quotes" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Quotes Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Client</th>
                        <th><?= I18n::t('common.status') ?></th>
                        <th>Grand Total</th>
                        <th><?= I18n::t('common.created_at') ?></th>
                        <th><?= I18n::t('common.action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($quotes['data'])): ?>
                        <?php foreach ($quotes['data'] as $quote): ?>
                            <tr>
                                <td>
                                    <a href="/quotes/<?= $quote['id'] ?>" style="text-decoration: none; color: #667eea;">
                                        #<?= str_pad($quote['id'], 4, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= Helpers::escape($quote['client_name'] ?? 'Unknown') ?></strong>
                                        <small style="display: block; color: #666;">
                                            <?= ucfirst($quote['client_type'] ?? 'company') ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $quote['status'] ?>">
                                        <?= ucfirst($quote['status']) ?>
                                    </span>
                                </td>
                                <td><?= Helpers::formatCurrency($quote['grand_total']) ?></td>
                                <td><?= Helpers::formatDate($quote['created_at'] ?? date('Y-m-d H:i:s')) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <a href="/quotes/<?= $quote['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                        
                                        <?php if ($quote['status'] === 'sent'): ?>
                                            <a href="/quotes/<?= $quote['id'] ?>/edit" class="btn btn-sm btn-primary"><?= I18n::t('actions.edit') ?></a>
                                            
                                            <form method="POST" action="/quotes/<?= $quote['id'] ?>/approve" style="display: inline;" 
                                                  onsubmit="return confirm('Approve this quote?')">
                                                <?= Helpers::csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            
                                            <form method="POST" action="/quotes/<?= $quote['id'] ?>/reject" style="display: inline;" 
                                                  onsubmit="return confirm('Reject this quote?')">
                                                <?= Helpers::csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($quote['status'] === 'approved'): ?>
                                            <form method="POST" action="/quotes/<?= $quote['id'] ?>/convert-to-order" style="display: inline;" 
                                                  onsubmit="return confirm('Convert this quote to a sales order?')">
                                                <?= Helpers::csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-success">Convert to Order</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($quote['status'] !== 'approved'): ?>
                                            <form method="POST" action="/quotes/<?= $quote['id'] ?>/delete" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this quote?')">
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
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">
                                No quotes found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($quotes['last_page']) && $quotes['last_page'] > 1): ?>
            <div class="pagination-wrapper">
                <?php
                $currentPage = $quotes['current_page'];
                $lastPage = $quotes['last_page'];
                $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
                ?>
                
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="/quotes?page=<?= $currentPage - 1 ?><?= $searchParam ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="btn btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/quotes?page=<?= $i ?><?= $searchParam ?>" class="btn btn-secondary"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $lastPage): ?>
                        <a href="/quotes?page=<?= $currentPage + 1 ?><?= $searchParam ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; color: #666;">
                    Showing <?= $quotes['from'] ?> to <?= $quotes['to'] ?> of <?= $quotes['total'] ?> results
                </div>
            </div>
        <?php endif; ?>
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

.badge-sent { background-color: #6c757d; }
.badge-approved { background-color: #28a745; }
.badge-rejected { background-color: #dc3545; }

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
    
    .btn-sm {
        margin-bottom: 0.25rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
