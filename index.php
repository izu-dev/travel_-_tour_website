<?php
session_start();
include 'db_connect.php';

// Fetch featured tours
$sql = "SELECT * FROM tours ORDER BY id DESC LIMIT 6";
$result = $conn->query($sql);
$tours = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tours[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel & Tours - Explore the World</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">Travel & Tours</div>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="tours.php">Tours</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">My Bookings</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Explore the World</h1>
            <p>Discover amazing places with our exclusive tour packages</p>
            <a href="tours.php" class="btn">View Tours</a>
        </div>
    </section>

    <section class="tours-section">
        <div class="container">
            <h2 class="section-title">Featured Tours</h2>
            
            <div class="tours-grid">
                <?php foreach ($tours as $tour): ?>
                <div class="tour-card">
                    <div class="tour-image">
                        <img src="<?php echo $tour['image']; ?>" alt="<?php echo $tour['title']; ?>">
                    </div>
                    <div class="tour-details">
                        <h3><?php echo $tour['title']; ?></h3>
                        <p><?php echo substr($tour['description'], 0, 100) . '...'; ?></p>
                        <p><strong>Location:</strong> <?php echo $tour['location']; ?></p>
                        <p><strong>Duration:</strong> <?php echo $tour['duration']; ?></p>
                        <div class="tour-price">$<?php echo $tour['price']; ?></div>
                        <a href="tour-details.php?id=<?php echo $tour['id']; ?>" class="btn">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($tours)): ?>
                <p>No tours available at the moment.</p>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="tours.php" class="btn">View All Tours</a>
            </div>
        </div>
    </section>

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
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="dashboard.php">My Bookings</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
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