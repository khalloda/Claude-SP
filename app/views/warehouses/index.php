<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.warehouses') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= I18n::t('navigation.warehouses') ?></h1>
            <a href="/warehouses/create" class="btn btn-primary"><?= I18n::t('actions.create') ?></a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="/warehouses" style="margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem;">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="<?= I18n::t('actions.search') ?>..." 
                    value="<?= Helpers::escape($search ?? '') ?>"
                    style="flex: 1;"
                >
                <button type="submit" class="btn btn-secondary"><?= I18n::t('actions.search') ?></button>
                <?php if (!empty($search)): ?>
                    <a href="/warehouses" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Warehouses Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= I18n::t('common.name') ?></th>
                        <th>Responsible Person</th>
                        <th><?= I18n::t('common.phone') ?></th>
                        <th>Capacity</th>
                        <th><?= I18n::t('common.created_at') ?></th>
                        <th><?= I18n::t('common.action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($warehouses['data'])): ?>
                        <?php foreach ($warehouses['data'] as $warehouse): ?>
                            <tr>
                                <td>
                                    <a href="/warehouses/<?= $warehouse['id'] ?>" style="text-decoration: none; color: #667eea;">
                                        <?= Helpers::escape($warehouse['name'] ?? '') ?>
                                    </a>
                                </td>
                                <td><?= Helpers::escape($warehouse['responsible_name'] ?? '') ?></td>
                                <td><?= Helpers::escape($warehouse['responsible_phone'] ?? '') ?></td>
                                <td>
                                    <?= $warehouse['capacity'] ? number_format($warehouse['capacity'], 0) . ' m²' : '-' ?>
                                </td>
                                <td><?= Helpers::formatDate($warehouse['created_at'] ?? date('Y-m-d H:i:s')) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="/warehouses/<?= $warehouse['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                        <a href="/warehouses/<?= $warehouse['id'] ?>/edit" class="btn btn-sm btn-primary"><?= I18n::t('actions.edit') ?></a>
                                        <form method="POST" action="/warehouses/<?= $warehouse['id'] ?>/delete" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this warehouse?')">
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
                                No warehouses found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($warehouses['last_page'] > 1): ?>
            <div class="pagination-wrapper">
                <?php
                $currentPage = $warehouses['current_page'];
                $lastPage = $warehouses['last_page'];
                $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
                ?>
                
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="/warehouses?page=<?= $currentPage - 1 ?><?= $searchParam ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="btn btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/warehouses?page=<?= $i ?><?= $searchParam ?>" class="btn btn-secondary"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $lastPage): ?>
                        <a href="/warehouses?page=<?= $currentPage + 1 ?><?= $searchParam ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; color: #666;">
                    Showing <?= $warehouses['from'] ?> to <?= $warehouses['to'] ?> of <?= $warehouses['total'] ?> results
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
