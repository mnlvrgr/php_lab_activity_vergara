

<?php
include 'dbcon.php';
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
$name = "";
$email = "";
$error = "";
$edit_id = 0;
// GET USER DATA FOR EDITING
if (isset($_GET["edit"]) && is_numeric($_GET["edit"])) {
    $edit_id = (int) $_GET["edit"];
    if ($edit_id > 0) {
        $stmt = $conn->prepare(
            "SELECT id, name, email FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user) {
            $name = $user["name"];
            $email = $user["email"];
        } else {
            header("Location: index.php?msg=notfound");
            exit();
        }
    }
}
// ADD OR UPDATE USER
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $edit_id = (int) ($_POST["edit_id"] ?? 0);
    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid name and email address.";
    } else {
        if ($edit_id > 0) {
            // UPDATE USER
            $stmt = $conn->prepare(
                "UPDATE users SET name = ?, email = ? WHERE id = ?"
            );
            $stmt->bind_param("ssi", $name, $email, $edit_id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?msg=updated");
                exit();
            }
            $error = "Unable to update user.";
            $stmt->close();
        } else {
            // ADD NEW USER
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email) VALUES (?, ?)"
            );
            $stmt->bind_param("ss", $name, $email);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?msg=added");
                exit();
            }
            $error = "Unable to add user.";
            $stmt->close();
        }
    }
}
$result = $conn->query(
    "SELECT * FROM users ORDER BY id DESC"
);
$msg = $_GET["msg"] ?? "";
$alerts = [
    "added"    => "User added successfully!",
    "updated"  => "User updated successfully!",
    "deleted"  => "User deleted successfully!",
    "notfound" => "User not found."
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <style>
        body {
            background-color: white;
        }
        .container {
            margin-top: 40px;
            margin-bottom: 40px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .table th,
        .table td {
            vertical-align: middle;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .btn-edit {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }
        .btn-edit:hover {
            background-color: #157347;
            border-color: #146c43;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>User Management System</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <?php if (isset($alerts[$msg])): ?>
                <div
                    class="alert <?= $msg === 'notfound'
                        ? 'alert-warning'
                        : 'alert-success' ?> alert-dismissible fade show"
                    role="alert"
                >
                    <?= e($alerts[$msg]) ?>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>
            <?php endif; ?>
            <?php if ($error !== ""): ?>
                <div
                    class="alert alert-danger alert-dismissible fade show"
                    role="alert"
                >
                    <?= e($error) ?>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>
            <?php endif; ?>
            <h4 class="mb-3">
                <?= $edit_id > 0 ? "Edit User" : "Add User" ?>
            </h4>
            <form action="index.php" method="POST">
                <input
                    type="hidden"
                    name="edit_id"
                    value="<?= (int) $edit_id ?>"
                >
                <div class="mb-3">
                    <label for="name" class="form-label">
                        Full Name
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        placeholder="Enter full name"
                        maxlength="255"
                        value="<?= e($name) ?>"
                        required
                    >
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">
                        Email Address
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="Enter email address"
                        maxlength="255"
                        value="<?= e($email) ?>"
                        required
                    >
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <?= $edit_id > 0 ? "Update User" : "Submit" ?>
                </button>
                <?php if ($edit_id > 0): ?>
                    <a
                        href="index.php"
                        class="btn btn-secondary w-100 mt-2"
                    >
                        Cancel
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <div class="col-md-8">
            <h4 class="mb-3">Registered Users</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= (int) $row["id"] ?>
                                    </td>
                                    <td>
                                        <?= e($row["name"]) ?>
                                    </td>
                                    <td>
                                        <?= e($row["email"]) ?>
                                    </td>
                                    <td>
                                        <?= e(date(
                                            "M d, Y",
                                            strtotime($row["created_at"])
                                        )) ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <!-- EDIT BUTTON -->
                                        <a
                                            href="index.php?edit=<?= (int) $row["id"] ?>"
                                            class="btn btn-edit btn-sm"
                                        >
                                            Edit
                                        </a>
                                        <!-- DELETE BUTTON -->
                                        <a
                                            href="delete.php?id=<?= (int) $row["id"] ?>"
                                            class="btn btn-delete btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this user?');"
                                        >
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    No data found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>