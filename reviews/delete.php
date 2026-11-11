
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    require_login();

    $currentUserId = (int) $_SESSION['user_id'];
    $isAdmin = !empty($_SESSION['is_admin']);

    // Retrieve and validate review ID.
    $review_id = filter_input(INPUT_GET, 'review_id', FILTER_VALIDATE_INT);

    if ($review_id === false || $review_id === null || $review_id < 1) {
        http_response_code(400);
        exit('Invalid review ID.');
    }

    try {
        // Retrieve the review and associated book information.
        $sql = "SELECT
                    reviews.review_id,
                    reviews.book_id,
                    reviews.user_id,
                    reviews.rating,
                    reviews.review_text,
                    books.title
                FROM reviews
                INNER JOIN books
                    ON reviews.book_id = books.book_id
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

        // Check review ownership.
        $ownsReview = $currentUserId === (int) $review['user_id'];

        if (!$ownsReview && !$isAdmin) {
            http_response_code(403);
            exit('You do not have permission to delete this review.');
        }

        // Process deletion after confirmation.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Verify CSRF token.
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                exit('Invalid form submission.');
            }

            // Delete only the review the user owns, unless admin.
            $deleteSql = "DELETE FROM reviews WHERE review_id = ?";

            if (!$isAdmin) {
                $deleteSql .= " AND user_id = ?";
            }

            $deleteStmt = $conn->prepare($deleteSql);

            if ($isAdmin) {
                $deleteStmt->bind_param("i", $review_id);
            } else {
                $deleteStmt->bind_param(
                    "ii",
                    $review_id,
                    $currentUserId
                );
            }

            $deleteStmt->execute();

            if ($deleteStmt->affected_rows !== 1) {
                $deleteStmt->close();
                http_response_code(404);
                exit('Review could not be deleted.');
            }

            $deleteStmt->close();

            header("Location: index.php?deleted=1");
            exit;
        }

    } catch (mysqli_sql_exception $e) {
        error_log("Review deletion failed: " . $e->getMessage());
        http_response_code(500);
        exit('Something went wrong while deleting this review.');
    }
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container my-4">

        <h1 class="mb-4">Delete Review</h1>

        <div class="card border-danger shadow-sm">

            <div class="card-header bg-danger text-white">
                Confirm Review Deletion
            </div>

            <div class="card-body">

                <h5 class="card-title">
                    <?= htmlspecialchars($review['title'], ENT_QUOTES, 'UTF-8') ?>
                </h5>

                <p>
                    <strong>Rating:</strong>
                    <?= (int) $review['rating'] ?>/5
                </p>

                <p>
                    <strong>Review:</strong>
                </p>

                <p>
                    <?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?>
                </p>

                <div class="alert alert-warning">
                    Are you sure you want to delete this review? This action cannot be undone.
                </div>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <div class="d-flex gap-2">

                        <button class="btn btn-danger" type="submit">
                            Confirm Delete
                        </button>

                        <a
                            href="view.php?review_id=<?= (int) $review_id ?>"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
