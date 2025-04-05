<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if tour ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$tour_id = $_GET['id'];

// Fetch tour details to get the image path
$stmt = $conn->prepare("SELECT image FROM tours WHERE id = ?");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $tour = $result->fetch_assoc();
    
    // Delete the tour image file if it exists
    if (file_exists($tour['image']) && $tour['image'] != '') {
        unlink($tour['image']);
    }
    
    // Delete related bookings
    $stmt = $conn->prepare("DELETE FROM bookings WHERE tour_id = ?");
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    
    // Delete the tour
    $stmt = $conn->prepare("DELETE FROM tours WHERE id = ?");
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
}

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>