<?php
// Create this file: app/views/users/index.php

use App\Core\I18n;
use App\Core\Helpers;
use App\Core\Auth;

$title = 'User Management - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title">User Management</h1>
            <a href="/users/create" class="btn btn-primary">Add New User</a>
        </div>
    </div>

    <!-- User Statistics -->
    <?php if (!empty($stats)): ?>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['admins'] ?></div>
                    <div class="stat-label">Administrators</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['active'] ?></div>
                    <div class="stat-label">Active (30 days)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['recent'] ?></div>
                    <div class="stat-label">New (7 days)</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card-body border-top">
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search users by name or email..." 
                           value="<?= Helpers::escape($search ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="/users" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card-body">
        <?php if (!empty($users['items'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Language</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users['items'] as $user): ?>
                            <tr class="<?= $user['id'] == Auth::id() ? 'table-warning' : '' ?>">
                                <td>
                                    <?= $user['id'] ?>
                                    <?php if ($user['id'] == Auth::id()): ?>
                                        <span class="badge badge-info ms-1">You</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= Helpers::escape($user['name']) ?></strong>
                                </td>
                                <td><?= Helpers::escape($user['email']) ?></td>
                                <td>
                                    <?php if (!empty($user['roles'])): ?>
                                        <?php foreach (explode(',', $user['roles']) as $role): ?>
                                            <?php 
                                            $badgeClass = match(trim($role)) {
                                                'admin' => 'badge-danger',
                                                'manager' => 'badge-warning',
                                                default => 'badge-info'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= Helpers::escape(trim($role)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No roles</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['locale'] === 'en'): ?>
                                        <span class="badge badge-primary">English</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">العربية</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Helpers::formatDate($user['created_at']) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/users/<?= $user['id'] ?>" class="btn btn-outline-info">View</a>
                                        <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-outline-primary">Edit</a>
                                        <?php if ($user['id'] != Auth::id()): ?>
                                            <form method="POST" action="/users/<?= $user['id'] ?>/delete" style="display: inline;">
                                                <?= Helpers::csrfField() ?>
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Delete user <?= Helpers::escape($user['name']) ?>?\n\nThis action cannot be undone.')">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="btn btn-outline-secondary disabled">Cannot Delete</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($users['pagination']['total_pages'] > 1): ?>
                <nav aria-label="User pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($users['pagination']['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $users['pagination']['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $users['pagination']['total_pages']; $i++): ?>
                            <li class="page-item <?= $i == $users['pagination']['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($users['pagination']['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $users['pagination']['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <h3 class="text-muted">
                    <?= !empty($search) ? 'No users found matching your search' : 'No Users Found' ?>
                </h3>
                <?php if (!empty($search)): ?>
                    <p>Try adjusting your search terms or <a href="/users">view all users</a>.</p>
                <?php else: ?>
                    <p>Start by adding the first user to the system.</p>
                    <a href="/users/create" class="btn btn-primary btn-lg">Add First User</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 1rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.badge {
    font-size: 0.75rem;
    margin-right: 0.25rem;
}

.badge-primary { background-color: #007bff; }
.badge-success { background-color: #28a745; }
.badge-info { background-color: #17a2b8; }
.badge-warning { background-color: #ffc107; color: #000; }
.badge-danger { background-color: #dc3545; }

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

@media (max-width: 768px) {
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
