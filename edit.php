

<?php
require_once __DIR__ . "/dbcon.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Get user ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

// Find user
$stmt = $conn->prepare(
    "SELECT name, email FROM users WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: index.php");
    exit;
}

$error = "";

// Update user
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid name and email address.";
        $user["name"] = $name;
        $user["email"] = $email;
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $name, $email, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?msg=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit User</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0 mx-auto"
         style="max-width: 600px;">

        <div class="card-body p-4">
            <h3 class="mb-4">Edit User</h3>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="edit.php?id=<?= (int)$id ?>"
                  method="POST">

                <div class="mb-3">
                    <label for="name" class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        value="<?= e($user["name"]) ?>"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="<?= e($user["email"]) ?>"
                        maxlength="255"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Update User
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>

            </form>
        </div>
    </div>
</div>

</body>
</html>
