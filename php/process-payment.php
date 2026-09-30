<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');

ob_start();
error_reporting(0);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'package');

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
    $requiredFields = ['booking_id', 'amount', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $bookingId = $conn->real_escape_string($input['booking_id']);
    $amount = floatval($input['amount']);
    $paymentMethod = $conn->real_escape_string($input['payment_method']);
    $transactionId = 'TXN' . time() . rand(1000, 9999);

    // Start transaction
    $conn->begin_transaction();

    // Insert payment record
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(20, 20, 31, .7), rgba(20, 20, 31, .7)), url(bg-hero.jpg);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .payment-container {
            max-width: 800px;
            width: 100%;
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid #38a169;
        }
        .payment-info {
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .payment-summary {
            background: #f0fff4;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .payment-summary p {
            margin-bottom: 0.5rem;
        }
        .payment-summary .font-semibold {
            color: #2f855a;
        }
        .payment-summary .font-bold {
            color: #2f855a;
        }
        .payment-summary .text-xl {
            font-size: 1.25rem;
        }
        .hidden {
            display: none !important;
        }
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
        }
        .modal-content svg {
            margin-bottom: 1rem;
        }
        .modal-content p {
            margin-bottom: 1rem;
        }
        .error-container {
            background: #fff5f5;
            border-left: 4px solid #f56565;
            color: #c53030;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        .error-container p {
            font-weight: bold;
        }
        .error-container ul {
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h1 class="text-3xl font-bold mb-8 text-green-600 text-center">Complete Your Payment</h1>
        
        <!-- Booking Summary -->
        <div id="booking-summary" class="payment-summary">
            <h2 class="text-xl font-bold mb-4 text-green-700">Booking Summary</h2>
            <p><span class="font-semibold">Name:</span> <?php echo htmlspecialchars($_POST['name']); ?></p>
            <p><span class="font-semibold">Email:</span> <?php echo htmlspecialchars($_POST['email']); ?></p>
            <p><span class="font-semibold">Phone:</span> <?php echo htmlspecialchars($_POST['phone']); ?></p>
            <p><span class="font-semibold">Hotel:</span> <?php echo htmlspecialchars($_POST['hotel']); ?></p>
            <p><span class="font-semibold">Package ID:</span> <?php echo htmlspecialchars($_POST['package_id']); ?></p>
            <p class="text-xl mt-4"><span class="font-bold">Total Price:</span> ₹<?php echo number_format(htmlspecialchars($_POST['price']), 2); ?></p>
        </div>

        <!-- Payment Form -->
        <form id="payment-form" class="space-y-6">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($_POST['booking_id']); ?>">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($_POST['price']); ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select name="payment_method" id="payment-method" class="date-picker">
                    <option value="card">Credit/Debit Card</option>
                    <option value="upi">UPI</option>
                    <option value="wallet">Wallet</option>
                </select>
            </div>

            <!-- Card Payment Fields -->
            <div id="card-payment-fields" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                    <input type="text" name="card_number" class="date-picker" placeholder="1234 5678 9012 3456">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Holder Name</label>
                    <input type="text" name="card_name" class="date-picker" placeholder="John Doe">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="text" name="expiry_date" class="date-picker" placeholder="MM/YY">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                        <input type="text" name="cvv" class="date-picker" placeholder="123">
                    </div>
                </div>
            </div>

            <!-- UPI Payment Fields -->
            <div id="upi-payment-fields" class="space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UPI ID</label>
                    <input type="text" name="upi_id" class="date-picker" placeholder="example@upi">
                </div>
            </div>

            <!-- Wallet Payment Fields -->
            <div id="wallet-payment-fields" class="space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Wallet Type</label>
                    <select name="wallet_type" class="date-picker">
                        <option value="paytm">Paytm</option>
                        <option value="phonepe">PhonePe</option>
                        <option value="googlepay">Google Pay</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                    <input type="text" name="wallet_mobile" class="date-picker" placeholder="9876543210">
                </div>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg text-lg font-semibold hover:bg-green-700 transform hover:scale-105 transition-all duration-200">
                Pay ₹<span id="pay-button-amount"><?php echo number_format(htmlspecialchars($_POST['price']), 2); ?></span>
            </button>
        </form>

        <!-- Payment Processing Modal -->
        <div id="payment-processing-modal" class="modal hidden">
            <div class="modal-content">
                <svg class="animate-spin h-10 w-10 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4m-8 0H4m16 0a8 8 0 11-16 0 8 8 0 0116 0z"></path>
                </svg>
                <p class="text-lg font-semibold text-green-700">Processing your payment...</p>
            </div>
        </div>

        <!-- Payment Success Modal -->
        <div id="payment-success-modal" class="modal hidden">
            <div class="modal-content">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h3 class="text-2xl font-bold text-green-700 mb-4">Payment Successful!</h3>
                <p class="text-green-700 mb-2">Thank you for your payment.</p>
                <p class="text-green-700 font-semibold text-lg mb-4">Transaction ID: <span id="transaction-id" class="font-bold"></span></p>
            </div>
        </div>

        <!-- Error Container -->
        <div id="error-container" class="error-container hidden">
            <p class="font-bold">Error</p>
            <ul id="error-list"></ul>
        </div>
    </div>

    <script>
        // Make sure modals are hidden before any other JavaScript runs
        window.onload = function() {
            document.getElementById('payment-processing-modal').classList.add('hidden');
            document.getElementById('payment-success-modal').classList.add('hidden');
            document.getElementById('error-container').classList.add('hidden');
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Force hide modals again to ensure they're hidden
            document.getElementById('payment-processing-modal').classList.add('hidden');
            document.getElementById('payment-success-modal').classList.add('hidden');
            document.getElementById('error-container').classList.add('hidden');

            const paymentMethodSelect = document.getElementById('payment-method');
            const cardFields = document.getElementById('card-payment-fields');
            const upiFields = document.getElementById('upi-payment-fields');
            const walletFields = document.getElementById('wallet-payment-fields');

            paymentMethodSelect.addEventListener('change', () => {
                cardFields.classList.add('hidden');
                upiFields.classList.add('hidden');
                walletFields.classList.add('hidden');

                switch (paymentMethodSelect.value) {
                    case 'card':
                        cardFields.classList.remove('hidden');
                        break;
                    case 'upi':
                        upiFields.classList.remove('hidden');
                        break;
                    case 'wallet':
                        walletFields.classList.remove('hidden');
                        break;
                }
            });

            // Handle form submission
            document.getElementById('payment-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Make sure other modals are hidden before showing processing modal
                document.getElementById('payment-success-modal').classList.add('hidden');
                document.getElementById('error-container').classList.add('hidden');
                document.getElementById('payment-processing-modal').classList.remove('hidden');
                
                try {
                    const formData = new FormData(e.target);
                    const paymentData = {
                        booking_id: formData.get('booking_id'),
                        amount: formData.get('amount'),
                        payment_method: formData.get('payment_method'),
                        card_number: formData.get('card_number'),
                        card_name: formData.get('card_name'),
                        expiry_date: formData.get('expiry_date'),
                        cvv: formData.get('cvv'),
                        upi_id: formData.get('upi_id'),
                        wallet_type: formData.get('wallet_type'),
                        wallet_mobile: formData.get('wallet_mobile')
                    };

                    const response = await fetch('process-payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(paymentData)
                    });

                    if (!response.ok) {
                        throw new Error(`Payment failed with status: ${response.status}`);
                    }

                    const result = await response.json();
                    
                    if (result.success) {
                        document.getElementById('transaction-id').textContent = result.data.transaction_id;
                        document.getElementById('payment-processing-modal').classList.add('hidden');
                        document.getElementById('payment-success-modal').classList.remove('hidden');
                        
                        setTimeout(() => {
                            window.location.href = `booking-success.html?transaction_id=${result.data.transaction_id}`;
                        }, 2000);
                    } else {
                        throw new Error(result.message || 'Payment failed');
                    }
                } catch (error) {
                    document.getElementById('payment-processing-modal').classList.add('hidden');
                    document.getElementById('error-container').classList.remove('hidden');
                    document.getElementById('error-list').innerHTML = `<li>${error.message}</li>`;
                }
            });
        });
    </script>
</body>
</html>