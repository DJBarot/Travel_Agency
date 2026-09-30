-- Create the new database
CREATE DATABASE IF NOT EXISTS sr;

-- Use the newly created database
USE sr;

-- Main booking table (updated to include payment fields)
CREATE TABLE vehicle_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(50) UNIQUE NOT NULL,
    vehicle_category VARCHAR(50) NOT NULL,
    vehicle_model VARCHAR(100) NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    duration INT NOT NULL,
    additional_services TEXT DEFAULT 'None',
    total_cost DECIMAL(10,2) NOT NULL CHECK (total_cost > 0),
    confirmation_code VARCHAR(50) NOT NULL,
    booking_status ENUM('Pending', 'Confirmed', 'Cancelled') DEFAULT 'Pending',
    payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
    payment_method VARCHAR(20) NULL,
    payment_details TEXT NULL,
    transaction_id VARCHAR(50) NULL,
    payment_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Separate payments table to store payment history
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(20) NOT NULL,
    transaction_id VARCHAR(50) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES vehicle_bookings(booking_id)
);

-- Vehicle Models Table
CREATE TABLE vehicle_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    model_name VARCHAR(100) NOT NULL,
    daily_rate DECIMAL(6,2) NOT NULL,
    image_path VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Additional Services Table
CREATE TABLE additional_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    daily_rate DECIMAL(5,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Sample Data Insertion
INSERT INTO vehicle_models (category, model_name, daily_rate, image_path) VALUES
('Car', 'Toyota Camry', 3500.00, 'images/toyota-camry.jpg'),
('Car', 'Honda Civic', 3000.00, 'images/honda-civic.jpg'),
('Car', 'Maruti Swift', 2000.00, 'images/maruti-swift.jpg'),
('Bike', 'Yamaha R15', 1200.00, 'images/yamaha-r15.jpg'),
('Bike', 'Royal Enfield Classic 350', 1500.00, 'images/royal-enfield.jpg'),
('Scooty', 'Honda Activa', 800.00, 'images/honda-activa.jpg'),
('Scooty', 'TVS Jupiter', 750.00, 'images/tvs-jupiter.jpg');

INSERT INTO additional_services (service_name, description, daily_rate) VALUES
('Insurance Protection', 'Comprehensive insurance coverage for your journey', 300.00),
('GPS Navigation', 'Built-in GPS system for easy navigation', 150.00),
('Child Seat', 'Safety seat for children', 200.00),
('Helmet', 'Safety helmet for bike/scooty riders', 100.00),
('Roadside Assistance', '24/7 roadside assistance during your rental period', 250.00);

-- Indexes
CREATE INDEX idx_booking_id ON vehicle_bookings(booking_id);
CREATE INDEX idx_payment_status ON vehicle_bookings(payment_status);
CREATE INDEX idx_transaction_id ON payments(transaction_id);
