<?php
// payment_and_booking_process.php
header('Content-Type: application/json');

// Get the booking data from the request
$requestData = json_decode(file_get_contents('php://input'), true);

// Connect to the database
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]));
}

// Validate incoming data
if (!isset($requestData['vehicleCategory']) || 
    !isset($requestData['vehicleModel']) || 
    !isset($requestData['pickupDate']) || 
    !isset($requestData['returnDate']) || 
    !isset($requestData['totalCost']) || 
    !isset($requestData['confirmationCode']) ||
    !isset($requestData['paymentDetails'])) {
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required booking data'
    ]);
    exit;
}

// Process payment (in a real application, you would integrate with a payment gateway API here)
// For this example, we'll simulate a successful payment

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Format additional services for database storage
    $additionalServices = 'None';
    if (!empty($requestData['selectedServices'])) {
        $additionalServices = implode(', ', $requestData['selectedServices']);
    }
    
    // Prepare SQL statement to insert booking data
    $stmt = $conn->prepare("INSERT INTO vehicle_bookings 
        (vehicle_category, vehicle_model, pickup_date, return_date, 
        additional_services, total_cost, confirmation_code, booking_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
    
    $stmt->bind_param("sssssds", 
        $requestData['vehicleCategory'],
        $requestData['vehicleModel'],
        $requestData['pickupDate'],
        $requestData['returnDate'],
        $additionalServices,
        $requestData['totalCost'],
        $requestData['confirmationCode']
    );
    
    // Execute the statement
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    // Get the booking ID
    $bookingId = $conn->insert_id;
    
    // Here you would typically also store payment details in a separate payments table
    // For simplicity, we're omitting that step in this example
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Booking confirmed successfully',
        'booking' => [
            'id' => $bookingId,
            'confirmationCode' => $requestData['confirmationCode'],
            'vehicleCategory' => $requestData['vehicleCategory'],
            'vehicleModel' => $requestData['vehicleModel'],
            'pickupDate' => $requestData['pickupDate'],
            'returnDate' => $requestData['returnDate'],
            'selectedServices' => $additionalServices,
            'totalCost' => $requestData['totalCost'],
            'paymentStatus' => 'completed'
        ]
    ]);
    
} catch (Exception $e) {
    // Roll back the transaction in case of error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Payment processing failed: ' . $e->getMessage()
    ]);
} finally {
    // Close connection
    $conn->close();
}
?>
