
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    // Determine the user's login and administrator status.
    $isLoggedIn = !empty($_SESSION['user_id']);
    $isAdmin = !empty($_SESSION['is_admin']);

    // Get the book ID from the URL.
    $book_id = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT);

    $book = null;
    $error = '';

    if (!$book_id || $book_id < 1) {
        http_response_code(400);
        $error = 'Invalid book ID.';
    } else {
        try {
            $sql = "SELECT book_id, title, author, genre, publication_date, created_at
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
                $error = 'Sorry, that book could not be found.';
            }

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            http_response_code(500);
            $error = 'Unable to retrieve this book right now.';
        }
    }

    require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Back to Books
        </a>

    <?php else: ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <h1 class="card-title mb-4">
                    <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>
                </h1>

                <p>
                    <strong>Author:</strong>
                    <?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <p>
                    <strong>Genre:</strong>
                    <?= htmlspecialchars($book['genre'], ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <p>
                    <strong>Publication Date:</strong>
                    <?= htmlspecialchars($book['publication_date'] ?? 'Not specified', ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <p>
                    <strong>Added to Catalog:</strong>
                    <?= htmlspecialchars($book['created_at'] ?? 'Not available', ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <hr>

                <div class="d-flex flex-wrap gap-2">

                    <a href="index.php" class="btn btn-secondary">
                        Back to Books
                    </a>

                    <?php if ($isLoggedIn): ?>

                        <a
                            href="../reviews/create.php?book_id=<?= (int) $book['book_id']; ?>"
                            class="btn btn-primary"
                        >
                            Write a Review
                        </a>

                    <?php else: ?>

                        <a href="../users/create.php" class="btn btn-primary">
                            Sign up to add your own review!
                        </a>

                    <?php endif; ?>

                    <?php if ($isAdmin): ?>

                        <a
                            href="edit.php?book_id=<?= (int) $book['book_id']; ?>"
                            class="btn btn-warning"
                        >
                            Edit Book
                        </a>

                        <a
                            href="delete.php?book_id=<?= (int) $book['book_id']; ?>"
                            class="btn btn-danger"
                        >
                            Delete Book
                        </a>

                    <?php endif; ?>

                </div>

            </div>
        </div>

    <?php endif; ?>

</div>

<?php
    require_once __DIR__ . '/../includes/footer.php';
?>
