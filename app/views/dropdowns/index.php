<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = 'Dropdown Management - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title">Dropdown Management</h1>
            <a href="/dropdowns/create" class="btn btn-primary"><?= I18n::t('actions.create') ?></a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Category Filter -->
        <form method="GET" action="/dropdowns" style="margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem; align-items: end;">
                <div style="flex: 1;">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select name="category" id="category" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <!-- Dropdowns by Category -->
        <?php foreach ($categories as $categoryKey => $categoryLabel): ?>
            <?php if (empty($category) || $category === $categoryKey): ?>
                <div class="category-section" style="margin-bottom: 3rem;">
                    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="color: #667eea; margin: 0;"><?= $categoryLabel ?></h3>
                        <a href="/dropdowns/create?parent_category=<?= $categoryKey ?>" class="btn btn-sm btn-primary">
                            Add <?= $categoryLabel ?>
                        </a>
                    </div>
                    
                    <?php if (isset($dropdowns[$categoryKey])): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Value</th>
                                        <?php if ($categoryKey === 'car_model'): ?>
                                            <th>Car Make</th>
                                        <?php endif; ?>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dropdowns[$categoryKey] as $item): ?>
                                        <tr>
                                            <td><?= Helpers::escape($item['value']) ?></td>
                                            <?php if ($categoryKey === 'car_model'): ?>
                                                <td>
                                                    <?php if ($item['parent_id']): ?>
                                                        <?php
                                                        // Find parent car make
                                                        $parentMake = '';
                                                        if (isset($dropdowns['car_make'])) {
                                                            foreach ($dropdowns['car_make'] as $make) {
                                                                if ($make['id'] == $item['parent_id']) {
                                                                    $parentMake = $make['value'];
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <?= Helpers::escape($parentMake) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            <td><?= Helpers::formatDate($item['created_at']) ?></td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <a href="/dropdowns/<?= $item['id'] ?>/edit" class="btn btn-sm btn-primary">Edit</a>
                                                    <form method="POST" action="/dropdowns/<?= $item['id'] ?>/delete" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure? This will also delete any dependent items.')">
                                                        <?= Helpers::csrfField() ?>
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color: #666; font-style: italic;">No items in this category yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<style>
.category-section {
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 1.5rem;
    background: #fafbfc;
}

.category-section h3 {
    border-bottom: 2px solid #667eea;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

.table {
    background: white;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
