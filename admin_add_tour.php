<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $duration = trim($_POST['duration']);
    $price = floatval($_POST['price']);
    
    // Handle image upload
    $target_dir = "uploads/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $unique_filename = $target_dir . uniqid() . '.' . $imageFileType;
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }
    
    // Check file size (limit to 5MB)
    if ($_FILES["image"]["size"] > 5000000) {
        die("Sorry, your file is too large.");
    }
    
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }
    
    // Upload file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $unique_filename)) {
        // Insert tour into database
        $stmt = $conn->prepare("INSERT INTO tours (title, description, location, duration, price, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssds", $title, $description, $location, $duration, $price, $unique_filename);
        
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tour - Travel & Tours</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">Travel & Tours - Admin</div>
                <ul class="nav-links">
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container" style="max-width: 800px;">
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

    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2023 Travel & Tours. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>