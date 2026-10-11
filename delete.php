
<?php
require_once __DIR__ . "/dbcon.php";

// Get user ID from URL
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

// Delete user from database
$stmt = $conn->prepare(
    "DELETE FROM users WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Redirect to main page
header("Location: index.php?msg=deleted");
exit;
?>
