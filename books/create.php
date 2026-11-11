
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    // Only administrators can add books.
    require_login();

    if (empty($_SESSION['is_admin'])) {
        http_response_code(403);
        exit('You do not have permission to add books.');
    }

    $errors = [];

    $title = '';
    $author = '';
    $genre = '';
    $publication_date = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

        if ($publication_date !== '') {
            $date = DateTime::createFromFormat('!Y-m-d', $publication_date);

            if (!$date || $date->format('Y-m-d') !== $publication_date) {
                $errors[] = 'Please enter a valid publication date.';
            }
        }

        if (empty($errors)) {
            try {
                $sql = "INSERT INTO books
                        (title, author, genre, publication_date)
                        VALUES (?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                $dateValue = $publication_date !== ''
                    ? $publication_date
                    : null;

                $stmt->bind_param(
                    "ssss",
                    $title,
                    $author,
                    $genre,
                    $dateValue
                );

                $stmt->execute();
                $stmt->close();

                header('Location: index.php?added=1');
                exit;

            } catch (mysqli_sql_exception $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to add the book right now. Please try again.';
            }
        }
    }

    require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">

    <h1>Add a Book</h1>
    <p class="text-muted">Add a new book to the catalog.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="create.php">

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
            Add Book
        </button>

        <a href="index.php" class="btn btn-secondary">
            Cancel
        </a>

    </form>

</div>

<?php
    require_once __DIR__ . '/../includes/footer.php';
?>
