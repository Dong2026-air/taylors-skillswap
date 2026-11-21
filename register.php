<?php

session_start();

require_once "pdo.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name == "" || $email == "" || $password == "" || $confirm_password == "") {

        $message = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $message = "This email is already registered. Please log in instead.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (name, email, password, role)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hashedPassword,
                "student"
            ]);

            $_SESSION["registration_success"] =
                "Registration successful! Please log in with your new account.";

            header("Location: login.php");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Register - Taylor's SkillSwap</title>
</head>

<body class="auth-page register-page">

    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-mark" href="index.php">
                <span class="brand-icon">&harr;</span>
                <span>SkillSwap</span>
            </a>

            <nav class="main-nav">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="browse_sessions.php">Browse Sessions</a>

                <?php if (isset($_SESSION["user_id"])): ?>
                    <a class="nav-link" href="create_session.php">Create Session</a>
                    <a class="nav-link" href="my_sessions.php">My Sessions</a>
                <?php endif; ?>
            </nav>

            <div class="nav-account">
                <?php if (isset($_SESSION["user_id"])): ?>
                    <a class="user-link" href="profile.php">
                        Hi, <?php echo htmlentities($_SESSION["name"]); ?>
                    </a>
                    <a class="btn btn-outline btn-small" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline btn-small" href="login.php">Login</a>
                    <a class="btn btn-primary btn-small active-register" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="auth-main register-main">

        <section class="auth-card register-card">
            <p class="eyebrow">TAYLOR'S SKILLSWAP</p>
            <h1>Create your account</h1>
            <p class="auth-subtitle">Join Taylor's peer learning community.</p>

            <?php if ($message != ""): ?>
                <div class="status-message auth-message">
                    <?php echo htmlentities($message); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="register.php">

                <div class="form-field">
                    <label for="name">Full name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        maxlength="100"
                        placeholder="Alex Tan"
                        value="<?php echo isset($name) ? htmlentities($name) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        placeholder="alex@example.edu"
                        value="<?php echo isset($email) ? htmlentities($email) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="confirm_password">Confirm password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Enter your password again"
                        required
                    >
                </div>

                <button class="btn btn-primary auth-submit" type="submit">Create account</button>

            </form>

            <p class="auth-switch">
                Already registered?
                <a href="login.php">Log in</a>
            </p>
        </section>

    </main>

</body>

</html>