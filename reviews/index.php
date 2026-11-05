<?php
    require_once '../includes/header.php'
?>

<main>
    <label for="username">Username</label>
    <input type="text" id="username" name="username">
    <label for="bTitle">Book Title</label>
    <input type="text" id="bTitle" name="bTitle">
    <label for="genre">Genre</label>
    <input type="text" id="genre" name="genre">
    <label for="rating">Rating</label>
    <input type="text" id="rating" name="rating">
    <label for="rText">Review</label>
    <textarea name="rText" id="rText" rows='10' cols='30'>Enter text review here.</textarea>
</main>

<?php
    require_once '../includes/footer.php'
?>