
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    require_login();

    $currentUserId = (int) $_SESSION['user_id'];
    $isAdmin = !empty($_SESSION['is_admin']);

    $errors = [];

    // Retrieve and validate review ID.
    $review_id = filter_input(INPUT_GET, 'review_id', FILTER_VALIDATE_INT);

    if ($review_id === false || $review_id === null || $review_id < 1) {
        http_response_code(400);
        exit('Invalid review ID.');
    }

    try {
        // Retrieve the review and its associated book.
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
            exit('You do not have permission to edit this review.');
        }

        // Populate form values.
        $rating = $review['rating'];
        $review_text = $review['review_text'];

        // Process submitted form.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Verify CSRF token.
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                exit('Invalid form submission.');
            }

            $rating = $_POST['rating'] ?? '';
            $review_text = trim($_POST['review_text'] ?? '');

            // Validate rating.
            $rating = filter_var($rating, FILTER_VALIDATE_INT);

            if ($rating === false || $rating < 1 || $rating > 5) {
                $errors[] = "Rating must be a whole number between 1 and 5.";
            }

            // Validate review text.
            if (empty($review_text)) {
                $errors[] = "Please enter your review.";
            }

            // Update review if validation succeeds.
            if (empty($errors)) {

                $updateSql = "UPDATE reviews
                              SET rating = ?, review_text = ?
                              WHERE review_id = ?";

                // Regular users can only update their own reviews.
                if (!$isAdmin) {
                    $updateSql .= " AND user_id = ?";
                }

                $updateStmt = $conn->prepare($updateSql);

                if ($isAdmin) {
                    $updateStmt->bind_param(
                        "isi",
                        $rating,
                        $review_text,
                        $review_id
                    );
                } else {
                    $updateStmt->bind_param(
                        "isii",
                        $rating,
                        $review_text,
                        $review_id,
                        $currentUserId
                    );
                }

                $updateStmt->execute();
                $updateStmt->close();

                header("Location: view.php?review_id=" . $review_id . "&updated=1");
                exit;
            }
        }

    } catch (mysqli_sql_exception $e) {
        error_log("Review update failed: " . $e->getMessage());
        http_response_code(500);
        exit('Something went wrong while updating this review.');
    }
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container my-4">

        <h1 class="mb-4">Edit Review</h1>

        <div class="card shadow-sm">

            <div class="card-header">
                <h5 class="mb-0">
                    <?= htmlspecialchars($review['title'], ENT_QUOTES, 'UTF-8') ?>
                </h5>
            </div>

            <div class="card-body">

                <!-- Display validation errors. -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li>
                                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <label for="rating" class="form-label">
                        Rating (1-5)
                    </label>

                    <input
                        class="form-control mb-3"
                        type="number"
                        id="rating"
                        name="rating"
                        min="1"
                        max="5"
                        step="1"
                        value="<?= htmlspecialchars((string) $rating, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                    <!-- Review text -->
                    <label for="review_text" class="form-label">
                        Review
                    </label>

                    <textarea
                        class="form-control"
                        name="review_text"
                        id="review_text"
                        rows="6"
                        required
                    ><?= htmlspecialchars($review_text, ENT_QUOTES, 'UTF-8') ?></textarea>

                    <div class="d-flex justify-content-center gap-2 mt-3">

                        <button class="btn btn-primary" type="submit">
                            Save Changes
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
