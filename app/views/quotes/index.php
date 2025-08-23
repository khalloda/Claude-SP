<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.quotes');
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= I18n::t('navigation.quotes') ?></h1>
        <div style="display: flex; gap: 1rem;">
            <a href="/salesorders" class="btn btn-secondary">
                View Sales Orders
            </a>
            <a href="/quotes/create" class="btn btn-primary">
                + Create Quote
            </a>
        </div>
    </div>

    <!-- Quotes Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        <?= count(array_filter($quotes['data'] ?? [], fn($q) => ($q['status'] ?? '') === 'sent')) ?>
                    </h3>
                    <p class="text-muted">Sent</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?= count(array_filter($quotes['data'] ?? [], fn($q) => ($q['status'] ?? '') === 'approved')) ?>
                    </h3>
                    <p class="text-muted">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-danger">
                        <?= count(array_filter($quotes['data'] ?? [], fn($q) => ($q['status'] ?? '') === 'rejected')) ?>
                    </h3>
                    <p class="text-muted">Rejected</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-primary">
                        <?php 
                        $totalValue = array_sum(array_map(fn($q) => $q['grand_total'] ?? 0, $quotes['data'] ?? []));
                        echo Helpers::formatCurrency($totalValue);
                        ?>
                    </h3>
                    <p class="text-muted">Total Value</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/quotes" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Search</label>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Client name, notes, quote ID..." 
                           value="<?= Helpers::escape($search ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-secondary d-block w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <?php if (!empty($search)): ?>
                        <a href="/quotes" class="btn btn-outline-secondary d-block w-100">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Quotes Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($quotes['data'])): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Quote #</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotes['data'] as $quote): ?>
                                <tr>
                                    <td>
                                        <a href="/quotes/<?= $quote['id'] ?>" style="text-decoration: none; color: #667eea;">
                                            #<?= str_pad($quote['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= Helpers::escape($quote['client_name'] ?? 'Unknown Client') ?></strong>
                                            <br><small class="text-muted">
                                                <?= ucfirst($quote['client_type'] ?? 'Company') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $quote['status'] ?>">
                                            <?= ucfirst($quote['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= Helpers::formatCurrency($quote['grand_total']) ?></td>
                                    <td><?= Helpers::formatDate($quote['created_at']) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="/quotes/<?= $quote['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                                            
                                            <?php if ($quote['status'] === 'approved'): ?>
                                                <form method="POST" action="/salesorders/create-from-quote" style="display: inline;">
                                                    <?= Helpers::csrfField() ?>
                                                    <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">Convert to Order</button>
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
                <?php if ($quotes['last_page'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($quotes['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $quotes['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $quotes['last_page']; $i++): ?>
                                <li class="page-item <?= $i === $quotes['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($quotes['current_page'] < $quotes['last_page']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $quotes['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            Showing <?= $quotes['from'] ?> to <?= $quotes['to'] ?> of <?= $quotes['total'] ?> results
                        </small>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <h5 class="text-muted">No quotes found</h5>
                    <p class="text-muted">
                        <?php if (!empty($search)): ?>
                            No quotes match your search criteria.
                        <?php else: ?>
                            Get started by creating your first quote.
                        <?php endif; ?>
                    </p>
                    <a href="/quotes/create" class="btn btn-primary">Create Quote</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
