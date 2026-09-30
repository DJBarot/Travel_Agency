-- Create Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route VARCHAR(100) NOT NULL,
    bus VARCHAR(100) NOT NULL,
    seats INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    booking_reference VARCHAR(20) UNIQUE
);

-- Create Routes Table
CREATE TABLE routes (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Insert Sample Routes
INSERT INTO routes (id, name) VALUES 
('vadodara-mumbai', 'Vadodara to Mumbai'),
('mumbai-vadodara', 'Mumbai to Vadodara'),
('vadodara-ahmedabad', 'Vadodara to Ahmedabad'),
('ahmedabad-vadodara', 'Ahmedabad to Vadodara'),
('vadodara-surat', 'Vadodara to Surat'),
('surat-vadodara', 'Surat to Vadodara'),
('ahmedabad-rajkot', 'Ahmedabad to Rajkot'),
('rajkot-ahmedabad', 'Rajkot to Ahmedabad'),
('surat-bhavnagar', 'Surat to Bhavnagar'),
('bhavnagar-surat', 'Bhavnagar to Surat'),
('rajkot-jamnagar', 'Rajkot to Jamnagar'),
('jamnagar-rajkot', 'Jamnagar to Rajkot'),
('bhavnagar-junagadh', 'Bhavnagar to Junagadh'),
('junagadh-bhavnagar', 'Junagadh to Bhavnagar'),
('junagadh-porbandar', 'Junagadh to Porbandar'),
('porbandar-junagadh', 'Porbandar to Junagadh'),
('porbandar-dwarka', 'Porbandar to Dwarka'),
('dwarka-porbandar', 'Dwarka to Porbandar'),
('dwarka-gandhidham', 'Dwarka to Gandhidham'),
('gandhidham-dwarka', 'Gandhidham to Dwarka');

-- Create Buses Table
CREATE TABLE buses (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    total_seats INT NOT NULL
);

-- Insert Sample Buses
INSERT INTO buses (id, name, total_seats) VALUES 
('luxury', 'Luxury Coach', 40),
('deluxe', 'Deluxe Bus', 32),
('standard', 'Standard Bus', 25);

-- Create Payments Table (new)
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);