<?php
    require_once "../includes/header.php"
?>

<main>
    <div class="container mx-auto">
        <form action="#submitted" method='POST'>
            <label class="mb-3 form-label" for="user">Username</label>
            <input class="mb-3 form-control" type="text" id="user" name="user">
            <label class="form-label d-block" for="password">Password</label>
            <input class="mb-3 form-control" type="text" id="password" name="password">
            <div class="d-flex justify-content-center mt-3 mb-3">
                <input class="btn btn-primary" type="submit" value="Submit">
            </div>
        </form>
        <div class="d-flex justify-content-center mt-3 mb-3">
            <a href="create.php">Don't have an account, Create one!</a>
        </div>
    </div>
</main>

<?php
    require_once "../includes/footer.php"
?>