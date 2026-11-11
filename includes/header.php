<?php
    require_once __DIR__ . '/auth.php';

    $isLoggedIn = !empty($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booked & Reviewed</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">

            <a class="navbar-brand" href="/index.php">
                Booked & Reviewed
            </a>

            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mt-3">

                    <li class="nav-item">
                        <a class="nav-link" href="/index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/books/index.php">Books</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/reviews/index.php">Reviews</a>
                    </li>

                    <?php if ($isLoggedIn): ?>

                        <li class="nav-item">
                            <span class="nav-link">
                                Welcome, <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/users/profile.php">
                                My Profile
                            </a>
                        </li>

                        <li class="nav-item">
                            <form action="/users/logout.php" method="POST">
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"
                                >

                                <button type="submit" class="nav-link btn btn-link">
                                    Log Out
                                </button>
                            </form>
                        </li>

                    <?php else: ?>

                        <li class="nav-item">
                            <a class="nav-link" href="/users/index.php">
                                Log In
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/users/create.php">
                                Sign Up
                            </a>
                        </li>

                    <?php endif; ?>

                </ul>
            </div>

        </div>
    </nav>