<?php
require_once "config/session_timeout.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/db.php";
include "includes/csrf_token.php";

// Standardize session initialization checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Authentication Gate
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Enforce strict HTTP POST request method strategy for data destruction operations
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

verify_csrf_token($_POST["csrf_token"] ?? "");

if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
    die("Invalid rainfall reading.");
}

$id = (int) $_POST["id"];
$user_id = $_SESSION["user_id"];
$user_role = $_SESSION["role"] ?? "user"; // Fallback default

// Construct context-aware SQL execution queries based on authorization layers
if ($user_role === "admin") {
    // Admin Override: Delete by ID alone regardless of ownership
    $stmt = $conn->prepare("
        DELETE FROM rainfall_data
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
} else {
    // Standard User: Strict binding validation match on both record ID and creator ID
    $stmt = $conn->prepare("
        DELETE FROM rainfall_data
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $id, $user_id);
}

if ($stmt->execute()) {
    // Check if a row was actually deleted (handles edge case where a non-admin tries to delete a non-owned ID)
    if ($stmt->affected_rows > 0) {
        $_SESSION["delete_message"] = "Rainfall reading deleted successfully.";
    } else {
        $_SESSION["delete_message"] = "No changes made. Either record does not exist or access was denied.";
    }
    
    $stmt->close();
    header("Location: view_readings.php");
    exit();
} else {
    echo "Error executing database deletion payload command.";
    $stmt->close();
}
?>