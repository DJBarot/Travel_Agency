<?php
include 'db_connection.php'; // Ensure this connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $hotel = htmlspecialchars($_POST['hotel']);
    $package_id = htmlspecialchars($_POST['package_id']);
    $price = htmlspecialchars($_POST['price']); // Get price from hidden input field

    // Insert into bookings table
    $sql = "INSERT INTO bookings (name, email, phone, hotel, package_id, price) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $name, $email, $phone, $hotel, $package_id, $price);

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id; // Get the last inserted booking ID
    } else {
        die("Error: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    
    <!-- Bootstrap & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/confirm-booking.css">
</head>
<body>

<div class="confirmation-container">
    <h1>Booking Confirmed! 🎉</h1>
    <p><strong>Name:</strong> <?php echo $name; ?></p>
    <p><strong>Email:</strong> <?php echo $email; ?></p>
    <p><strong>Phone:</strong> <?php echo $phone; ?></p>
    <p><strong>Hotel:</strong> <?php echo $hotel; ?></p>
    <p><strong>Package ID:</strong> <?php echo $package_id; ?></p>
    <p><strong>Total Price:</strong> ₹<?php echo number_format($price, 2); ?></p>
    <form action="payment-gateway.php" method="POST">
        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
        <input type="hidden" name="name" value="<?php echo $name; ?>">
        <input type="hidden" name="email" value="<?php echo $email; ?>">
        <input type="hidden" name="phone" value="<?php echo $phone; ?>">
        <input type="hidden" name="hotel" value="<?php echo $hotel; ?>">
        <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
        <input type="hidden" name="price" value="<?php echo $price; ?>">
        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
    </form>
    <a href="../index.html" class="btn-home">Go to Home</a>
</div>

</body>
</html>
