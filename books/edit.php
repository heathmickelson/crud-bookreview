
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    // Only administrators can edit books.
    require_login();

    if (empty($_SESSION['is_admin'])) {
        http_response_code(403);
        exit('You do not have permission to edit books.');
    }

    $errors = [];

    // Retrieve and validate book ID.
    $book_id = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT);

    if (!$book_id || $book_id < 1) {
        http_response_code(400);
        exit('Invalid book ID.');
    }

    // Retrieve the existing book.
    try {
        $sql = "SELECT title, author, genre, publication_date
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

    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit('Unable to retrieve this book.');
    }

    // Populate the form with existing book information.
    $title = $book['title'];
    $author = $book['author'];
    $genre = $book['genre'];
    $publication_date = $book['publication_date'] ?? '';

    // Process submitted changes.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Keep submitted values if validation fails.
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $publication_date = trim($_POST['publication_date'] ?? '');

        // Verify CSRF token.
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid form submission. Please try again.';
        }

        // Validate required fields.
        if ($title === '') {
            $errors[] = 'Book title is required.';
        }

        if ($author === '') {
            $errors[] = 'Author is required.';
        }

        if ($genre === '') {
            $errors[] = 'Genre is required.';
        }

        // Validate publication date if provided.
        if ($publication_date !== '') {
            $date = DateTime::createFromFormat('!Y-m-d', $publication_date);

            if (!$date || $date->format('Y-m-d') !== $publication_date) {
                $errors[] = 'Please enter a valid publication date.';
            }
        }

        // Update the book if validation succeeds.
        if (empty($errors)) {
            try {
                $sql = "UPDATE books
                        SET title = ?,
                            author = ?,
                            genre = ?,
                            publication_date = ?
                        WHERE book_id = ?";

                $stmt = $conn->prepare($sql);

                $dateValue = $publication_date !== ''
                    ? $publication_date
                    : null;

                $stmt->bind_param(
                    "ssssi",
                    $title,
                    $author,
                    $genre,
                    $dateValue,
                    $book_id
                );

                $stmt->execute();
                $stmt->close();

                header("Location: view.php?book_id=" . $book_id . "&updated=1");
                exit;

            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to update the book right now. Please try again.';
            }
        }
    }

    require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">

    <h1>Edit Book</h1>

    <p class="text-muted">
        Update the information for this book.
    </p>

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

    <form method="POST" action="edit.php?book_id=<?= (int) $book_id; ?>" class="needs-validation" novalidate>

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"
        >

        <div class="mb-3">
            <label for="title" class="form-label">Book Title</label>

            <input
                type="text"
                class="form-control"
                id="title"
                name="title"
                value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label for="author" class="form-label">Author</label>

            <input
                type="text"
                class="form-control"
                id="author"
                name="author"
                value="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label for="genre" class="form-label">Genre</label>

            <input
                type="text"
                class="form-control"
                id="genre"
                name="genre"
                value="<?= htmlspecialchars($genre, ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label for="publication_date" class="form-label">
                Publication Date
            </label>

            <input
                type="date"
                class="form-control"
                id="publication_date"
                name="publication_date"
                value="<?= htmlspecialchars($publication_date, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>

        <a
            href="view.php?book_id=<?= (int) $book_id; ?>"
            class="btn btn-secondary"
        >
            Cancel
        </a>

    </form>

</div>

<?php
    require_once __DIR__ . '/../includes/footer.php';
?>
