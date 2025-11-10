<?php
// This file assumes all variables like $postId, $postUser, $postTime, etc.,
// are defined in the file that includes it (dashboard.php).
?>
<div id="post-<?= $postId; ?>" class="post-card mb-3" data-post-tag="<?= $postTag; ?>" data-post-likes="<?= (int)$postLikes; ?>" data-post-comments=" <?= $postComments; ?>" data-post-time="<?= $postTime; ?>">
    <div class="d-flex gap-3">
        <div class="avatar"><?= strtoupper(substr($postUser, 0, 1)); ?></div>
        <div class="w-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><?= sane($postUser); ?></strong>
                    <?php if ($postRole === 'Admin'): ?>
                        <small class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">
                            <i class="bi bi-patch-check-fill"></i> <?= sane($postRole); ?>
                        </small>
                    <?php endif; ?>
                    <small class="text-muted d-block"><?= sane($postTime); ?></small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge <?= $tagClass; ?>"><?= htmlspecialchars($postTag, ENT_NOQUOTES); ?></span>
                    
                    <button class="btn-bookmark btn-as-link" data-post-id="<?= $postId; ?>" title="Bookmark">
                        <i class="bi <?= $bookmarked ? 'bi-bookmark-fill text-warning' : 'bi-bookmark'; ?>"></i>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0 btn-as-link" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($isCurrentUserPost): ?>
                                <li>
                                    <a href="#" class="dropdown-item edit-post-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editPostModal" 
                                        data-index="<?= $postId; ?>" 
                                        data-title="<?= htmlspecialchars($postTitle, ENT_QUOTES); ?>" 
                                        data-content="<?= htmlspecialchars($postContent, ENT_QUOTES); ?>">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                        <input type="hidden" name="delete_post_id" value="<?= $postId; ?>">
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="alert('Report functionality coming soon.'); return false;">
                                    <i class="bi bi-flag me-2"></i>Report
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php if (!empty($postTitle)): ?>
                <h5 class="post-title mt-2"><?= htmlspecialchars($postTitle); ?></h5>
            <?php endif; ?>
            
            <p class="mt-2 mb-2 post-content post-content-text"><?= nl2br(sane($postContent)); ?></p>

            <div class="d-flex justify-content-between align-items-center mt-3 interaction-stats">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn-like btn-as-link" data-post-id="<?= $postId; ?>">
                        <i class="bi <?= $liked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                        <span class="like-count"><?= $postLikes; ?></span>
                    </button>

                    <a class="view-comments-toggle btn-as-link" data-bs-toggle="collapse" href="#comments-<?= $postId; ?>">
                        <i class="bi bi-chat-left"></i>
                        <span id="comment-count-<?= $postId; ?>"><?= $postComments; ?></span>
                    </a>
                </div>
            </div>

            <div class="collapse mt-3" id="comments-<?= $postId; ?>">
                <div class="comment-list mb-3">
                    <?php if (empty($commentsForPost)): ?>
                         <p class="text-muted small text-center no-comments-msg">No comments yet. Be the first to reply!</p>
                    <?php endif; ?>
                    <?php foreach ($commentsForPost as $comment): ?>
                        <?php
                        $commentUser = trim($comment['first_name'] . ' ' . $comment['last_name']) ?: 'Anonymous';
                        $commentTime = date('M d, Y H:i', strtotime($comment['created_at']));
                        $commentContent = $comment['content'];
                        $isCurrentUserComment = $comment['user_id'] == $userId;
                        ?>
                        <div class="d-flex gap-2 mb-3 comment-card" data-comment-id="<?= $comment['id']; ?>">
                            <div class="avatar comment-avatar bg-secondary"><?= strtoupper(substr($commentUser, 0, 1)); ?></div>
                            <div class="w-100">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= sane($commentUser); ?></strong>
                                        <small class="text-muted"> • <?= sane($commentTime); ?></small>
                                    </div>
                                    <?php if ($isCurrentUserComment): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item btn-edit-comment" 
                                                        data-comment-id="<?= $comment['id']; ?>" 
                                                        data-comment-text="<?= htmlspecialchars($commentContent, ENT_QUOTES); ?>"
                                                        data-post-index="<?= $postId; ?>">
                                                        <i class="bi bi-pencil me-2"></i>Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger btn-delete-comment" 
                                                        data-comment-id="<?= $comment['id']; ?>">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-0 comment-text"><?= sane($commentContent); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="comment-form d-flex gap-2 pt-2 border-top" method="POST" action="#">
                    <input type="hidden" name="comment_post_id" value="<?= $postId; ?>">
                    <div class="avatar comment-avatar"><?= strtoupper(substr($currentUser['first_name'] ?? '?', 0, 1)); ?></div>
                    <input type="text" name="comment_content" class="form-control form-control-sm comment-input" placeholder="Write a comment..." required>
                    <button type="submit" class="btn btn-primary btn-sm">Post</button>
                </form>
            </div>
            
        </div>
    </div>
</div>