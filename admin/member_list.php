<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Member Management - Admin';

// === Parameter handling ===
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = (int)($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');

$where = "WHERE u.role = 'member'";
$params = [];

if ($search !== '') {
    $where .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Total members count
$count_stmt = $_db->prepare("SELECT COUNT(*) FROM users u $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $limit));

// Predefined lock reasons
$predefined_reasons = [
    '' => '— No reason —',
    'Suspicious activity' => 'Suspicious activity',
    'Multiple failed logins' => 'Multiple failed logins',
    'Violation of terms' => 'Violation of terms',
    'Payment issues' => 'Payment issues',
    'Spam / fake account' => 'Spam / fake account',
    'Requested by user' => 'Requested by user',
    'Other' => 'Other (type below)'
];

// Handle lock/unlock action
if (is_post() && post('action') === 'toggle_lock' && post('member_id')) {
    $member_id = (int)post('member_id');
    $selected_reason = post('predefined_reason') ?? '';
    $custom_reason = trim(post('custom_reason') ?? '');

    // Determine final reason
    $final_reason = '';
    if ($selected_reason === 'Other') {
        $final_reason = $custom_reason !== '' ? $custom_reason : 'Other (no details provided)';
    } elseif ($selected_reason !== '' && $selected_reason !== '— No reason —') {
        $final_reason = $selected_reason;
    }

    // Get current locked status
    $stmt = $_db->prepare("SELECT locked FROM users WHERE id = ? AND role = 'member'");
    $stmt->execute([$member_id]);
    $current = $stmt->fetchColumn();

    if ($current !== false) {
        $new_status = $current ? 0 : 1;

        if ($new_status == 1) {
            // Locking → update status + reason
            $sql = "UPDATE users SET locked = ?, lock_reason = ? WHERE id = ? AND role = 'member'";
            $_db->prepare($sql)->execute([$new_status, $final_reason, $member_id]);
        } else {
            // Unlocking → only change status (reason kept for history)
            $_db->prepare("UPDATE users SET locked = ? WHERE id = ? AND role = 'member'")
                ->execute([$new_status, $member_id]);
        }

        $status_text = $new_status ? 'locked' : 'unlocked';
        temp('info', "Member account has been $status_text ♡");
    }
    redirect("member_list.php?page=$page&limit=$limit&search=" . urlencode($search));
}

// Fetch members
$sql = "
    SELECT u.id, u.username, u.email, u.phone, u.created_at, u.locked, u.lock_reason,
           COUNT(o.order_id) AS total_orders
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?> • Moe Moe Pet Mart</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .reason-group { margin-bottom: 8px; }
        .custom-reason { display: none; margin-top: 6px; }
        .custom-reason textarea { width: 100%; padding: 6px; font-size: 0.9rem; border: 1px solid #ff69b4; border-radius: 4px; }
        select { width: 100%; padding: 6px; border: 1px solid #ff69b4; border-radius: 4px; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar same as before -->
    <aside class="admin-sidebar">
        <div class="logo"><h2>MoeMoePet</h2></div>
         <ul>
            <li><a href="/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/admin/product_list.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="/admin/member_list.php"><i class="fas fa-users"></i> Members</a></li>
            <li><a href="/admin/order_list.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="/admin/review_list.php"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="/admin/chat_list.php"><i class="fas fa-comments"></i> Chats</a></li>
            <li><a href="/admin/report.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="/admin/profile.php"><i class="fas fa-user-cog"></i> My Profile ♛</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <h1>Member Management ♡</h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?= encode($_SESSION['user']) ?></span>
            </div>
        </header>

        <div class="toolbar">
            <input type="text" name="search" placeholder="Search by username, email or phone..." value="<?= encode($search) ?>">
            <button onclick="applyFilters()">Apply</button>
        </div>

        <div class="table-card">
            <?php if (empty($members)): ?>
                <div class="empty-full" style="text-align:center;padding:60px;color:#999;">
                    No members registered yet~<br>Waiting for our first cute customer! ♡
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Join Date</th>
                            <th>Status</th>
                            <th>Status Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                        <tr>
                            <td><strong><?= encode($m->username) ?></strong></td>
                            <td><?= encode($m->email ?? '—') ?></td>
                            <td><?= encode($m->phone ?? '—') ?></td>
                            <td><?= date('d M Y', strtotime($m->created_at)) ?></td>
                            <td>
                                <span class="status-badge <?= $m->locked ? 'locked' : 'active' ?>">
                                    <?= $m->locked ? 'Locked' : 'Active' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($m->locked && !empty($m->lock_reason)): ?>
                                    <span style="font-size:0.85rem; color:#e91e63;"><?= encode($m->lock_reason) ?></span>
                                <?php elseif ($m->locked): ?>
                                    <span style="font-size:0.85rem; color:#999;">No reason provided</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="action-links">
                                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to <?= $m->locked ? 'unlock' : 'lock' ?> this account?')">
                                    <input type="hidden" name="action" value="toggle_lock">
                                    <input type="hidden" name="member_id" value="<?= $m->id ?>">

                                    <?php if (!$m->locked): ?>
                                        <div class="reason-group">
                                            <select name="predefined_reason" onchange="toggleCustom(this)">
                                                <?php foreach ($predefined_reasons as $value => $text): ?>
                                                    <option value="<?= encode($value) ?>"><?= encode($text) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="custom-reason" id="custom_<?= $m->id ?>">
                                            <textarea name="custom_reason" rows="2" placeholder="Type your custom reason here ♡"></textarea>
                                        </div>
                                    <?php else: ?>
                                        <div style="margin-bottom:8px; color:#999; font-style:italic;">
                                            Unlocking will not clear existing reason
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="text-pink-500 hover:text-pink-700 font-medium transition" 
                                            style="background:none;border:none;cursor:pointer;">
                                        <?= $m->locked ? '🔓 Unlock' : '🔒 Lock' ?> ♡
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Pagination same as before -->
            <?php if ($total > $limit): ?>
            <div class="pagination">
                <span>Page <?= $page ?> of <?= $total_pages ?></span>
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Previous</a>
                <?php endif; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Next</a>
                <?php endif; ?>
                <span><?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> of <?= $total ?> members</span>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function applyFilters() {
    const search = document.querySelector('input[name="search"]').value.trim();
    const url = new URL(location);
    url.searchParams.set('search', search);
    url.searchParams.set('page', 1);
    location = url.toString();
}

// Toggle custom reason textarea
function toggleCustom(select) {
    const row = select.closest('tr');
    const memberId = row.querySelector('input[name="member_id"]').value;
    const customDiv = document.getElementById('custom_' + memberId);
    
    if (select.value === 'Other') {
        customDiv.style.display = 'block';
    } else {
        customDiv.style.display = 'none';
    }
}

// Initialize on page load (in case of validation errors, but not needed here)
document.querySelectorAll('select[name="predefined_reason"]').forEach(toggleCustom);
</script>

</body>
</html>