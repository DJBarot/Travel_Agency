<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotel_category = mysqli_real_escape_string($conn, $_POST['hotel_category']);
    $package_id = mysqli_real_escape_string($conn, $_POST['package_id']);

    // Get the destination associated with the package_id
    $package_query = "SELECT destination FROM packages WHERE package_id = ?";
    $package_stmt = $conn->prepare($package_query);
    $package_stmt->bind_param("i", $package_id);
    $package_stmt->execute();
    $package_result = $package_stmt->get_result();

    if ($package_result->num_rows > 0) {
        $package_row = $package_result->fetch_assoc();
        $destination = $package_row['destination'];

        // Fetch hotels based on location (destination) and hotel category
        $hotel_query = "SELECT hotel_id, hotel_name, location FROM hotels WHERE location = ? AND hotel_category = ?";
        $hotel_stmt = $conn->prepare($hotel_query);
        $hotel_stmt->bind_param("ss", $destination, $hotel_category);
        $hotel_stmt->execute();
        $hotel_result = $hotel_stmt->get_result();

        if ($hotel_result->num_rows > 0) {
            $hotels = [];
            while ($hotel = $hotel_result->fetch_assoc()) {
                $hotels[] = [
                    'hotel_id' => $hotel['hotel_id'],
                    'hotel_name' => $hotel['hotel_name'],
                    'hotel_location' => $hotel['location']
                ];
            }

            echo json_encode(['success' => true, 'hotels' => $hotels]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No hotels found for this category.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Package not found.']);
    }

    $package_stmt->close();
    $hotel_stmt->close();
    $conn->close();
}
?>
