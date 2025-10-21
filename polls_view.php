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
</head>

<body>
<div class="dashboard-layout d-flex">

    <div class="sidebar">
        <div>
            <h4 class="mb-4"><i class="bi bi-people-fill me-2"></i> AgoraBoard</h4>
            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link active"><i class="bi bi-house-door"></i> Dashboard</a>
                <a href="public-safety.php" class="nav-link"><i class="bi bi-shield-exclamation"></i> Public Safety</a>
                <a href="lost-and-found.php" class="nav-link"><i class="bi bi-search"></i> Lost and Found</a>
                <a href="event.php" class="nav-link"><i class="bi bi-calendar-event"></i> Event</a>
                <a href="jobs.php" class="nav-link"><i class="bi bi-briefcase"></i> Jobs</a>
                <a href="polls_view.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Polls</a>
                <a href="volunteering.php" class="nav-link"><i class="bi bi-heart"></i> Volunteering</a>
                <hr class="my-3 border-white opacity-25">

                <a href="bookmarks_view.php" class="nav-link"><i class="bi bi-bookmark"></i> Bookmarks</a>
                <a href="#" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <form action="logout.php" method="POST" class="m-0" id="logoutForm">
                <input type="hidden" name="logout" value="1">
                <button type="button" class="nav-link logout-btn w-100" onclick="confirmLogout()">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

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
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong><?= sane($pollUser); ?></strong>
                                <small class="d-block text-muted"><?= sane($pollTime); ?></small>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Poll</span>
                        </div>

                        <h5 class="post-title mb-3"><?= sane($poll['question']); ?></h5>

                        <form class="pollVoteForm" method="POST" action="poll_vote.php">
                            <input type="hidden" name="poll_id" value="<?= $pollId; ?>">
                            <?php foreach ($options as $opt): ?>
                                <?php
                                $percentage = $totalVotes > 0 ? round(($opt['votes'] / $totalVotes) * 100, 1) : 0;
                                ?>
                                <div class="mb-2">
                                    <label class="w-100">
                                        <input type="radio" name="option_id" value="<?= $opt['id']; ?>" required>
                                        <?= sane($opt['option_text']); ?>
                                    </label>
                                    <div class="progress mt-1" style="height:6px; border-radius:4px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: <?= $percentage; ?>%; background-color:var(--sage);"
                                             aria-valuenow="<?= $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= $opt['votes']; ?> votes (<?= $percentage; ?>%)</small>
                                </div>
                            <?php endforeach; ?>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-sm" style="background-color:var(--sage); color:white; border-radius:8px;">
                                    <i class="bi bi-check2-circle me-1"></i> Vote
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
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
</script>

</body>
</html>
