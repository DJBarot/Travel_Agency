<?php
$packages = [
    1 => [
        "title" => "Kerala - 3 Days Tour",
        "price" => "₹14,500 per person",
        "duration" => "3 Days / 2 Nights",
        "hotel" => "4-Star Lakeside Resort",
        "includes" => ["Hotel Stay", "Daily Breakfast", "Houseboat Cruise", "Backwater Tour"],
        "excludes" => ["Personal Expenses", "Entry Fees for Attractions"],
        "itinerary" => [
            "Day 1: Arrival in Kochi & Sightseeing (Fort Kochi, Marine Drive, Chinese Fishing Nets)",
            "Day 2: Munnar Tea Gardens & Waterfalls Visit",
            "Day 3: Alleppey Houseboat Cruise & Departure"
        ],
        "image" => "../img/package-6.jpg" // Update with the correct image path
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
        <p class="text-muted"><?php echo htmlspecialchars($package["duration"]); ?> - <strong><?php echo htmlspecialchars($package["price"]); ?></strong></p>

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
