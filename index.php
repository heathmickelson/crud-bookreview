<?php
    include_once "includes/db.php";
    include_once "includes/header.php";

    
    // Select one random book and its most recent review, if available.
    $sql = "
        SELECT
            b.book_id,
            b.title,
            b.author,
            b.genre,
            r.rating,
            r.review_text,
            r.created_at AS review_date,
            u.full_name
        FROM books b
        LEFT JOIN reviews r
            ON r.review_id = (
                SELECT r2.review_id
                FROM reviews r2
                WHERE r2.book_id = b.book_id
                ORDER BY r2.created_at DESC, r2.review_id DESC
                LIMIT 1
            )
        LEFT JOIN users u
            ON r.user_id = u.user_id
        ORDER BY RAND()
        LIMIT 1
    ";

    $result = $conn->query($sql);
    $featuredBook = $result->fetch_assoc();

?>

    <!-- Main Content -->
    <main class="container text-center py-5">

        <h1>Welcome to Booked & Reviewed!</h1>

        <p class="lead">
            Your next great read starts with a review.
        </p>

        <p>
            Discover books, explore reader opinions,
            and share your thoughts on your favorite reads.
        </p>

        <h1> Check out some Books </h1>
        <div id="highlightedBook" class="d-flex justify-content-center mt-3 mb-3">

            <?php if ($featuredBook): ?>

                <div class="card shadow-sm" style="max-width: 500px; width: 100%;">
                    <div class="card-body">

                        <h3 class="card-title">
                            <?php echo htmlspecialchars($featuredBook['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>

                        <h6 class="card-subtitle mb-3 text-muted">
                            By <?php echo htmlspecialchars($featuredBook['author'], ENT_QUOTES, 'UTF-8'); ?>
                        </h6>

                        <p class="card-text">
                            **Genre:**
                            <?php echo htmlspecialchars($featuredBook['genre'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <?php if ($featuredBook['rating'] !== null): ?>

                            <hr>

                            <p>
                                **Latest Rating:**
                                <?php echo (int) $featuredBook['rating']; ?>/5
                            </p>

                            <p class="card-text">
                                <?php
                                    echo htmlspecialchars(
                                        mb_strimwidth($featuredBook['review_text'], 0, 180, '...'),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>
                            </p>

                            <small class="text-muted">
                                Reviewed by <?php echo htmlspecialchars($featuredBook['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </small>

                        <?php else: ?>

                            <p class="text-muted">
                                This book hasn't received a review yet. Be the first!
                            </p>

                        <?php endif; ?>

                        <div class="mt-3">
                            <a
                                href="books/view.php?book_id=<?php echo (int) $featuredBook['book_id']; ?>"
                                class="btn btn-primary"
                            >
                                View Book
                            </a>
                        </div>

                    </div>
                </div>

            <?php else: ?>

                <p>No books are available yet. Check back soon!</p>

            <?php endif; ?>

        </div>

        <a href="books/index.php" class="btn btn-primary">
            Browse Books
        </a>

        

    </main>

    
<?php
    include_once "includes/footer.php";
?>
