<?php
require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];

// Handle mark as read (AJAX or standard POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action         = $_POST['action']          ?? '';
    $notificationId = (int)($_POST['notification_id'] ?? 0);

    if ($action === 'mark_read' && $notificationId > 0) {
        markNotificationAsRead($notificationId);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        redirect('notifications.php', 'Notification marked as read.', 'success');
    }

    if ($action === 'mark_all_read') {
        $conn->query("UPDATE notifications SET is_read = 'Yes' WHERE user_id = $userId");
        redirect('notifications.php', 'All notifications marked as read.', 'success');
    }
}

// Fetch notifications
$notifications = $conn->query(
    "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC LIMIT 50"
)->fetch_all(MYSQLI_ASSOC);

$unreadCount = count(array_filter($notifications, fn($n) => $n['is_read'] === 'No'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Notifications - Project Rock Ascent</title>
    <?php include('../includes/head.php'); ?>
    <style>
        /* ── Page Header ─────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 36px;
        }
        .page-title-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .page-icon {
            width: 56px; height: 56px;
            border-radius: 18px;
            background: var(--primary-gradient);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            box-shadow: 0 12px 30px rgba(236, 72, 153, 0.3);
            flex-shrink: 0;
        }
        .page-title-text h2 {
            font-size: 2rem !important;
            font-weight: 900 !important;
            margin-bottom: 0 !important;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }
        .page-title-text p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }
        .unread-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
            font-size: 12px;
            font-weight: 800;
            padding: 5px 14px;
            border-radius: 20px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .btn-mark-all {
            background: rgba(245,158,11,0.1);
            border: 1px solid rgba(245,158,11,0.3);
            color: #f59e0b;
            font-size: 13px !important;
            font-weight: 700 !important;
            padding: 10px 22px !important;
            border-radius: 16px !important;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: none !important;
            letter-spacing: 0 !important;
        }
        .btn-mark-all:hover {
            background: rgba(245,158,11,0.2);
            border-color: #f59e0b;
            transform: translateY(-2px);
        }

        /* ── Notification Cards ───────────────────────────────── */
        .notif-list { display: flex; flex-direction: column; gap: 16px; }

        .notif-card {
            display: flex;
            gap: 18px;
            align-items: flex-start;
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            padding: 22px 24px;
            transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease both;
        }
        .notif-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(225deg, rgba(255,255,255,0.04) 0%, transparent 60%);
            pointer-events: none;
        }
        .notif-card:hover {
            transform: translateY(-4px);
            border-color: rgba(245,158,11,0.35);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .notif-card.unread {
            border-left: 3px solid #f59e0b;
            background: rgba(245,158,11,0.04);
        }
        .notif-card.unread:hover {
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(245,158,11,0.2);
        }

        /* Stagger animation */
        .notif-card:nth-child(1)  { animation-delay: .05s; }
        .notif-card:nth-child(2)  { animation-delay: .10s; }
        .notif-card:nth-child(3)  { animation-delay: .15s; }
        .notif-card:nth-child(4)  { animation-delay: .20s; }
        .notif-card:nth-child(5)  { animation-delay: .25s; }
        .notif-card:nth-child(n+6){ animation-delay: .30s; }

        /* ── Avatar Icon ── */
        .notif-avatar {
            width: 50px; height: 50px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .notif-avatar.av-payment    { background: linear-gradient(135deg,#22c55e,#16a34a); box-shadow: 0 8px 20px rgba(34,197,94,0.25); }
        .notif-avatar.av-workout    { background: linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow: 0 8px 20px rgba(99,102,241,0.25); }
        .notif-avatar.av-diet       { background: linear-gradient(135deg,#10b981,#06b6d4); box-shadow: 0 8px 20px rgba(16,185,129,0.25); }
        .notif-avatar.av-admin      { background: linear-gradient(135deg,#f59e0b,#ec4899); box-shadow: 0 8px 20px rgba(245,158,11,0.25); }
        .notif-avatar.av-instructor { background: linear-gradient(135deg,#3b82f6,#6366f1); box-shadow: 0 8px 20px rgba(59,130,246,0.25); }
        .notif-avatar.av-system     { background: linear-gradient(135deg,#f97316,#ef4444); box-shadow: 0 8px 20px rgba(249,115,22,0.25); }
        .notif-avatar.av-default    { background: rgba(255,255,255,0.07); }

        /* ── Content ── */
        .notif-body { flex: 1; min-width: 0; position: relative; z-index: 1; }

        .notif-top {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .notif-title {
            font-size: 15px;
            font-weight: 800;
            color: #f1f5f9;
            margin: 0;
            line-height: 1.3;
        }
        .pill-new {
            background: var(--primary-gradient);
            color: white;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
        }
        .pill-read {
            background: rgba(34,197,94,0.12);
            color: #4ade80;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
            border: 1px solid rgba(74,222,128,0.2);
        }

        .notif-message {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.7;
            margin: 0 0 12px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .notif-meta {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }
        .meta-chip {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: #64748b; font-weight: 500;
        }
        .meta-chip i { font-size: 11px; }

        /* ── Mark as Read Button ── */
        .btn-mark-read {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            border: 1px solid rgba(245,158,11,0.35);
            color: #f59e0b;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            font-family: inherit;
        }
        .btn-mark-read:hover {
            background: rgba(245,158,11,0.12);
            border-color: #f59e0b;
            transform: translateY(-1px);
        }
        .btn-mark-read:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

        /* ── Divider line on right (unread dot) ── */
        .notif-card.unread::after {
            content: '';
            position: absolute;
            top: 22px; right: 24px;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #f59e0b;
            box-shadow: 0 0 8px rgba(245,158,11,0.6);
        }

        /* ── Empty State ── */
        .notif-empty {
            text-align: center;
            padding: 80px 20px;
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 28px;
        }
        .notif-empty .empty-icon {
            width: 90px; height: 90px;
            border-radius: 28px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-glass);
            display: flex; align-items: center; justify-content: center;
            font-size: 38px;
            margin: 0 auto 24px;
        }
        .notif-empty h5 {
            font-size: 20px;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 10px;
        }
        .notif-empty p {
            color: var(--text-muted);
            font-size: 14px;
            max-width: 320px;
            margin: 0 auto;
        }

        /* ── Stats Strip ── */
        .notif-stats {
            display: flex;
            gap: 14px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }
        .stat-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--card-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 12px 20px;
            flex: 1;
            min-width: 120px;
        }
        .stat-chip-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .stat-chip-icon.orange { background: rgba(245,158,11,0.15); }
        .stat-chip-icon.green  { background: rgba(34,197,94,0.12); }
        .stat-chip-icon.blue   { background: rgba(59,130,246,0.12); }
        .stat-chip-val  { font-size: 20px; font-weight: 900; color: #f1f5f9; line-height: 1; }
        .stat-chip-label{ font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
    </style>
</head>
<body>
<?php include('../includes/header.php'); ?>

<div class="container-fluid">
    <div class="dashboard-container">

        <!-- Shared Sidebar -->
        <?php include('../includes/user_sidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content">

            <?php displayMessage(); ?>

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-wrap">
                    <div class="page-icon">🔔</div>
                    <div class="page-title-text">
                        <h2>Notifications</h2>
                        <p>Stay updated with your gym activity</p>
                    </div>
                    <?php if ($unreadCount > 0): ?>
                        <span class="unread-badge">
                            <i class="fas fa-circle" style="font-size:7px;"></i>
                            <?php echo $unreadCount; ?> unread
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($unreadCount > 0): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-mark-all">
                        <i class="fas fa-check-double mr-2"></i>Mark all as read
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Stats Strip -->
            <?php
            $totalCount = count($notifications);
            $readCount  = $totalCount - $unreadCount;
            ?>
            <div class="notif-stats">
                <div class="stat-chip">
                    <div class="stat-chip-icon orange"><i class="fas fa-bell" style="color:#f59e0b;"></i></div>
                    <div>
                        <div class="stat-chip-val"><?php echo $totalCount; ?></div>
                        <div class="stat-chip-label">Total</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <div class="stat-chip-icon blue"><i class="fas fa-envelope-open" style="color:#60a5fa;"></i></div>
                    <div>
                        <div class="stat-chip-val"><?php echo $unreadCount; ?></div>
                        <div class="stat-chip-label">Unread</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <div class="stat-chip-icon green"><i class="fas fa-check-circle" style="color:#4ade80;"></i></div>
                    <div>
                        <div class="stat-chip-val"><?php echo $readCount; ?></div>
                        <div class="stat-chip-label">Read</div>
                    </div>
                </div>
            </div>

            <!-- Notification List -->
            <?php if (!empty($notifications)): ?>
            <div class="notif-list" id="notifList">
                <?php foreach ($notifications as $notif):
                    $isUnread    = $notif['is_read'] === 'No';
                    $sender      = strtolower($notif['sender_type'] ?? 'system');
                    $title       = htmlspecialchars($notif['title']   ?? 'Notification');
                    $message     = htmlspecialchars($notif['message'] ?? '');
                    $nId         = (int)$notif['notification_id'];
                    $date        = date('d M Y, h:i A', strtotime($notif['created_at']));

                    // Smart icon + avatar class
                    $icon = '🔔'; $avClass = 'av-default';
                    if (stripos($title,'payment') !== false || stripos($title,'received') !== false || stripos($title,'paid') !== false) {
                        $icon = '💳'; $avClass = 'av-payment';
                    } elseif (stripos($title,'workout') !== false || stripos($title,'exercise') !== false) {
                        $icon = '🏋️'; $avClass = 'av-workout';
                    } elseif (stripos($title,'diet') !== false || stripos($title,'nutrition') !== false || stripos($title,'meal') !== false) {
                        $icon = '🥗'; $avClass = 'av-diet';
                    } elseif (stripos($title,'welcome') !== false || stripos($title,'joined') !== false) {
                        $icon = '🎉'; $avClass = 'av-system';
                    } elseif ($sender === 'admin') {
                        $icon = '👑'; $avClass = 'av-admin';
                    } elseif ($sender === 'instructor') {
                        $icon = '🏅'; $avClass = 'av-instructor';
                    } elseif (stripos($title,'alert') !== false || stripos($title,'warning') !== false) {
                        $icon = '⚠️'; $avClass = 'av-system';
                    }
                ?>
                <div class="notif-card <?php echo $isUnread ? 'unread' : ''; ?>" id="notif-<?php echo $nId; ?>">

                    <!-- Avatar -->
                    <div class="notif-avatar <?php echo $avClass; ?>"><?php echo $icon; ?></div>

                    <!-- Body -->
                    <div class="notif-body">
                        <div class="notif-top">
                            <h6 class="notif-title"><?php echo $title; ?></h6>
                            <?php if ($isUnread): ?>
                                <span class="pill-new">New</span>
                            <?php else: ?>
                                <span class="pill-read"><i class="fas fa-check mr-1"></i>Read</span>
                            <?php endif; ?>
                        </div>

                        <p class="notif-message"><?php echo nl2br(htmlspecialchars(cleanLiteralNewlines($notif['message'] ?? ''))); ?></p>

                        <div class="notif-meta">
                            <span class="meta-chip">
                                <i class="fas fa-user-tag"></i>
                                From: <?php echo ucfirst($sender); ?>
                            </span>
                            <span class="meta-chip">
                                <i class="fas fa-clock"></i>
                                <?php echo $date; ?>
                            </span>

                            <?php if ($isUnread): ?>
                            <button class="btn-mark-read" id="btn-<?php echo $nId; ?>" onclick="markRead(<?php echo $nId; ?>, this)">
                                <i class="fas fa-check"></i> Mark as read
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>
            <!-- Empty state -->
            <div class="notif-empty">
                <div class="empty-icon">🔕</div>
                <h5>You're all caught up!</h5>
                <p>No notifications yet. We'll let you know when something important happens.</p>
            </div>
            <?php endif; ?>

        </div><!-- /main-content -->
    </div><!-- /dashboard-container -->
</div>

<?php include('../includes/footer.php'); ?>

<?php include('../includes/scripts.php'); ?>
<script>
function markRead(id, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking…';

    fetch('notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=mark_read&notification_id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById('notif-' + id);
            card.classList.remove('unread');

            // Replace pill
            const pillNew = card.querySelector('.pill-new');
            if (pillNew) pillNew.outerHTML = '<span class="pill-read"><i class="fas fa-check mr-1"></i>Read</span>';

            // Replace button
            btn.outerHTML = '';

            // Remove orange dot (::after is CSS, so just kill unread class — already done)
            // Update stat chips
            updateStats(-1);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Mark as read';
    });
}

function updateStats(delta) {
    // Unread badge in header
    document.querySelectorAll('.unread-badge').forEach(el => {
        let m = el.textContent.match(/(\d+)/);
        if (m) {
            let v = parseInt(m[1]) + delta;
            if (v <= 0) {
                el.remove();
                // Also hide mark-all form
                const form = document.querySelector('form input[name="action"][value="mark_all_read"]');
                if (form) form.closest('form').style.display = 'none';
            } else {
                el.innerHTML = '<i class="fas fa-circle" style="font-size:7px;"></i> ' + v + ' unread';
            }
        }
    });
    // Stat strip chips (val divs — 2nd chip = unread, 3rd = read)
    const chips = document.querySelectorAll('.stat-chip-val');
    if (chips[1] && chips[2]) {
        let u = parseInt(chips[1].textContent) + delta;
        let r = parseInt(chips[2].textContent) - delta;
        chips[1].textContent = Math.max(0, u);
        chips[2].textContent = Math.max(0, r);
    }
}
</script>
</body>
</html>
