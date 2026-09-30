<?php
require_once 'db_connection.php';

// Check if transaction ID is provided
if (isset($_GET['txn']) && !empty($_GET['txn'])) {
    $transactionId = $_GET['txn'];
    
    // Get payment details
    $db = new Database();
    $paymentData = $db->getRow(
        "SELECT p.*, b.route, b.bus, b.seats, b.booking_reference 
         FROM payments p 
         JOIN bookings b ON p.booking_id = b.id 
         WHERE p.transaction_id = ?",
        [$transactionId]
    );
    $db->close();
    
    if (!$paymentData) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
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
        .success-container {
            max-width: 800px;
            width: 90%;
            margin: 2rem auto;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen py-8">
    <div class="success-container">
        <div class="bg-white p-10 rounded-xl shadow-2xl border-2 border-green-500">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-green-600 mb-2">Payment Successful!</h1>
                <p class="text-lg text-gray-600">Your booking has been confirmed.</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-green-700 mb-4">Booking Details</h2>
                <div class="space-y-4 text-lg">
                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Booking Reference:</span>
                        <span class="font-semibold text-green-700"><?php echo htmlspecialchars($paymentData['booking_reference']); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Transaction ID:</span>
                        <span class="font-semibold text-green-700"><?php echo htmlspecialchars($transactionId); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Amount Paid:</span>
                        <span class="font-semibold text-green-700">₹<?php echo htmlspecialchars($paymentData['amount']); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="font-semibold text-green-700"><?php echo htmlspecialchars(ucfirst($paymentData['payment_method'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Date:</span>
                        <span class="font-semibold text-green-700"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($paymentData['payment_date']))); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-green-700 mb-4">Journey Details</h2>
                <div class="space-y-4 text-lg">
                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Route:</span>
                        <span class="font-semibold text-green-700"><?php echo htmlspecialchars($paymentData['route']); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Bus Type:</span>
                        <span class="font-semibold text-green-700"><?php echo htmlspecialchars(ucfirst($paymentData['bus'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Seats:</span>
                        <span class="font-semibold text-green-700"><?php echo htmlspecialchars($paymentData['seats']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="index.php" 
                   class="inline-block bg-green-500 hover:bg-green-600 text-white text-lg font-bold py-4 px-8 rounded-lg transform hover:scale-105 transition-all duration-200">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
