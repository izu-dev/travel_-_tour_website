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

// Fetch tour details
$stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$tour = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $duration = trim($_POST['duration']);
    $price = floatval($_POST['price']);
    
    // Check if a new image is uploaded
    if ($_FILES['image']['size'] > 0) {
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
            // Delete old image file if it exists
            if (file_exists($tour['image']) && $tour['image'] != '') {
                unlink($tour['image']);
            }
            
            // Update tour with new image
            $stmt = $conn->prepare("UPDATE tours SET title = ?, description = ?, location = ?, duration = ?, price = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssssdsi", $title, $description, $location, $duration, $price, $unique_filename, $tour_id);
        } else {
            die("Sorry, there was an error uploading your file.");
        }
    } else {
        // Update tour without changing the image
        $stmt = $conn->prepare("UPDATE tours SET title = ?, description = ?, location = ?, duration = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssssdi", $title, $description, $location, $duration, $price, $tour_id);
    }
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WanderWorld</title>
    <link rel="stylesheet" href="styles.css">
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
        <div class="form-container" style="max-width: 800px;">
            <h2>Edit Tour</h2>
            
            <form action="admin_edit_tour.php?id=<?php echo $tour_id; ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Tour Title</label>
                    <input type="text" id="title" name="title" value="<?php echo $tour['title']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" required><?php echo $tour['description']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo $tour['location']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration</label>
                    <input type="text" id="duration" name="duration" value="<?php echo $tour['duration']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price per Person ($)</label>
                    <input type="number" id="price" name="price" min="1" step="0.01" value="<?php echo $tour['price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Current Image</label>
                    <img src="<?php echo $tour['image']; ?>" alt="<?php echo $tour['title']; ?>" style="max-width: 200px; margin-top: 10px;">
                </div>
                
                <div class="form-group">
                    <label for="image">New Image (leave empty to keep current image)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <button type="submit" class="form-btn">Update Tour</button>
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