
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    // Only administrators can delete books.
    require_login();

    if (empty($_SESSION['is_admin'])) {
        http_response_code(403);
        exit('You do not have permission to delete books.');
    }

    $errors = [];
    $message = '';

    // Retrieve and validate book ID.
    $book_id = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT);

    if (!$book_id || $book_id < 1) {
        http_response_code(400);
        exit('Invalid book ID.');
    }

    // Retrieve the book.
    try {
        $sql = "SELECT book_id, title, author
                FROM books
                WHERE book_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $book = $result->fetch_assoc();

        $stmt->close();

        if (!$book) {
            http_response_code(404);
            exit('Book not found.');
        }

        // Check whether reviews are associated with this book.
        $sql = "SELECT COUNT(*) AS review_count
                FROM reviews
                WHERE book_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $reviewData = $result->fetch_assoc();

        $review_count = (int) $reviewData['review_count'];

        $stmt->close();

    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit('Unable to retrieve book information.');
    }

    // Process deletion confirmation.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Verify CSRF token.
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid form submission. Please try again.';

        } elseif ($review_count > 0) {

            $errors[] = 'This book cannot be deleted because it has associated reviews.';

        } else {

            try {
                $sql = "DELETE FROM books WHERE book_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();

                $stmt->close();

                header('Location: index.php?deleted=1');
                exit;

            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());

                // Protect against a review being added between
                // the review count check and the deletion.
                if ((int) $e->getCode() === 1451) {
                    $errors[] = 'This book cannot be deleted because it has associated reviews.';
                } else {
                    $errors[] = 'Unable to delete the book right now. Please try again.';
                }
            }
        }
    }

    require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">

    <h1>Delete Book</h1>

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="card-title">
                <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>
            </h4>

            <p>
                <strong>Author:</strong>
                <?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <?php if ($review_count > 0): ?>

                <div class="alert alert-warning">
                    <strong>Deletion unavailable.</strong>

                    This book currently has
                    <?= $review_count; ?>
                    associated review(s).

                    You must preserve those reviews, so this book cannot
                    be deleted.
                </div>

                <a
                    href="view.php?book_id=<?= (int) $book_id; ?>"
                    class="btn btn-secondary"
                >
                    Back to Book
                </a>

            <?php else: ?>

                <div class="alert alert-warning">
                    Are you sure you want to delete this book?

                    This action cannot be undone.
                </div>

                <form
                    method="POST"
                    action="delete.php?book_id=<?= (int) $book_id; ?>"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"
                    >

                    <button type="submit" class="btn btn-danger">
                        Confirm Delete
                    </button>

                    <a
                        href="view.php?book_id=<?= (int) $book_id; ?>"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </form>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php
    require_once __DIR__ . '/../includes/footer.php';
?>
