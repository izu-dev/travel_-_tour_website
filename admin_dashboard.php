<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all tours
$sql = "SELECT * FROM tours ORDER BY id DESC";
$result = $conn->query($sql);
$tours = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tours[] = $row;
    }
}

// Fetch all bookings
$sql = "SELECT b.*, u.username, t.title 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN tours t ON b.tour_id = t.id 
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);
$bookings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Process booking status update
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Travel & Tours</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px 0;
        }
        
        .admin-sidebar {
            flex: 0 0 250px;
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-content {
            flex: 1;
            min-width: 300px;
        }
        
        .admin-menu {
            list-style: none;
        }
        
        .admin-menu li {
            margin-bottom: 10px;
        }
        
        .admin-menu a {
            display: block;
            padding: 10px;
            color: var(--dark-gray);
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .admin-menu a:hover, .admin-menu a.active {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .admin-panel {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-panel h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: var(--light-gray);
        }
        
        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">Travel & Tours - Admin</div>
                <ul class="nav-links">
                    <li><a href="index.php">View Website</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="admin-container">
            <div class="admin-sidebar">
                <h3>Admin Menu</h3>
                <ul class="admin-menu">
                    <li><a href="#bookings" class="active" onclick="showPanel('bookings')">Bookings</a></li>
                    <li><a href="#tours" onclick="showPanel('tours')">Tours</a></li>
                    <li><a href="#add-tour" onclick="showPanel('add-tour')">Add New Tour</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
                <div id="bookings" class="admin-panel">
                    <h2>All Bookings</h2>
                    
                    <?php if (empty($bookings)): ?>
                        <p>No bookings found.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Tour</th>
                                    <th>Date</th>
                                    <th>People</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td><?php echo $booking['username']; ?></td>
                                    <td><?php echo $booking['title']; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($booking['booking_date'])); ?></td>
                                    <td><?php echo $booking['number_of_people']; ?></td>
                                    <td>$<?php echo $booking['total_price']; ?></td>
                                    <td>
                                        <form action="admin_dashboard.php" method="post">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status" class="status-select" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <a href="admin_view_booking.php?id=<?php echo $booking['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 14px;">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div id="tours" class="admin-panel" style="display: none;">
                    <h2>All Tours</h2>
                    
                    <?php if (empty($tours)): ?>
                        <p>No tours found.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tours as $tour): ?>
                                <tr>
                                    <td><?php echo $tour['id']; ?></td>
                                    <td><?php echo $tour['title']; ?></td>
                                    <td><?php echo $tour['location']; ?></td>
                                    <td><?php echo $tour['duration']; ?></td>
                                    <td>$<?php echo $tour['price']; ?></td>
                                    <td>
                                        <a href="admin_edit_tour.php?id=<?php echo $tour['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 14px; margin-right: 5px;">Edit</a>
                                        <a href="admin_delete_tour.php?id=<?php echo $tour['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 14px; background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this tour?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div id="add-tour" class="admin-panel" style="display: none;">
                    <h2>Add New Tour</h2>
                    
                    <form action="admin_add_tour.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Tour Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">Duration</label>
                            <input type="text" id="duration" name="duration" placeholder="e.g. 3 days, 2 nights" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price per Person ($)</label>
                            <input type="number" id="price" name="price" min="1" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Tour Image</label>
                            <input type="file" id="image" name="image" accept="image/*" required>
                        </div>
                        
                        <button type="submit" class="form-btn">Add Tour</button>
                    </form>
                </div>
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

    <script>
        function showPanel(panelId) {
            // Hide all panels
            document.querySelectorAll('.admin-panel').forEach(panel => {
                panel.style.display = 'none';
            });
            
            // Show the selected panel
            document.getElementById(panelId).style.display = 'block';
            
            // Update active menu item
            document.querySelectorAll('.admin-menu a').forEach(link => {
                link.classList.remove('active');
            });
            
            document.querySelector(`.admin-menu a[href="#${panelId}"]`).classList.add('active');
        }
    </script>
</body>
</html>