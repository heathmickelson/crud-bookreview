<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';
    
    // Check whether someone is logged in.
    $isLoggedIn = !empty($_SESSION['user_id']);

    // Check whether the logged-in user is an administrator.
    $isAdmin = !empty($_SESSION['is_admin']);

    // Search value from the search form.
    $search = trim($_GET['search'] ?? '');

    $books = [];
    $error = '';

    try {
        if ($search !== '') {
            // Search by book title or author.
            $sql = "SELECT book_id, title, author, genre, publication_date
                    FROM books
                    WHERE title LIKE ? OR author LIKE ?
                    ORDER BY title ASC";

            $stmt = $conn->prepare($sql);

            $searchTerm = '%' . $search . '%';
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();

            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }

            $stmt->close();
        } else {
            // Retrieve all books.
            $sql = "SELECT book_id, title, author, genre, publication_date
                    FROM books
                    ORDER BY title ASC";

            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        $error = "Sorry, we couldn't load the book catalog right now.";
    }

    require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Book Catalog</h1>
            <p class="text-muted">
                Browse our collection and discover your next great read.
            </p>
        </div>

        <?php if ($isAdmin): ?>
            <a href="create.php" class="btn btn-primary">
                Add a Book
            </a>
        <?php endif; ?>
    </div>

    <!-- Search form -->
    <form method="GET" action="index.php" class="mb-4">
        <div class="input-group">
            <input
                type="search"
                name="search"
                class="form-control"
                placeholder="Search by title or author..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
            >

            <button type="submit" class="btn btn-primary">
                Search
            </button>

            <?php if ($search !== ''): ?>
                <a href="index.php" class="btn btn-outline-secondary">
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>

    <?php elseif (empty($books)): ?>

        <div class="alert alert-info">
            <?php if ($search !== ''): ?>
                No books found matching your search.
            <?php else: ?>
                There are currently no books in the catalog.
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($books as $book): ?>

                <div class="col-md-6 col-lg-4">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">

                            <h5 class="card-title">
                                <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>

                            <p class="card-text">
                                <strong>Author:</strong>
                                <?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <p class="card-text">
                                <strong>Genre:</strong>
                                <?= htmlspecialchars($book['genre'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <p class="card-text">
                                <strong>Published:</strong>
                                <?= htmlspecialchars($book['publication_date'] ?? 'Not specified', ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <a
                                href="view.php?book_id=<?= (int) $book['book_id']; ?>"
                                class="btn btn-outline-primary"
                            >
                                View Book
                            </a>

                            <?php if ($isLoggedIn): ?>

                                <a
                                    href="../reviews/create.php?book_id=<?= (int) $book['book_id']; ?>"
                                    class="btn btn-primary"
                                >
                                    Write a Review
                                </a>

                            <?php else: ?>

                                <p class="mt-3 mb-0">
                                    <a href="../users/create.php">
                                        Sign up to add your own review!
                                    </a>
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php
    require_once __DIR__ . '/../includes/footer.php';
?>