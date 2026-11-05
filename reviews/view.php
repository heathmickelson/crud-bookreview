<?php
    require_once '../includes/header.php'
?>

<main>
    <h1>Review</h1>
        <?php
            // if(!noReviews)
            echo "<ul>";
            for ($i = 0; $i <= 10; $i++) {
                 echo "<li>
                    
                 </li>";// Need to make small list of at LEAST, Book Title, Rating, Date Submitted, Truncated Review.
            }
            echo "</ul>";
            // else
            // echo "<h3> You haven't written any reviews yet!! </h3>
        ?>

</main>

<?php
    require_once '../includes/footer.php'
?>