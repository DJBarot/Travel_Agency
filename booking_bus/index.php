<?php
$routes = [
    'vadodara-mumbai' => 'Vadodara to Mumbai',
    'mumbai-vadodara' => 'Mumbai to Vadodara',
    'vadodara-ahmedabad' => 'Vadodara to Ahmedabad',
    'ahmedabad-vadodara' => 'Ahmedabad to Vadodara',
    'vadodara-surat' => 'Vadodara to Surat',
    'surat-vadodara' => 'Surat to Vadodara',
    'ahmedabad-rajkot' => 'Ahmedabad to Rajkot',
    'rajkot-ahmedabad' => 'Rajkot to Ahmedabad',
    'surat-bhavnagar' => 'Surat to Bhavnagar',
    'bhavnagar-surat' => 'Bhavnagar to Surat',
    'rajkot-jamnagar' => 'Rajkot to Jamnagar',
    'jamnagar-rajkot' => 'Jamnagar to Rajkot',
    'bhavnagar-junagadh' => 'Bhavnagar to Junagadh',
    'junagadh-bhavnagar' => 'Junagadh to Bhavnagar',
    'junagadh-porbandar' => 'Junagadh to Porbandar',
    'porbandar-junagadh' => 'Porbandar to Junagadh',
    'porbandar-dwarka' => 'Porbandar to Dwarka',
    'dwarka-porbandar' => 'Dwarka to Porbandar',
    'dwarka-gandhidham' => 'Dwarka to Gandhidham',
    'gandhidham-dwarka' => 'Gandhidham to Dwarka',
    // Add the missing route
    'junagadh-surat' => 'Junagadh to Surat',
    'surat-junagadh' => 'Surat to Junagadh'
];

$locations = array_unique(array_merge(array_map(function($route) {
    return explode('-', $route)[0];
}, array_keys($routes)), array_map(function($route) {
    return explode('-', $route)[1];
}, array_keys($routes))));
sort($locations);

$buses = [
    'luxury' => 'Luxury Coach', 
    'deluxe' => 'Deluxe Bus',
    'standard' => 'Standard Bus'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bus Booking System</title>
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
        .bus-seats {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;  /* Increased gap */
            margin-top: 30px;  /* Increased margin */
            padding: 20px;  /* Added padding */
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
        }
        .seat {
            width: 50px;  /* Increased size */
            height: 50px;  /* Increased size */
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 8px;  /* Slightly rounded corners */
            border: 2px solid #22c55e;
            color: #22c55e;
            font-size: 1.1rem;  /* Larger font */
            font-weight: 600;  /* Bolder text */
            transition: all 0.3s ease;  /* Smooth transitions */
        }
        .selected {
            background: #22c55e;
            color: white;
            border: 2px solid #15803d;
            transform: scale(1.05);  /* Slight grow effect */
        }
        .booked {
            background: #dc2626;
            color: white;
            cursor: not-allowed;
            border: 2px solid #991b1b;
            opacity: 0.8;
        }
        .hidden {
            display: none;
        }
        /* New styles for form elements */
        select, button {
            font-size: 1.1rem !important;
            padding: 0.75rem 1rem !important;
        }
        label {
            font-size: 1.1rem;
            font-weight: 600;
            color: #22c55e;
        }
    </style>
</head>
<body class="bg-white">
    <div class="container mx-auto p-8"> <!-- Increased padding -->
        <div class="max-w-2xl mx-auto bg-white p-10 rounded-lg shadow-lg border-2 border-green-500"> <!-- Increased max-width and padding -->
            <h1 class="text-3xl font-bold mb-8 text-center text-green-600">Bus Booking</h1> <!-- Larger heading -->
            
            <form action="booking.php" method="post" class="space-y-6"> <!-- Added vertical spacing -->
                <div class="mb-6"> <!-- Increased margin -->
                    <label class="block mb-3 text-lg">From</label> <!-- Larger label -->
                    <select name="from" id="from" class="w-full p-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all" required>
                        <option value="">Choose Starting Point</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location); ?>">
                                <?php echo htmlspecialchars(ucfirst($location)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block mb-3 text-lg">To</label>
                    <select name="to" id="to" class="w-full p-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all" required>
                        <option value="">Choose Destination</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location); ?>">
                                <?php echo htmlspecialchars(ucfirst($location)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block mb-3 text-lg">Select Bus</label>
                    <select name="bus" id="bus" class="w-full p-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all" required>
                        <option value="">Select Bus</option>
                        <?php foreach ($buses as $key => $bus): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>">
                                <?php echo htmlspecialchars($bus); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="seat-selection" class="mb-6 hidden">
                    <label class="block mb-3 text-lg">Select Your Seats</label>
                    <div class="bus-seats" id="bus-seats"></div>
                    <input type="hidden" name="selectedSeats" id="selectedSeats" required>
                </div>

                <button type="submit" 
                        class="w-full bg-green-500 text-white p-4 rounded-lg text-lg font-semibold hover:bg-green-600 transform hover:scale-105 transition-all duration-200">
                    Book Seats
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fromSelect = document.getElementById("from");
            const toSelect = document.getElementById("to");
            const busSelect = document.getElementById("bus");
            const seatSelection = document.getElementById("seat-selection");
            const busSeats = document.getElementById("bus-seats");
            const selectedSeatsInput = document.getElementById("selectedSeats");
            const totalSeats = 12; // 2 rows x 6 columns
            let selectedSeats = [];
            let bookedSeats = [];

            function updateSeatSelection() {
                if (fromSelect.value && toSelect.value && busSelect.value) {
                    seatSelection.classList.remove("hidden");
                    resetSeats();
                } else {
                    seatSelection.classList.add("hidden");
                }
            }

            function resetSeats() {
                selectedSeats = [];
                selectedSeatsInput.value = '';
                busSeats.innerHTML = '';

                bookedSeats = getRandomBookedSeats(totalSeats, 3); // Randomly book 3 seats

                for (let i = 1; i <= totalSeats; i++) {
                    let seat = document.createElement("div");
                    seat.classList.add("seat");
                    seat.textContent = i;
                    seat.dataset.seatNumber = i;

                    if (bookedSeats.includes(i)) {
                        seat.classList.add("booked");
                    } else {
                        seat.addEventListener("click", function () {
                            if (selectedSeats.includes(i)) {
                                selectedSeats = selectedSeats.filter(s => s !== i);
                                seat.classList.remove("selected");
                            } else {
                                selectedSeats.push(i);
                                seat.classList.add("selected");
                            }
                            selectedSeatsInput.value = selectedSeats.join(",");
                        });
                    }

                    busSeats.appendChild(seat);
                }
            }

            function getRandomBookedSeats(totalSeats, bookedCount) {
                const bookedSeats = new Set();
                while (bookedSeats.size < bookedCount) {
                    const seatNumber = Math.floor(Math.random() * totalSeats) + 1;
                    bookedSeats.add(seatNumber);
                }
                return Array.from(bookedSeats);
            }

            fromSelect.addEventListener("change", updateSeatSelection);
            toSelect.addEventListener("change", updateSeatSelection);
            busSelect.addEventListener("change", updateSeatSelection);
        });
    </script>
</body>
</html>