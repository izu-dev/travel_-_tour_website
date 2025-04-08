<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's bookings
$stmt = $conn->prepare("
    SELECT b.*, t.title, t.location, t.image 
    FROM bookings b 
    JOIN tours t ON b.tour_id = t.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Process booking cancellation
if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    
    // Update booking status to cancelled
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    
    // Redirect to refresh the page
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WanderWorld</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .booking-card {
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
        }
        
        .booking-image {
            flex: 0 0 200px;
            height: 200px;
        }
        
        .booking-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .booking-details {
            flex: 1;
            padding: 20px;
            min-width: 300px;
        }
        
        .booking-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="tours.php">Tours</a></li>
                    <li><a href="dashboard.php">My Bookings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="padding: 50px 0;">
        <h1>My Bookings</h1>
        <p>Welcome, <?php echo $_SESSION['username']; ?>! Here are your tour bookings:</p>
        
        <?php if (empty($bookings)): ?>
            <div style="margin-top: 30px;">
                <p>You don't have any bookings yet.</p>
                <a href="tours.php" class="btn" style="margin-top: 15px;">Browse Tours</a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-image">
                        <img src="<?php echo $booking['image']; ?>" alt="<?php echo $booking['title']; ?>">
                    </div>
                    <div class="booking-details">
                        <div class="status-badge status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </div>
                        <h3><?php echo $booking['title']; ?></h3>
                        <p><strong>Location:</strong> <?php echo $booking['location']; ?></p>
                        <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                        <p><strong>Number of People:</strong> <?php echo $booking['number_of_people']; ?></p>
                        <p><strong>Total Price:</strong> $<?php echo $booking['total_price']; ?></p>
                        <p><strong>Booked on:</strong> <?php echo date('F j, Y', strtotime($booking['created_at'])); ?></p>
                        
                        <?php if ($booking['status'] === 'pending'): ?>
                            <div class="booking-actions">
                                <form action="dashboard.php" method="post">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="btn" style="background-color: #dc3545;">Cancel Booking</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>We provide the best travel experiences around the world.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="tours.php">Tours</a></li>
                        <li><a href="dashboard.php">My Bookings</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: info@traveltours.com</p>
                    <p>Phone: +1 234 567 8900</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Travel & Tours. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>