<?php
    require_once "../includes/db.php";
    require_once '../includes/header.php'
?>

<main>
    <div class="container mx-auto">
        <form action="#submitted" method='POST'>
            <label class="mb-3 form-label" for="bTitle">Book Title</label>
            <input class="mb-3 form-control" type="text" id="bTitle" name="bTitle">
            <label class="mb-3 form-label" for="author">Author</label>
            <input class="mb-3 form-control" type="text" id="author" name="author">
            <label class="mb-3 form-label" for="genre">Genre</label>
            <input class="mb-3 form-control" type="text" id="genre" name="genre">
            <label class="mb-3 form-label" for="pDate">Publication Date</label>
            <input class="mb-3 form-control" type="text" id="pDate" name="pDate">
            <div class="d-flex justify-content-center mt-3 mb-3">
                <input class="btn btn-primary" type="submit" value="Submit">
            </div>
        </form>
    </div>
</main>

<?php
    require_once '../includes/footer.php'
?>