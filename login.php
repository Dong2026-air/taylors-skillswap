<?php

session_start();

require_once "pdo.php";

$message = "";
$return_url = $_POST["return_url"] ?? $_GET["return_url"] ?? "";
$redirect_url = "index.php";

if ($return_url !== "" && preg_match('/^session_details\.php\?id=\d+$/', $return_url)) {
    $redirect_url = $return_url;
}

if (isset($_SESSION["registration_success"])) {
    $message = $_SESSION["registration_success"];
    unset($_SESSION["registration_success"]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if ($email == "" || $password == "") {

        $message = "Please fill in all fields.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT user_id, name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            header("Location: index.php");
            exit;

        } else {

            $message = "Invalid email or password.";
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
    <title>Login - Taylor's SkillSwap</title>
</head>

<body class="auth-page login-page">

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
                    <a class="btn btn-outline btn-small active-auth" href="login.php">Login</a>
                    <a class="btn btn-primary btn-small" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="auth-main">

        <section class="auth-card">
            <p class="eyebrow">TAYLOR'S SKILLSWAP</p>
            <h1>Welcome back</h1>
            <p class="auth-subtitle">Log in to join or host a session.</p>

            <?php if ($message != ""): ?>
                <div class="status-message auth-message">
                    <?php echo htmlentities($message); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="login.php">

                <div class="form-field">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="alex@example.edu"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button class="btn btn-primary auth-submit" type="submit">Log in</button>

            </form>

            <p class="auth-switch">
                New to SkillSwap?
                <a href="register.php">Register</a>
            </p>
        </section>

    </main>

</body>

</html>