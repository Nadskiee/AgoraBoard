<?php
session_start();
require_once 'db_connect.php';

// 🔐 Auth check
if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

function sane($s)
{
    return htmlspecialchars(trim($s ?? ''), ENT_QUOTES, 'UTF-8');
}

// 🧭 Fetch polls and their options
$stmt = $pdo->prepare("
    SELECT p.*, u.first_name, u.last_name
    FROM polls p
    LEFT JOIN users u ON p.created_by = u.id
    WHERE p.deleted_at IS NULL
    ORDER BY p.created_at DESC
");


$stmt->execute();
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgoraBoard - Polls</title>
    <link rel="stylesheet" href="assets/dashboard.css?v=<?= time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="dashboard-layout d-flex">
        <?php include 'user_sidebar.php'; ?>

        <!-- ✅ Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <div class="main-header mb-4 d-flex justify-content-between align-items-center">
                <h3 style="font-weight:700;">🗳️ Community Polls</h3>
            </div>

            <!-- ✅ Create Poll Card -->
            <div class="post-card mb-4 p-4 shadow-sm">
                <form id="createPollForm" method="POST" action="poll_create.php">
                    <div class="mb-3">
                        <label for="pollQuestion" class="form-label fw-semibold">Poll Question</label>
                        <input type="text" id="pollQuestion" name="question" class="form-control input-underline" placeholder="e.g., What project feature should we prioritize next?" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Options</label>
                        <div id="pollOptionsContainer">
                            <div class="d-flex gap-2 mb-2">
                                <input type="text" name="options[]" class="form-control" placeholder="Option 1" required>
                            </div>
                            <div class="d-flex gap-2 mb-2">
                                <input type="text" name="options[]" class="form-control" placeholder="Option 2" required>
                            </div>
                        </div>
                        <button type="button" id="addOptionBtn" class="btn btn-sm" style="background-color: var(--sage); color:#fff;">
                            <i class="bi bi-plus-circle"></i> Add Option
                        </button>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn" style="background-color:var(--sage-light); color:white; border-radius:8px; padding:6px 20px;">
                            <i class="bi bi-send me-1"></i> Create Poll
                        </button>
                    </div>
                </form>
            </div>

            <!-- ✅ Polls Feed -->
            <div class="main-feed">
                <?php if (empty($polls)): ?>
                    <div class="text-center p-5" style="color: var(--muted-text); background:#fff; border:1px solid var(--border-color); border-radius:12px;">
                        <i class="bi bi-clipboard-check fs-3 d-block mb-2" style="color:var(--sage-light);"></i>
                        <strong>No polls yet.</strong>
                        <p class="mt-2 mb-0">Be the first to create a poll!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($polls as $poll): ?>
                        <?php
                        $pollId = $poll['id'];
                        $pollUser = trim(($poll['first_name'] ?? '') . ' ' . ($poll['last_name'] ?? '')) ?: 'Anonymous';
                        $pollTime = date('M d, Y • h:i A', strtotime($poll['created_at']));

                        // Fetch options + votes
                        $optStmt = $pdo->prepare("
                SELECT o.id, o.option_text,
                    (SELECT COUNT(*) FROM poll_votes v WHERE v.option_id = o.id) AS votes
                FROM poll_options o
                WHERE o.poll_id = ?
            ");
                        $optStmt->execute([$pollId]);
                        $options = $optStmt->fetchAll(PDO::FETCH_ASSOC);

                        $totalVotes = array_sum(array_column($options, 'votes'));
                        ?>

                        <div class="post-card mb-3 p-4 shadow-sm">

                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <strong><?= sane($pollUser); ?></strong>
                                    <small class="d-block text-muted"><?= sane($pollTime); ?></small>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($poll['is_closed'] == 1): ?>
                                        <span class="badge bg-secondary">Closed</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Poll</span>
                                    <?php endif; ?>

                                    <?php if ($poll['created_by'] == $userId): ?>
                                        <div class="dropdown">
                                            <!-- type="button" prevents accidental form submit; aria-expanded for accessibility -->
                                            <button type="button"
                                                class="btn btn-sm btn-light border dropdown-toggle"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false"
                                                aria-label="Open poll options">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end" style="min-width:160px;">
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger" onclick="deletePoll(<?= $pollId ?>)">
                                                        <i class="bi bi-trash me-2"></i> Delete Poll
                                                    </button>
                                                </li>
                                                <?php if ($poll['is_closed'] == 0): ?>
                                                    <li>
                                                        <button type="button" class="dropdown-item" onclick="endPoll(<?= $pollId ?>)">
                                                            <i class="bi bi-flag me-2"></i> End Poll
                                                        </button>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Question -->
                            <h5 class="post-title mb-3"><?= sane($poll['question']); ?></h5>

                            <!-- Options -->
                            <form class="pollVoteForm" method="POST" action="poll_vote.php">
                                <input type="hidden" name="poll_id" value="<?= $pollId; ?>">

                                <?php foreach ($options as $opt): ?>
                                    <?php
                                    $percentage = $totalVotes > 0 ? round(($opt['votes'] / $totalVotes) * 100, 1) : 0;
                                    ?>
                                    <div class="mb-2">
                                        <label class="w-100">
                                            <input type="radio" name="option_id" value="<?= $opt['id']; ?>"
                                                <?= $poll['is_closed'] ? 'disabled' : '' ?>>
                                            <?= sane($opt['option_text']); ?>
                                        </label>

                                        <div class="progress mt-1" style="height:6px; border-radius:4px;">
                                            <div class="progress-bar"
                                                style="width: <?= $percentage; ?>%; background-color:var(--sage);">
                                            </div>
                                        </div>

                                        <small class="text-muted"><?= $opt['votes']; ?> votes (<?= $percentage; ?>%)</small>
                                    </div>
                                <?php endforeach; ?>

                                <?php if ($poll['is_closed'] == 0): ?>
                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-sm" style="background-color:var(--sage); color:white;">
                                            <i class="bi bi-check2-circle me-1"></i> Vote
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="text-end mt-3 text-muted fst-italic">
                                        Voting closed.
                                    </div>
                                <?php endif; ?>
                            </form>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure Bootstrap Dropdown is available
            try {
                const Dropdown = bootstrap.Dropdown; // will throw if bootstrap bundle isn't loaded
                document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
                    // If element already has an instance, skip
                    if (!Dropdown.getInstance(el)) {
                        new Dropdown(el);
                    }
                });
                console.log('Bootstrap dropdowns initialized.');
            } catch (err) {
                console.error('Bootstrap dropdown init failed — make sure bootstrap.bundle.min.js is loaded once and no other JS error prevents execution.', err);
            }

            // Optional helpful debug: show any JS errors in a small on-page badge (dev only)
            window.addEventListener('error', function(e) {
                console.warn('JS error detected:', e.message);
            });
        });

        // 🟢 Add poll options dynamically
        document.getElementById('addOptionBtn').addEventListener('click', function() {
            const container = document.getElementById('pollOptionsContainer');
            const div = document.createElement('div');
            div.classList.add('d-flex', 'gap-2', 'mb-2');
            div.innerHTML = `
            <input type="text" name="options[]" class="form-control" placeholder="New Option" required>
            <button type="button" class="btn btn-outline-danger btn-sm removeOptionBtn"><i class="bi bi-x"></i></button>
        `;
            container.appendChild(div);
            div.querySelector('.removeOptionBtn').addEventListener('click', () => div.remove());
        });

        function deletePoll(pollId) {
            if (!confirm("Delete this poll? This cannot be undone.")) return;

            fetch("poll_delete.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "poll_id=" + pollId
                })
                .then(res => res.text())
                .then(() => location.reload());
        }

        function endPoll(pollId) {
            if (!confirm("End this poll? Users will no longer be able to vote.")) return;

            fetch("poll_end.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "poll_id=" + pollId
                })
                .then(res => res.text())
                .then(() => location.reload());
        }
        // 🔐 Logout Confirmation
        window.confirmLogout = function() {
            if (confirm("Are you sure you want to log out?")) {
                document.getElementById('logoutForm').submit();
            }
        };

        // ⚠️ Placeholder Alert
        window.showCustomAlert = function(message) {
            alert(message);
        };
    </script>

</body>

</html>