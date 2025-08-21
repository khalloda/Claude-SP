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
                                <td><?= Helpers::escape
