
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    // Check the user's login status and admin privileges.
    $isLoggedIn = !empty($_SESSION['user_id']);
    $isAdmin = !empty($_SESSION['is_admin']);
    $currentUserId = $_SESSION['user_id'] ?? null;

    // Retrieve all reviews with their associated book and reviewer.
    $sql = "SELECT
                reviews.review_id,
                reviews.book_id,
                reviews.user_id,
                reviews.rating,
                reviews.review_text,
                reviews.created_at,
                books.title,
                books.author,
                users.full_name
            FROM reviews
            INNER JOIN books
                ON reviews.book_id = books.book_id
            INNER JOIN users
                ON reviews.user_id = users.user_id
            ORDER BY reviews.created_at DESC";

    try {
        $reviewsResult = $conn->query($sql);
    } catch (mysqli_sql_exception $e) {
        error_log("Review retrieval failed: " . $e->getMessage());
        $reviewsResult = false;
    }
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container my-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-4">Book Reviews</h1>
            <?php if ($isLoggedIn): ?>
                <a href="create.php" class="btn btn-primary">
                    Add a Review
                </a>
            <?php else: ?>
                <a href="/users/index.php" class="btn btn-outline-primary">
                    Log in to Write a Review
                </a>
            <?php endif; ?>
        </div>
        <!-- Display success message after creating a review. -->
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success">
                Your review was successfully submitted!
            </div>
        <?php endif; ?>

        <?php if ($reviewsResult === false): ?>

            <div class="alert alert-danger">
                We couldn't retrieve the reviews. Please try again later.
            </div>

        <?php elseif ($reviewsResult->num_rows > 0): ?>

            <div class="row g-4">

                <?php while ($review = $reviewsResult->fetch_assoc()): ?>

                    <?php
                        // Determine whether the current user can manage this review.
                        $ownsReview = $currentUserId !== null
                            && (int) $currentUserId === (int) $review['user_id'];

                        $canManage = $isAdmin || $ownsReview;
                    ?>

                    <div class="col-12 col-md-6 col-lg-4">

                        <div class="card h-100 shadow-sm">

                            <div class="card-body">

                                <h5 class="card-title">
                                    <?= htmlspecialchars($review['title'], ENT_QUOTES, 'UTF-8') ?>
                                </h5>

                                <h6 class="card-subtitle mb-3 text-body-secondary">
                                    By <?= htmlspecialchars($review['author'], ENT_QUOTES, 'UTF-8') ?>
                                </h6>

                                <p class="mb-2">
                                    <strong>Reviewed by:</strong>
                                    <?= htmlspecialchars($review['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <p class="mb-2">
                                    <strong>Rating:</strong>
                                    <?= (int) $review['rating'] ?>/5
                                </p>

                                <p class="card-text">
                                    <?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?>
                                </p>

                                <p class="text-body-secondary small">
                                    Submitted:
                                    <?= htmlspecialchars($review['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                </p>

                            </div>

                            <div class="card-footer bg-transparent">

                                <a
                                    href="view.php?review_id=<?= (int) $review['review_id'] ?>"
                                    class="btn btn-sm btn-primary"
                                >
                                    View Review
                                </a>

                                <?php if ($canManage): ?>

                                    <a
                                        href="edit.php?review_id=<?= (int) $review['review_id'] ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete.php?review_id=<?= (int) $review['review_id'] ?>"
                                        class="btn btn-sm btn-danger"
                                    >
                                        Delete
                                    </a>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="alert alert-info">
                There are no reviews yet. Be the first to share your thoughts on a book!
            </div>

        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
