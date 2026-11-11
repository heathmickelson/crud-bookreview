
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    require_login();

    // Get the authenticated user's ID from the session.
    $user_id = $_SESSION['user_id'];

    $errors = [];

    // Keep submitted values if validation fails.
    $book_id = $_GET['book_id'] ?? '';
    $rating = '';
    $review_text = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Verify CSRF token.
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Invalid form submission.');
        }

        // Retrieve submitted information.
        $book_id = $_POST['book_id'] ?? '';
        $rating = $_POST['rating'] ?? '';
        $review_text = trim($_POST['review_text'] ?? '');

        // Validate book ID.
        $book_id = filter_var($book_id, FILTER_VALIDATE_INT);

        if ($book_id === false || $book_id < 1) {
            $errors[] = "Please select a valid book.";
        }

        // Validate rating.
        $rating = filter_var($rating, FILTER_VALIDATE_INT);

        if ($rating === false || $rating < 1 || $rating > 5) {
            $errors[] = "Rating must be a whole number between 1 and 5.";
        }

        // Validate review text.
        if (empty($review_text)) {
            $errors[] = "Please enter your review.";
        }

        // Confirm the selected book exists.
        if (empty($errors)) {
            $checkBook = $conn->prepare(
                "SELECT book_id FROM books WHERE book_id = ?"
            );

            $checkBook->bind_param("i", $book_id);
            $checkBook->execute();
            $checkBook->store_result();

            if ($checkBook->num_rows === 0) {
                $errors[] = "The selected book does not exist.";
            }

            $checkBook->close();
        }

        // Save the review if validation succeeds.
        if (empty($errors)) {

            try {
                $sql = "INSERT INTO reviews
                        (book_id, user_id, rating, review_text)
                        VALUES (?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "iiis",
                    $book_id,
                    $user_id,
                    $rating,
                    $review_text
                );

                $stmt->execute();
                $stmt->close();

                header("Location: index.php?created=1");
                exit;

            } catch (mysqli_sql_exception $e) {

                // Handle the unique user/book constraint.
                if ($e->getCode() === 1062) {
                    $errors[] = "You have already submitted a review for this book.";
                } else {
                    error_log("Review creation failed: " . $e->getMessage());
                    $errors[] = "Something went wrong while saving your review. Please try again.";
                }
            }
        }
    }

    // Retrieve available books.
    $booksResult = $conn->query(
        "SELECT book_id, title, author FROM books ORDER BY title"
    );
?>



<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container mx-auto">

        <h1 class="mb-4">Write a Review</h1>

        <!-- Display validation or database errors. -->
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

        <!-- 7. Display HTML form. -->
        <form method="POST" class="needs-validation review-form" novalidate>

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
            >
            <!-- Select a book. -->
            <label for="book_id" class="form-label">
                Select a Book
            </label>

            <select
                class="form-select mb-3"
                id="book_id"
                name="book_id"
                required
            >
                <option value="" disabled <?= empty($book_id) ? 'selected' : '' ?>>
                    Choose a book
                </option>

                <?php while ($book = $booksResult->fetch_assoc()): ?>
                    <option
                        value="<?= (int) $book['book_id'] ?>"
                        <?= (string) $book_id === (string) $book['book_id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>
                        -
                        <?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Select a rating. -->
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

            <!-- Write a review. -->
            <label for="review_text" class="form-label">
                Review
            </label>

            <textarea
                class="form-control"
                name="review_text"
                id="review_text"
                rows="6"
                placeholder="What did you think about this book?"
                required
            ><?= htmlspecialchars($review_text, ENT_QUOTES, 'UTF-8') ?></textarea>

            <!-- Submit button. -->
            <div class="d-flex justify-content-center mt-3 mb-3">
                <button class="btn btn-primary" type="submit">
                    Submit Review
                </button>
            </div>

        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>