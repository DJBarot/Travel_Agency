<?php
// Define the default price based on the selected package (example)
$package_id = 1;  // You can fetch this dynamically if necessary
$default_price = 0;

// Set default prices for packages
$prices = [
    1 => 25000,  // Package 1: 5-star
    2 => 22000,  // Package 2: 4-star
    3 => 18000,  // Package 3: Budget
    4 => 30000,  // Package 4: 5-star
];

// Get the price for the selected package
if (isset($prices[$package_id])) {
    $default_price = $prices[$package_id];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Now</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/book-now.css">
    <script>
        function updatePriceAndHotel() {
            var packageId = <?php echo $package_id; ?>;
            var hotelType = document.getElementById("hotel").value;

            var prices = {
                1: { "5-star": 25000, "4-star": 22000, "Budget": 18000 },
                2: { "5-star": 20000, "4-star": 17000, "Budget": 14000 },
                3: { "5-star": 15000, "4-star": 13000, "Budget": 11000 },
                4: { "5-star": 30000, "4-star": 27000, "Budget": 23000 }
            };

            var price = prices[packageId][hotelType];
            document.getElementById("price").innerText = "₹" + price;
            document.getElementById("price_input").value = price;

            // Fetch hotel details via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_hotel.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        document.getElementById("hotel_name").innerText = response.hotel_name;
                        document.getElementById("hotel_location").innerText = response.hotel_location;
                        document.getElementById("hotel_id").value = response.hotel_id;
                        document.getElementById("hotel_details").style.display = "block";
                    } else {
                        document.getElementById("hotel_details").style.display = "none";
                    }
                }
            };
            xhr.send("hotel_category=" + hotelType + "&package_id=" + packageId);
        }
    </script>
</head>
<body>

<div class="booking-container">
    <h1>Book Your Package</h1>
    <form action="confirm-booking.php" method="POST">
        <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($package_id); ?>">
        <input type="hidden" name="price" id="price_input" value="<?php echo $default_price; ?>">
        <input type="hidden" name="hotel_id" id="hotel_id">

        <label>Name:</label>
        <input type="text" name="name" class="form-control" required>

        <label>Email:</label>
        <input type="email" name="email" class="form-control" required>

        <label>Phone:</label>
        <input type="text" name="phone" class="form-control" required>

        <label>Select Hotel Type:</label>
        <select name="hotel" id="hotel" class="form-control" onchange="updatePriceAndHotel()">
            <option value="5-star">5-Star Hotel</option>
            <option value="4-star">4-Star Hotel</option>
            <option value="Budget">Budget Hotel</option>
        </select>

        <div id="hotel_details" class="hotel-details">
            <p><strong>Hotel Name:</strong> <span id="hotel_name"></span></p>
            <p><strong>Location:</strong> <span id="hotel_location"></span></p>
        </div>

        <div class="price-box">
            Total Price: <span id="price">₹<?php echo number_format($default_price, 2); ?></span>
        </div>

        <button type="submit" class="btn btn-primary">Confirm Booking</button>
    </form>
</div>

</body>
</html>
