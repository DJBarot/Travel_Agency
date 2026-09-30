<?php
require_once 'db_connection.php';

// Start session to store booking info
session_start();

// Check if request comes from booking page
if (isset($_GET['totalPrice']) && !empty($_GET['totalPrice'])) {
    $totalPrice = $_GET['totalPrice'];
    
    // Store other parameters passed from booking page
    $_SESSION['payment_data'] = [
        'from' => $_GET['from'] ?? '',
        'to' => $_GET['to'] ?? '',
        'bus' => $_GET['bus'] ?? '',
        'selectedSeats' => $_GET['selectedSeats'] ?? '',
        'routeKey' => $_GET['routeKey'] ?? '',
        'totalPrice' => $totalPrice,
        'booking_id' => $_GET['booking_id'] ?? 0
    ];
} elseif (isset($_SESSION['payment_data'])) {
    // Retrieve data from session if page is reloaded
    $totalPrice = $_SESSION['payment_data']['totalPrice'];
} else {
    // Redirect if no price data available
    header("Location: index.php");
    exit();
}

// Process payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? '';
    $cardNumber = $_POST['card_number'] ?? '';
    $cardName = $_POST['card_name'] ?? '';
    $expiryDate = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $upiId = $_POST['upi_id'] ?? '';
    $walletProvider = $_POST['wallet_provider'] ?? '';
    $walletNumber = $_POST['wallet_number'] ?? '';
    
    // Basic validation
    $errors = [];
    
    if (empty($paymentMethod)) {
        $errors[] = "Please select a payment method";
    }
    
    if ($paymentMethod == 'card') {
        if (empty($cardNumber) || strlen(preg_replace('/\D/', '', $cardNumber)) < 16) {
            $errors[] = "Please enter a valid card number";
        }
        
        if (empty($cardName)) {
            $errors[] = "Please enter the name on card";
        }
        
        if (empty($expiryDate) || !preg_match('/^\d{2}\/\d{2}$/', $expiryDate)) {
            $errors[] = "Please enter a valid expiry date (MM/YY)";
        }
        
        if (empty($cvv) || !preg_match('/^\d{3,4}$/', $cvv)) {
            $errors[] = "Please enter a valid CVV";
        }
    } elseif ($paymentMethod == 'upi') {
        if (empty($upiId) || !preg_match('/^[\w\.\-]+@[\w\-]+$/', $upiId)) {
            $errors[] = "Please enter a valid UPI ID";
        }
    } elseif ($paymentMethod == 'wallet') {
        if (empty($walletProvider)) {
            $errors[] = "Please select a wallet provider";
        }
        
        if (empty($walletNumber) || !preg_match('/^\d{10}$/', $walletNumber)) {
            $errors[] = "Please enter a valid mobile number linked to your wallet";
        }
    }
    
    // If no errors, process payment
    if (empty($errors)) {
        // Connect to database
        $db = new Database();
        
        // Generate transaction ID
        $transactionId = 'TXN' . time() . rand(1000, 9999);
        
        // Get booking ID from session
        $bookingId = $_SESSION['payment_data']['booking_id'];
        
        // Insert payment record
        $paymentInserted = $db->query(
            "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) 
             VALUES (?, ?, ?, ?, 'completed')",
            [$bookingId, $totalPrice, $paymentMethod, $transactionId]
        );
        
        // Update booking status
        $bookingUpdated = $db->query(
            "UPDATE bookings SET payment_status = 'completed' WHERE id = ?",
            [$bookingId]
        );
        
        $db->close();
        
        if ($paymentInserted && $bookingUpdated) {
            // Clear session data
            unset($_SESSION['payment_data']);
            
            // Redirect to success page
            header("Location: payment-success.php?txn=" . $transactionId);
            exit();
        } else {
            $errors[] = "Payment processing failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            background: linear-gradient(rgba(20, 20, 31, .7), rgba(20, 20, 31, .7)), url(bg-hero.jpg);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .payment-container {
            max-width: 900px;
            width: 90%;
            margin: 2rem auto;
        }
        input[type="text"], input[type="number"] {
            font-size: 1.1rem;
            padding: 0.75rem 1rem;
        }
        label {
            font-size: 1.1rem;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen py-8">
    <div class="payment-container">
        <div class="bg-white p-10 rounded-xl shadow-2xl border-2 border-green-500">
            <h1 class="text-3xl font-bold mb-8 text-green-600 text-center">Complete Your Payment</h1>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="bg-red-100 border-2 border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6">
                    <ul class="list-disc pl-5 space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="mb-8 p-6 bg-green-50 rounded-lg border border-green-200">
                <h2 class="text-xl font-bold mb-4 text-green-700">Order Summary</h2>
                <div class="grid grid-cols-2 gap-4 text-lg">
                    <p><span class="font-semibold text-green-700">Journey:</span><br>
                        <?php echo htmlspecialchars($_SESSION['payment_data']['from'] ?? ''); ?> to 
                        <?php echo htmlspecialchars($_SESSION['payment_data']['to'] ?? ''); ?>
                    </p>
                    <p><span class="font-semibold text-green-700">Bus Type:</span><br>
                        <?php echo htmlspecialchars($_SESSION['payment_data']['bus'] ?? ''); ?>
                    </p>
                    <p><span class="font-semibold text-green-700">Seats:</span><br>
                        <?php echo htmlspecialchars($_SESSION['payment_data']['selectedSeats'] ?? ''); ?>
                    </p>
                    <p class="text-xl font-bold text-green-700">Total Amount:<br>
                        ₹<?php echo htmlspecialchars($totalPrice); ?>
                    </p>
                </div>
            </div>
            
            <form method="POST" action="" class="space-y-6">
                <div class="mb-6">
                    <label class="block text-lg font-semibold mb-4">Select Payment Method</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-green-200 rounded-lg hover:bg-green-50 cursor-pointer">
                            <input type="radio" name="payment_method" value="card" class="form-radio h-5 w-5 text-green-600" checked>
                            <span class="ml-3 text-lg">Credit/Debit Card</span>
                        </label>
                        <label class="flex items-center p-4 border-2 border-green-200 rounded-lg hover:bg-green-50 cursor-pointer">
                            <input type="radio" name="payment_method" value="upi" class="form-radio h-5 w-5 text-green-600">
                            <span class="ml-3 text-lg">UPI</span>
                        </label>
                        <label class="flex items-center p-4 border-2 border-green-200 rounded-lg hover:bg-green-50 cursor-pointer">
                            <input type="radio" name="payment_method" value="wallet" class="form-radio h-5 w-5 text-green-600">
                            <span class="ml-3 text-lg">Wallet</span>
                        </label>
                    </div>
                </div>
                
                <div id="card-payment-fields" class="space-y-6">
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2" for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" 
                               class="w-full p-4 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2" for="card_name">Name on Card</label>
                        <input type="text" id="card_name" name="card_name" 
                               class="w-full p-4 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-lg font-semibold mb-2" for="expiry_date">Expiry Date</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" 
                                   class="w-full p-4 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-lg font-semibold mb-2" for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" 
                                   class="w-full p-4 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                        </div>
                    </div>
                </div>
                
                <div id="upi-payment-fields" class="hidden">
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2" for="upi_id">UPI ID</label>
                        <input type="text" id="upi_id" name="upi_id" placeholder="example@upi" 
                               class="w-full p-4 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                    </div>
                </div>
                
                <div id="wallet-payment-fields" class="hidden">
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2" for="wallet_provider">Select Wallet Provider</label>
                        <select id="wallet_provider" name="wallet_provider" 
                                class="w-full p-4 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                            <option value="">Select a provider</option>
                            <option value="paytm">Paytm</option>
                            <option value="phonepe">PhonePe</option>
                            <option value="googlepay">Google Pay</option>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-2" for="wallet_number">Mobile Number</label>
                        <input type="text" id="wallet_number" name="wallet_number" placeholder="10-digit mobile number" 
                               class="w-full p-4 border-2 border-green-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                    </div>
                </div>
                
                <div class="flex items-center justify-between mt-8 pt-6 border-t-2 border-green-100">
                    <a href="booking.php" 
                       class="text-green-600 hover:text-green-700 font-semibold text-lg">
                        ← Back to Booking
                    </a>
                    <button type="submit" 
                            class="bg-green-500 hover:bg-green-600 text-white text-lg font-bold py-4 px-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all">
                        Pay ₹<?php echo htmlspecialchars($totalPrice); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Toggle payment method fields
        document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                // Hide all payment method fields first
                document.getElementById('card-payment-fields').classList.add('hidden');
                document.getElementById('upi-payment-fields').classList.add('hidden');
                document.getElementById('wallet-payment-fields').classList.add('hidden');
                
                // Show the selected payment method fields
                if (this.value === 'card') {
                    document.getElementById('card-payment-fields').classList.remove('hidden');
                } else if (this.value === 'upi') {
                    document.getElementById('upi-payment-fields').classList.remove('hidden');
                } else if (this.value === 'wallet') {
                    document.getElementById('wallet-payment-fields').classList.remove('hidden');
                }
            });
        });
    </script>
</body>
</html>