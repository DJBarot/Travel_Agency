<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = $_POST['from'] ?? 'N/A';
    $to = $_POST['to'] ?? 'N/A';
    $bus = $_POST['bus'] ?? 'N/A';
    $selectedSeats = $_POST['selectedSeats'] ?? '';

    // Convert seat selection into an array
    $selectedSeatsArray = explode(',', $selectedSeats);
    $totalSeats = count($selectedSeatsArray);

    // Construct the route key
    $routeKey = strtolower($from . '-' . $to);

    // Define route distances (or any other metric to base pricing on)
    $routeDistances = [
        // Major cities connections
        'vadodara-mumbai' => 400,
        'mumbai-vadodara' => 400,
        'vadodara-ahmedabad' => 100,
        'ahmedabad-vadodara' => 100,
        'vadodara-surat' => 150,
        'surat-vadodara' => 150,
        'ahmedabad-rajkot' => 220,
        'rajkot-ahmedabad' => 220,
        'surat-bhavnagar' => 200,
        'bhavnagar-surat' => 200,
        'rajkot-jamnagar' => 90,
        'jamnagar-rajkot' => 90,
        'bhavnagar-junagadh' => 180,
        'junagadh-bhavnagar' => 180,
        'junagadh-porbandar' => 120,
        'porbandar-junagadh' => 120,
        'porbandar-dwarka' => 110,
        'dwarka-porbandar' => 110,
        'dwarka-gandhidham' => 200,
        'gandhidham-dwarka' => 200,
        'junagadh-surat' => 300,
        'surat-junagadh' => 300,
        // Add more connections as needed
    ];

    // Calculate base price based on distance
    // If the route is not specifically defined, calculate a price based on a default rate
    $basePrice = 0;
    if (isset($routeDistances[$routeKey])) {
        // Use predefined distance-based pricing
        $distance = $routeDistances[$routeKey];
        $basePrice = ceil($distance * 1); // ₹1 per km
    } else {
        // Calculate an estimated price for undefined routes
        // Here we'll use a matrix of cities and their approximate coordinates
        // to calculate distances
        
        // Simplified city coordinates (these are not real, just for demonstration)
        $cityCoordinates = [
            'vadodara' => ['lat' => 22.30, 'lng' => 73.21],
            'mumbai' => ['lat' => 19.08, 'lng' => 72.88],
            'ahmedabad' => ['lat' => 23.03, 'lng' => 72.58],
            'surat' => ['lat' => 21.17, 'lng' => 72.83],
            'rajkot' => ['lat' => 22.30, 'lng' => 70.78],
            'bhavnagar' => ['lat' => 21.77, 'lng' => 72.15],
            'jamnagar' => ['lat' => 22.47, 'lng' => 70.07],
            'junagadh' => ['lat' => 21.52, 'lng' => 70.46],
            'porbandar' => ['lat' => 21.64, 'lng' => 69.60],
            'dwarka' => ['lat' => 22.24, 'lng' => 68.97],
            'gandhidham' => ['lat' => 23.08, 'lng' => 70.13],
            // Add more cities as needed
        ];
        
        // If both cities are in our coordinate system, calculate distance
        if (isset($cityCoordinates[$from]) && isset($cityCoordinates[$to])) {
            // Simple distance calculation (not actual driving distance)
            $fromLat = $cityCoordinates[$from]['lat'];
            $fromLng = $cityCoordinates[$from]['lng'];
            $toLat = $cityCoordinates[$to]['lat'];
            $toLng = $cityCoordinates[$to]['lng'];
            
            // Rough distance calculation (not accurate, just for pricing)
            $distance = sqrt(pow($fromLat - $toLat, 2) + pow($fromLng - $toLng, 2)) * 111; // 111 km per degree
            $basePrice = ceil($distance * 1); // ₹1 per km
        } else {
            // Default price if we don't have coordinates
            $basePrice = 350; // Default price for unknown routes
        }
    }

    // Define bus type multipliers
    $busMultipliers = [
        'luxury' => 1.5,   // 1.5x the base price
        'deluxe' => 1.2,   // 1.2x the base price
        'standard' => 1.0  // Standard price
    ];

    // Calculate seat price
    $busMultiplier = $busMultipliers[$bus] ?? 1;
    $seatPrice = $basePrice * $busMultiplier;
    $totalPrice = $seatPrice * $totalSeats;

    // Database operations
    $db = new Database();
    $bookingId = $db->insertBooking($routeKey, $bus, $selectedSeats);
    $bookingSuccess = ($bookingId !== false);
    $db->close();

    // Create human-readable route name
    $routeName = ucfirst($from) . ' to ' . ucfirst($to);
    $busName = $bus == 'luxury' ? 'Luxury Coach' : ($bus == 'deluxe' ? 'Deluxe Bus' : 'Standard Bus');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(20, 20, 31, .7), rgba(20, 20, 31, .7)), url(bg-hero.jpg);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .booking-container {
            max-width: 800px;
            width: 90%;
            margin: 2rem auto;
        }
        .booking-info {
            font-size: 1.1rem;
            line-height: 2;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen py-8">
    <div class="booking-container">
        <div class="bg-white p-10 rounded-xl shadow-2xl border-2 border-green-500">
            <h1 class="text-3xl font-bold mb-8 text-green-600 text-center">Booking Confirmation</h1>
            <div class="booking-info space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p><span class="font-semibold text-green-700">From:</span> <?php echo htmlspecialchars(ucfirst($from)); ?></p>
                        <p><span class="font-semibold text-green-700">To:</span> <?php echo htmlspecialchars(ucfirst($to)); ?></p>
                        <p><span class="font-semibold text-green-700">Route:</span> <?php echo htmlspecialchars($routeName); ?></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p><span class="font-semibold text-green-700">Bus Type:</span> <?php echo htmlspecialchars($busName); ?></p>
                        <p><span class="font-semibold text-green-700">Seats:</span> <?php echo htmlspecialchars(implode(', ', $selectedSeatsArray)); ?></p>
                        <p><span class="font-semibold text-green-700">Total Seats:</span> <?php echo htmlspecialchars($totalSeats); ?></p>
                    </div>
                </div>

                <div class="bg-green-50 p-6 rounded-lg mt-6">
                    <h2 class="text-xl font-bold mb-4 text-green-700">Price Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <p><span class="font-semibold text-green-700">Base Price (Per Seat):</span> ₹<?php echo htmlspecialchars($basePrice); ?></p>
                        <p><span class="font-semibold text-green-700">Bus Type Multiplier:</span> ×<?php echo htmlspecialchars($busMultiplier); ?></p>
                    </div>
                    <div class="mt-4 pt-4 border-t-2 border-green-200">
                        <p class="text-lg"><span class="font-bold text-green-700">Final Price per Seat:</span> ₹<?php echo htmlspecialchars($seatPrice); ?></p>
                        <p class="text-xl mt-2"><span class="font-bold text-green-700">Total Price:</span> ₹<?php echo htmlspecialchars($totalPrice); ?></p>
                    </div>
                </div>

                <?php if ($bookingSuccess): ?>
                    <div class="bg-green-100 p-4 rounded-lg text-center">
                        <p class="text-green-700 font-semibold text-lg">Booking Confirmed! Please proceed to payment.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-red-100 p-4 rounded-lg text-center">
                        <p class="text-red-700 font-semibold text-lg">Booking Failed. Please try again.</p>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-8">
                    <?php if ($bookingSuccess): ?>
                        <a href="payment-gateway.php?totalPrice=<?php echo $totalPrice; ?>&from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&bus=<?php echo urlencode($bus); ?>&selectedSeats=<?php echo urlencode($selectedSeats); ?>&routeKey=<?php echo urlencode($routeKey); ?>&booking_id=<?php echo $bookingId; ?>" 
                           class="inline-block bg-green-500 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-600 transform hover:scale-105 transition-all duration-200">
                            Proceed to Payment
                        </a>
                    <?php else: ?>
                        <a href="index.php" 
                           class="inline-block bg-green-500 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-600 transform hover:scale-105 transition-all duration-200">
                            Back to Home
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
} else {
    header("Location: index.php");
    exit();
}
?>