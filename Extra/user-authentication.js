const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { Pool } = require('pg');

class AuthenticationService {
    constructor() {
        this.pool = new Pool({
            // Database connection details
            host: 'localhost',
            user: 'your_username',
            password: 'your_password',
            database: 'travel_agency'
        });
    }

    async registerUser(userData) {
        const { 
            full_name, 
            email, 
            password, 
            phone_number, 
            driving_license 
        } = userData;

        // Hash password
        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(password, salt);

        const query = `
            INSERT INTO users 
            (full_name, email, password, phone_number, driving_license, registration_date) 
            VALUES ($1, $2, $3, $4, $5, NOW()) 
            RETURNING user_id
        `;

        const values = [
            full_name, 
            email, 
            hashedPassword, 
            phone_number, 
            driving_license
        ];

        try {
            const result = await this.pool.query(query, values);
            return result.rows[0].user_id;
        } catch (error) {
            throw new Error('Registration failed');
        }
    }

    async authenticateUser(email, password) {
        const query = 'SELECT * FROM users WHERE email = $1';
        
        try {
            const result = await this.pool.query(query, [email]);
            
            if (result.rows.length === 0) {
                throw new Error('User not found');
            }

            const user = result.rows[0];
            const isMatch = await bcrypt.compare(password, user.password);

            if (!isMatch) {
                throw new Error('Invalid credentials');
            }

            // Generate JWT token
            const token = jwt.sign(
                { 
                    userId: user.user_id, 
                    email: user.email 
                },
                process.env.JWT_SECRET,
                { expiresIn: '24h' }
            );

            return { token, user: { id: user.user_id, email: user.email } };
        } catch (error) {
            throw error;
        }
    }
}

module.exports = new AuthenticationService();
