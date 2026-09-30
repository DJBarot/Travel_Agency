<?php
$packages = [
    1 => [
        "title" => "Saputara - 2 Days Tour",
        "price" => "₹7,500 per person",
        "duration" => "2 Days / 1 Night",
        "hotel" => "3-Star Hill Resort",
        "includes" => ["Hotel Stay", "Daily Breakfast", "Sightseeing Tour", "Boating in Saputara Lake", "Sunset Point Visit"],
        "excludes" => ["Personal Expenses", "Adventure Activities Fees"],
        "itinerary" => [
            "Day 1: Arrival & Sightseeing (Saputara Lake, Step Garden, Sunset Point)",
            "Day 2: Governor's Hill, Ropeway Ride & Departure"
        ],
        "image" => "../img/package-7.jpg" // Update with the correct image path
    ],
];

$package_id = isset($_GET['package_id']) && array_key_exists($_GET['package_id'], $packages) ? $_GET['package_id'] : 1;
$package = $packages[$package_id];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($package["title"]); ?></title>
    
    <!-- Bootstrap & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/package.css">
</head>
<body>

<!-- Package Details Container -->
<div class="container mt-5">
    <div class="package-container">
        <img src="<?php echo $package["image"]; ?>" class="package-img" alt="Package Image">
        
        <h2 class="mt-3"><?php echo htmlspecialchars($package["title"]); ?></h2>
        <p class="text-muted"><?php echo htmlspecialchars($package["duration"]); ?> - <strong><?php echo htmlspecialchars($package["price"]); ?> per person</strong></p>

        <h4 class="mt-4">🏨 Stay at: <?php echo htmlspecialchars($package["hotel"]); ?></h4>

        <h5 class="mt-3">📌 Inclusions:</h5>
        <ul class="list-group">
            <?php foreach ($package["includes"] as $item) { echo "<li class='list-group-item'>✅ $item</li>"; } ?>
        </ul>

        <h5 class="mt-3">🚫 Exclusions:</h5>
        <ul class="list-group">
            <?php foreach ($package["excludes"] as $item) { echo "<li class='list-group-item'>❌ $item</li>"; } ?>
        </ul>

        <h5 class="mt-3">📅 Itinerary:</h5>
        <ul class="list-group">
            <?php foreach ($package["itinerary"] as $day) { echo "<li class='list-group-item'>📍 $day</li>"; } ?>
        </ul>

        <div class="text-center mt-4">
            <a href="book-now.php?package_id=<?php echo urlencode($package_id); ?>" class="btn btn-primary btn-lg">Book Now</a>
            <a href="../package.html" class="btn btn-primary btn-lg">Back</a>
        </div>
    </div>
</div>

</body>
</html>
