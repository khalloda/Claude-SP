<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.clients') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= I18n::t('navigation.clients') ?></h1>
            <a href="/clients/create" class="btn btn-primary"><?= I18n::t('actions.create') ?></a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="/clients" style="margin-bottom: 2rem;">
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
                    <a href="/clients" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Clients Table -->
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
                    <?php if (!empty($clients['data'])): ?>
                        <?php foreach ($clients['data'] as $client): ?>
                            <tr>
                                <td>
                                    <a href="/clients/<?= $client['id'] ?>" style="text-decoration: none; color: #667eea;">
                                        <?= Helpers::escape($client['name']) ?>
                                    </a>
                                </td>
                                <td><?= Helpers::escape(ucfirst($client['type'])) ?></td>
                                <td><?= Helpers::escape($client['email']) ?></td>
                                <td><?= Helpers::escape($client['phone']) ?></td>
                                <td><?= Helpers::formatDate($client['created_at']) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="/clients/<?= $client['id'] ?>" class="btn btn-sm btn-secondary"><?= I18n::t('actions.view') ?></a>
                                        <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-sm btn-primary"><?= I18n::t('actions.edit') ?></a>
                                        <form method="POST" action="/clients/<?= $client['id'] ?>/delete" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this client?')">
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
                                No clients found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($clients['last_page'] > 1): ?>
            <div class="pagination-wrapper">
                <?php
                $currentPage = $clients['current_page'];
                $lastPage = $clients['last_page'];
                $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
                ?>
                
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="/clients?page=<?= $currentPage - 1 ?><?= $searchParam ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="btn btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/clients?page=<?= $i ?><?= $searchParam ?>" class="btn btn-secondary"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $lastPage): ?>
                        <a href="/clients?page=<?= $currentPage + 1 ?><?= $searchParam ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; color: #666;">
                    Showing <?= $clients['from'] ?> to <?= $clients['to'] ?> of <?= $clients['total'] ?> results
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
