<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');

ob_start();
error_reporting(0);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'sr');

if ($conn->connect_error) {
    ob_clean();
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]));
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $requiredFields = ['booking_id', 'amount', 'payment_method', 'vehicle_type', 'vehicle_model', 'pickup_date', 'return_date', 'duration', 'selected_services'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $bookingId = $conn->real_escape_string($input['booking_id']);
    $amount = floatval($input['amount']);
    $paymentMethod = $conn->real_escape_string($input['payment_method']);
    $vehicleType = $conn->real_escape_string($input['vehicle_type']);
    $vehicleModel = $conn->real_escape_string($input['vehicle_model']);
    $pickupDate = $conn->real_escape_string($input['pickup_date']);
    $returnDate = $conn->real_escape_string($input['return_date']);
    $duration = intval($input['duration']);
    $services = json_encode($input['selected_services']);

    // Start transaction
    $conn->begin_transaction();

    // Insert booking record
    $insertBookingSql = "INSERT INTO vehicle_bookings (
        booking_id, 
        vehicle_category, 
        vehicle_model, 
        pickup_date, 
        return_date, 
        duration, 
        additional_services,
        total_cost, 
        payment_method,
        payment_status,
        booking_status,
        confirmation_code
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', 'Confirmed', ?)";

    $stmt = $conn->prepare($insertBookingSql);
    $stmt->bind_param('sssssissss', 
        $bookingId,
        $vehicleType,
        $vehicleModel,
        $pickupDate,
        $returnDate,
        $duration,
        $services,
        $amount,
        $paymentMethod,
        $bookingId
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create booking');
    }

    // Create payment record
    $transactionId = 'TXN' . time() . rand(1000, 9999);
    $paymentSql = "INSERT INTO payments (
        booking_id, 
        amount, 
        payment_method, 
        transaction_id, 
        status
    ) VALUES (?, ?, ?, ?, 'completed')";

    $paymentStmt = $conn->prepare($paymentSql);
    $paymentStmt->bind_param('sdss', 
        $bookingId,
        $amount,
        $paymentMethod,
        $transactionId
    );

    if (!$paymentStmt->execute()) {
        throw new Exception('Failed to record payment');
    }

    // Commit transaction
    $conn->commit();

    // Send success response
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => [
            'transaction_id' => $transactionId,
            'booking_id' => $bookingId,
            'amount' => $amount
        ],
        'message' => 'Payment processed successfully'
    ]);

} catch (Exception $e) {
    if ($conn->connect_error === false) {
        $conn->rollback();
    }
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
ob_end_flush();
exit;