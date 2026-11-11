
<?php
    require_once __DIR__ . '/../includes/auth.php';

    // Prevent visitors from accessing this page without logging in.
    require_login();
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container mx-auto">

        <h1>My Profile</h1>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?>!
        </p>

        <p>
            Email:
            <?= htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <a href="../reviews/index.php" class="btn btn-primary">
            View My Reviews
        </a>

        <form action="logout.php" method="POST" class="mt-3">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
            >

            <button type="submit" class="btn btn-outline-danger mb-3">
                Logout
            </button>
        </form>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
