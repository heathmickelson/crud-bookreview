
<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    $errors = [];
    $email = '';

    // Process login.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Validate CSRF token.
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $errors[] = "Invalid form submission. Please try again.";
        }

        if ($email === '' || $password === '') {
            $errors[] = "Please enter your email and password.";
        }

        if (empty($errors)) {

            $sql = "SELECT user_id, full_name, email, password_hash, is_admin
                    FROM users
                    WHERE email = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $stmt->bind_result(
                $user_id,
                $full_name,
                $stored_email,
                $password_hash,
                $is_admin
            );

            if (
                $stmt->fetch()
                && $password_hash !== null
                && password_verify($password, $password_hash)
            ) {

                $stmt->close();

                // Prevent session fixation.
                session_regenerate_id(true);

                // Store authenticated user information.
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $stored_email;
                $_SESSION['is_admin'] = (int) $is_admin;

                // Redirect to profile.
                header("Location: profile.php");
                exit;

            } else {

                $stmt->close();

                $errors[] = "Incorrect email or password.";
            }
        }
    }
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container mx-auto">

        <h1 class="mb-4">Login</h1>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                Your account was created successfully! Please log in.
            </div>
        <?php endif; ?>

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

        <form method="POST" class="needs-validation" novalidate>

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="email" class="form-label">
                Email
            </label>

            <input
                class="form-control mb-3"
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="email"
                required
            >

            <label for="password" class="form-label">
                Password
            </label>

            <input
                class="form-control mb-3"
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <div class="d-flex justify-content-center mt-3 mb-3">
                <button class="btn btn-primary" type="submit">
                    Login
                </button>
            </div>

        </form>

        <div class="text-center mb-3">
            <a href="create.php">Don't have an account? Create one!</a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
