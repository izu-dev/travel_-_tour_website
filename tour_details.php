<?php
    session_start();
    include 'db_connect.php';

    // Check if tour ID is provided
    // $tour_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    // if (!$tour_id) {
    if (! isset($_GET['id']) || empty($_GET['id'])) {
        die("invalid tour id");
        header("Location: tours.php");
        exit();
    }

    $tour_id = $_GET['id'];

    // Fetch tour details
    $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: tours.php");
        exit();
    }

    $tour = $result->fetch_assoc();

    // Process booking form
    $error   = '';
    $success = '';
    

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
        $booking_date     = $_POST['booking_date'];
        $number_of_people = $_POST['number_of_people'];
        $user_id          = $_SESSION['user_id'];

        // Calculate total price
        $total_price = $tour['price'] * $number_of_people;

        if (! is_numeric($number_of_people) || $number_of_people < 1) {
            $error = "Number of people must be a valid number greater than 0.";
        } elseif (! strtotime($booking_date)) {
            $error = "Invalid booking date.";
        } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
            $error = "Booking date cannot be in the past.";

            // Insert booking
            $stmt        = $conn->prepare("INSERT INTO bookings (user_id, tour_id, booking_date, number_of_people, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $total_price = $tour['price'] * $number_of_people;
            $stmt->bind_param("iisid", $user_id, $tour_id, $booking_date, $number_of_people, $total_price);

            if ($stmt->execute()) {
                $success = "Booking successful! You can view your booking details in your dashboard.";
                header("Location: dashboard.php");
                exit();
            } else {
                error_log("Database error: " . $stmt->error);
                $error = "An error occurred while processing your booking. Please try again later.";

                // $error = "Error: " . $stmt->error;
                // exit();
            }

        }
        // /
    }

    // if (! is_numeric($number_of_people) || $number_of_people < 1) {
    //     $error = "Number of people must be a valid number greater than 0.";
    // }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tour['title']; ?> WanderWorld</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .tour-details-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 50px 0;
        }

        .tour-image-large {
            flex: 1;
            min-width: 300px;
        }

        .tour-image-large img {
            width: 100%;
            border-radius: 8px;
        }

        .tour-info {
            flex: 1;
            min-width: 300px;
        }

        .booking-form {
            background-color: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
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

    <div class="container">
        <div class="tour-details-container">
            <div class="tour-image-large">
                <img src="<?php echo $tour['image']; ?>" alt="<?php echo $tour['title']; ?>">
            </div>

            <div class="tour-info">
                <h1><?php echo $tour['title']; ?></h1>
                <div class="tour-price">$<?php echo $tour['price']; ?> per person</div>
                <p><strong>Location:</strong>                                                                                                                                        <?php echo $tour['location']; ?></p>
                <p><strong>Duration:</strong>                                                                                                                                        <?php echo $tour['duration']; ?></p>
                <p><?php echo $tour['description']; ?></p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="booking-form">
                        <h2>Book This Tour</h2>

                        <?php if ( empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (! empty($success)):  ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form action="tour_details.php?id=<?php echo $tour_id; ?>" method="post">
                            <div class="form-group">
                                <label for="booking_date">Booking Date</label>
                                <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="number_of_people">Number of People</label>
                                <input type="number" id="number_of_people" name="number_of_people" min="1" value="1" required>
                            </div>

                            <button type="submit" class="form-btn">Book Now</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Please <a href="login.php">login</a> to book this tour.
                    </div>
                <?php endif; ?>
            </div>
        </div>
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