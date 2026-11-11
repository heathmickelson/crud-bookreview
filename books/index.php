<?php
    require_once "../includes/db.php";
    require_once '../includes/header.php'
?>

<main>
    <h1>My Reviews</h1>
        <?php
            // if(!books)
            echo "<ul>";
            for ($i = 0; $i <= 10; $i++) {
                 echo "<li>
                    
                 </li>";// Need to make small list of at LEAST, Book Title, Genre!! Options to view, edit, delete, OR Add Review
            }
            echo "</ul>";
            // else
            // echo "<h3> No books yet!! </h3>
        ?>

</main>

<?php
    require_once '../includes/footer.php'
?>