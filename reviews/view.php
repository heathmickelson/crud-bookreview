
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    // Check login status and admin privileges.
    $isLoggedIn = !empty($_SESSION['user_id']);
    $isAdmin = !empty($_SESSION['is_admin']);
    $currentUserId = $_SESSION['user_id'] ?? null;

    // Retrieve and validate the review ID.
    $review_id = filter_input(INPUT_GET, 'review_id', FILTER_VALIDATE_INT);

    if ($review_id === false || $review_id === null || $review_id < 1) {
        http_response_code(400);
        exit('Invalid review ID.');
    }

    try {
        // Retrieve the review, book, and reviewer information.
        $sql = "SELECT
                    reviews.review_id,
                    reviews.book_id,
                    reviews.user_id,
                    reviews.rating,
                    reviews.review_text,
                    reviews.created_at,
                    reviews.updated_at,
                    books.title,
                    books.author,
                    books.genre,
                    users.full_name
                FROM reviews
                INNER JOIN books
                    ON reviews.book_id = books.book_id
                INNER JOIN users
                    ON reviews.user_id = users.user_id
                WHERE reviews.review_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $review_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $review = $result->fetch_assoc();

        $stmt->close();

        if (!$review) {
            http_response_code(404);
            exit('Review not found.');
        }

        // Determine whether the current user can manage this review.
        $ownsReview = $currentUserId !== null
            && (int) $currentUserId === (int) $review['user_id'];

        $canManage = $isAdmin || $ownsReview;

    } catch (mysqli_sql_exception $e) {
        error_log("Review retrieval failed: " . $e->getMessage());
        http_response_code(500);
        exit('Something went wrong while retrieving this review.');
    }
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container my-4">

        <h1 class="mb-4">Review Details</h1>

        <div class="card shadow-sm">

            <div class="card-header">
                <h4 class="mb-0">
                    <?= htmlspecialchars($review['title'], ENT_QUOTES, 'UTF-8') ?>
                </h4>
            </div>

            <div class="card-body">

                <h6 class="text-body-secondary mb-3">
                    By <?= htmlspecialchars($review['author'], ENT_QUOTES, 'UTF-8') ?>
                </h6>

                <p>
                    <strong>Genre:</strong>
                    <?= htmlspecialchars($review['genre'] ?? 'Not specified', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Reviewed by:</strong>
                    <?= htmlspecialchars($review['full_name'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Rating:</strong>
                    <?= (int) $review['rating'] ?>/5
                </p>

                <hr>

                <h5>Review</h5>

                <p>
                    <?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?>
                </p>

                <hr>

                <p class="text-body-secondary small mb-1">
                    <strong>Submitted:</strong>
                    <?= htmlspecialchars($review['created_at'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <?php if (!empty($review['updated_at'])): ?>
                    <p class="text-body-secondary small">
                        <strong>Last updated:</strong>
                        <?= htmlspecialchars($review['updated_at'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

            </div>

            <div class="card-footer">

                <a href="index.php" class="btn btn-secondary">
                    Back to Reviews
                </a>

                <?php if ($canManage): ?>

                    <a
                        href="edit.php?review_id=<?= (int) $review['review_id'] ?>"
                        class="btn btn-warning"
                    >
                        Edit Review
                    </a>

                    <a
                        href="delete.php?review_id=<?= (int) $review['review_id'] ?>"
                        class="btn btn-danger"
                    >
                        Delete Review
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
