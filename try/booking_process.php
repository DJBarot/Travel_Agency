<?php
// Database configuration
$DB_CONFIG = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'sr'
];

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Establish database connection
function connectDatabase($config) {
    $conn = new mysqli(
        $config['host'], 
        $config['username'], 
        $config['password'], 
        $config['database']
    );
    
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Database connection failed: " . $conn->connect_error
        ]);
        exit;
    }
    
    return $conn;
}

// Process booking
function processBooking($conn, $data) {
    // Validate required fields
    $requiredFields = [
        'vehicleCategory', 
        'vehicleModel', 
        'pickupDate', 
        'returnDate', 
        'totalCost'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Missing required field: $field"
            ]);
            exit;
        }
    }

    // Sanitize and prepare data
    $vehicleCategory = $conn->real_escape_string($data['vehicleCategory']);
    $vehicleModel = $conn->real_escape_string($data['vehicleModel']);
    $pickupDate = $conn->real_escape_string($data['pickupDate']);
    $returnDate = $conn->real_escape_string($data['returnDate']);
    $totalCost = floatval($data['totalCost']);
    
    // Handle additional services
    $services = isset($data['selectedServices']) 
        ? $conn->real_escape_string(implode(", ", $data['selectedServices'])) 
        : "None";

    // Generate unique confirmation code
    $confirmationCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

    // Prepare SQL statement
    $sql = "INSERT INTO vehicle_bookings (
        vehicle_category, 
        vehicle_model, 
        pickup_date, 
        return_date, 
        additional_services, 
        total_cost, 
        confirmation_code
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Prepare statement failed: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "sssssds", 
        $vehicleCategory, 
        $vehicleModel, 
        $pickupDate, 
        $returnDate, 
        $services, 
        $totalCost, 
        $confirmationCode
    );

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Booking failed: " . $stmt->error
        ]);
        exit;
    }

    // Return successful booking response
    echo json_encode([
        "status" => "success", 
        "booking" => [
            "confirmationCode" => $confirmationCode,
            "vehicleCategory" => $vehicleCategory,
            "vehicleModel" => $vehicleModel,
            "pickupDate" => $pickupDate,
            "returnDate" => $returnDate,
            "selectedServices" => $services,
            "totalCost" => number_format($totalCost, 2)
        ]
    ]);

    $stmt->close();
}

// Main execution
try {
    // Receive JSON input
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid JSON input"
        ]);
        exit;
    }

    // Connect to database and process booking
    $conn = connectDatabase($DB_CONFIG);
    processBooking($conn, $data);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Booking process failed: " . $e->getMessage()
    ]);
}
?>