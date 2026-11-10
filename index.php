<?php
    include_once "includes/db.php";
    include_once "includes/header.php";
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
            <?php
                // Select random book from DB and place relevant information
                // $bookName
                // $author
                // $recentRating
                // $recentReviewTruncated
            ?>
        </div>

        <a href="books/index.php" class="btn btn-primary">
            Browse Books
        </a>

        

    </main>

    
<?php
    include_once "includes/footer.php";
?>
