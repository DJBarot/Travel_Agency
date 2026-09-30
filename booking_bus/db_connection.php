<?php
class Database {
    private $conn;
    
    public function __construct() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "bus_booking";
        
        try {
            $this->conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    public function getRow($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getRows($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insertBooking($route, $bus, $seats) {
        // Generate a unique booking reference
        $bookingRef = 'BK' . time() . rand(1000, 9999);
        
        try {
            $sql = "INSERT INTO bookings (route, bus, seats, booking_reference) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$route, $bus, $seats, $bookingRef]);
            
            if ($result) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch(PDOException $e) {
            // Log error
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    public function close() {
        $this->conn = null;
    }
}