
<?php
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    $errors = [];

    $full_name = '';
    $email = '';

    // Process registration.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $full_name = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));

        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate CSRF token.
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $errors[] = "Invalid form submission. Please try again.";
        }

        // Validate full name.
        if ($full_name === '') {
            $errors[] = "Please enter your full name.";
        }

        // Validate email.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        // Validate password.
        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors[] = "Password must be between 8 and 72 bytes.";
        }

        // Confirm password.
        if ($password !== $confirm_password) {
            $errors[] = "Your passwords do not match.";
        }

        // Check whether the email already exists.
        if (empty($errors)) {

            $checkSql = "SELECT user_id FROM users WHERE email = ?";

            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $errors[] = "An account with that email already exists.";
            }

            $checkStmt->close();
        }

        // Create the account.
        if (empty($errors)) {

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {

                $sql = "INSERT INTO users
                        (full_name, email, password_hash)
                        VALUES (?, ?, ?)";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "sss",
                    $full_name,
                    $email,
                    $password_hash
                );

                $stmt->execute();
                $stmt->close();

                // Redirect to login after successful registration.
                header("Location: index.php?registered=1");
                exit;

            } catch (mysqli_sql_exception $e) {

                error_log("Account registration failed: " . $e->getMessage());

                $errors[] = "Unable to create your account. Please try again.";
            }
        }
    }
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <div class="container mx-auto">

        <h1 class="mb-4">Create an Account</h1>

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

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="full_name" class="form-label">
                Full Name
            </label>

            <input
                class="form-control mb-3"
                type="text"
                id="full_name"
                name="full_name"
                value="<?= htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') ?>"
                maxlength="255"
                required
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
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
                required
            >

            <label for="confirm_password" class="form-label">
                Confirm Password
            </label>

            <input
                class="form-control mb-3"
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
                required
            >

            <div class="d-flex justify-content-center mt-3 mb-3">
                <button class="btn btn-primary" type="submit">
                    Create Account
                </button>
            </div>

        </form>

        <div class="text-center mb-3">
            <a href="index.php">Already have an account? Log in!</a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
