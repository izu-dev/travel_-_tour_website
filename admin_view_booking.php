<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if booking ID is provided

$booking_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$booking_id) {
    header("Location: admin_dashboard.php");
    exit();
}
// if (!isset($_GET['id']) || empty($_GET['id'])) {
//     header("Location: admin_dashboard.php");
//     exit();
// }

// $booking_id = $_GET['id'];

// Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, u.username, u.email, t.title, t.location, t.duration, t.image 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN tours t ON b.tour_id = t.id 
    WHERE b.id = ?
");
// $stmt->bind_param("i", $booking_id);
// $stmt->execute();
// $result = $stmt->get_result();

if (!$stmt) {
    error_log("Database error: " . $conn->error);
    die("An error occurred while fetching booking details.");
}
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

// Process status update

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status'])) {
    $status = $_POST['status'];
    $allowed_statuses = ['pending', 'confirmed', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        die("Invalid status value.");
    }

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        die("An error occurred while updating the booking status.");
    }
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking status updated successfully.";
        header("Location: admin_view_booking.php?id=" . $booking_id);
        exit();
    }
}

// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status'])) {
//     $status = $_POST['status'];
    
//     $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
//     $stmt->bind_param("si", $status, $booking_id);
    
//     if ($stmt->execute()) {
//         // Refresh booking data
//         header("Location: admin_view_booking.php?id=" . $booking_id);
//         exit();
//     }
// }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WanderWorld</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .booking-details {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 30px 0;
        }
        
        .booking-image {
            flex: 0 0 300px;
        }
        
        .booking-image img {
            width: 100%;
            border-radius: 8px;
        }
        
        .booking-info {
            flex: 1;
            min-width: 300px;
        }
        
        .info-group {
            margin-bottom: 20px;
        }
        
        .info-group h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">WanderWorld</div>
                <ul class="nav-links">
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Booking Details</h1>
        
        <div class="booking-details">
            <div class="booking-image">
                <img src="<?php echo $booking['image']; ?>" alt="<?php echo $booking['title']; ?>">
            </div>
            
            <div class="booking-info">
                <div class="status-badge status-<?php echo $booking['status']; ?>">
                    <?php echo ucfirst($booking['status']); ?>
                </div>
                
                <div class="info-group">
                    <h3>Tour Information</h3>
                    <p><strong>Tour:</strong> <?php echo $booking['title']; ?></p>
                    <p><strong>Location:</strong> <?php echo $booking['location']; ?></p>
                    <p><strong>Duration:</strong> <?php echo $booking['duration']; ?></p>
                </div>
                
                <div class="info-group">
                    <h3>Booking Information</h3>
                    <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                    <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                    <p><strong>Number of People:</strong> <?php echo $booking['number_of_people']; ?></p>
                    <p><strong>Total Price:</strong> $<?php echo $booking['total_price']; ?></p>
                    <p><strong>Booked on:</strong> <?php echo date('F j, Y', strtotime($booking['created_at'])); ?></p>
                </div>
                
                <div class="info-group">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo $booking['username']; ?></p>
                    <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                </div>
                
                <div class="info-group">
                    <h3>Update Status</h3>
                    <form action="admin_view_booking.php?id=<?php echo $booking_id; ?>" method="post">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Update Status</button>
                    </form>
                </div>
                
                <a href="admin_dashboard.php" class="btn" style="margin-top: 20px;">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2023 Travel & Tours. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>