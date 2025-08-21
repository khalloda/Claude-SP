<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.suppliers') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= I18n::t('navigation.suppliers') ?></h1>
            <a href="/suppliers/create" class="btn btn-primary"><?= I18n::t('actions.create') ?></a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="/suppliers" style="margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem;">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="<?= I18n::t('actions.search') ?>..." 
                    value="<?= Helpers::escape($search) ?>"
                    style="flex: 1;"
                >
                <button type="submit" class="btn btn-secondary"><?= I18n::t('actions.search') ?></button>
                <?php if (!empty($search)): ?>
                    <a href="/suppliers" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Suppliers Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= I18n::t('common.name') ?></th>
                        <th><?= I18n::t('common.type') ?></th>
                        <th><?= I18n::t('common.email') ?></th>
                        <th><?= I18n::t('common.phone') ?></th>
                        <th><?= I18n::t('common.created_at') ?></th>
                        <th><?= I18n::t('common.action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($suppliers['data'])): ?>
                        <?php foreach ($suppliers['data'] as $supplier): ?>
                            <tr>
                                <td>
                                    <a href="/suppliers/<?= $supplier['id'] ?>" style="text-decoration: none; color: #667eea;">
                                        <?= Helpers::escape($supplier['name']) ?>
                                    </a>
                                </td>
                                <td><?= Helpers::escape(ucfirst($supplier['type'])) ?></td>
                                <td><?= Helpers::escape($supplier['email']) ?></td>
                                <td><?= Helpers::escape($supplier['phone']) ?></td>
                                <td><?= Helpers::formatDate($supplier['created_at']) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="/suppliers/<?= $supplier['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                        <a href="/suppliers/<?= $supplier['id'] ?>/edit" class="btn btn-sm btn-primary"><?= I18n::t('actions.edit') ?></a>
                                        <form method="POST" action="/suppliers/<?= $supplier['id'] ?>/delete" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this supplier?')">
                                            <?= Helpers::csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-danger"><?= I18n::t('actions.delete') ?></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">
                                No suppliers found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($suppliers['last_page'] > 1): ?>
            <div class="pagination-wrapper">
                <?php
                $currentPage = $suppliers['current_page'];
                $lastPage = $suppliers['last_page'];
                $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
                ?>
                
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="/suppliers?page=<?= $currentPage - 1 ?><?= $searchParam ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="btn btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/suppliers?page=<?= $i ?><?= $searchParam ?>" class="btn btn-secondary"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $lastPage): ?>
                        <a href="/suppliers?page=<?= $currentPage + 1 ?><?= $searchParam ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; color: #666;">
                    Showing <?= $suppliers['from'] ?> to <?= $suppliers['to'] ?> of <?= $suppliers['total'] ?> results
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
