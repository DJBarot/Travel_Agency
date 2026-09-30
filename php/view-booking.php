<?php
// Connect to MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$database = "travel_agency"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all bookings
$sql = "SELECT * FROM bookings";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Link to external CSS -->
    <link rel="stylesheet" href="css/view-booking.css">
</head>
<body>

<div class="container">
    <h1>All Bookings</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Package</th>
            <th>Hotel</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['package_id']}</td>
                        <td>{$row['hotel']}</td>
                        <td><a href='delete-booking.php?id={$row['id']}' class='delete-btn'>Delete</a></td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No bookings found</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>
