<?php

session_start();

require_once "pdo.php";

// Only logged-in users can view and edit their profile.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$message = "";

// Load the current user's profile.
$stmt = $pdo->prepare(
    "SELECT user_id, name, email, role
     FROM users
     WHERE user_id = ?"
);

$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));

    if ($name == "" || $email == "") {
        $message = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } else {

        // Make sure another account is not already using this email.
        $checkStmt = $pdo->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ? AND user_id != ?"
        );

        $checkStmt->execute([
            $email,
            $user_id
        ]);

        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $message = "This email is already used by another account.";

        } else {

            $updateStmt = $pdo->prepare(
                "UPDATE users
                 SET name = ?, email = ?
                 WHERE user_id = ?"
            );

            $updateStmt->execute([
                $name,
                $email,
                $user_id
            ]);

            // Keep the session name in sync with the updated profile.
            $_SESSION["name"] = $name;

            header("Location: profile.php?updated=1");
            exit;
        }
    }

    // Keep submitted values in the form if validation fails.
    $user["name"] = $name;
    $user["email"] = $email;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>My Profile - Taylor's SkillSwap</title>
</head>

<body class="profile-page">

    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-mark" href="index.php">
                <span class="brand-icon">&harr;</span>
                <span>SkillSwap</span>
            </a>

            <nav class="main-nav">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="browse_sessions.php">Browse Sessions</a>
                <a class="nav-link" href="create_session.php">Create Session</a>
                <a class="nav-link" href="my_sessions.php">My Sessions</a>
            </nav>

            <div class="nav-account">
                <a class="user-link active-user" href="profile.php">
                    Hi, <?php echo htmlentities($_SESSION["name"]); ?>
                </a>
                <a class="btn btn-outline btn-small" href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <main class="profile-main">

        <div class="page-heading profile-heading">
            <p class="eyebrow">MY PROFILE</p>
            <h1>Account information</h1>
            <p>View and update your Taylor's SkillSwap profile.</p>
        </div>

        <?php if (isset($_GET["updated"]) && $_GET["updated"] == "1"): ?>
            <div class="status-message success-message">Profile updated successfully!</div>
        <?php endif; ?>

        <?php if ($message != ""): ?>
            <div class="status-message error-message">
                <?php echo htmlentities($message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-layout">

            <section class="profile-summary-card">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user["name"], 0, 1)); ?>
                </div>

                <h2><?php echo htmlentities($user["name"]); ?></h2>
                <p><?php echo htmlentities($user["email"]); ?></p>

                <div class="profile-role-box">
                    <span class="info-label">Role</span>
                    <strong><?php echo ucfirst(htmlentities($user["role"])); ?></strong>
                </div>
            </section>

            <form class="profile-form-card" method="POST" action="profile.php">

                <div class="profile-form-heading">
                    <h2>Edit Profile</h2>
                    <p>Keep your name and email address up to date.</p>
                </div>

                <div class="form-field">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        maxlength="100"
                        value="<?php echo htmlentities($user["name"]); ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        value="<?php echo htmlentities($user["email"]); ?>"
                        required
                    >
                </div>

                <div class="form-actions">
                    <a class="btn btn-outline" href="index.php">Cancel</a>
                    <button class="btn btn-primary" type="submit">Save Profile</button>
                </div>

            </form>

        </div>

    </main>

</body>

</html>