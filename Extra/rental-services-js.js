document.addEventListener('DOMContentLoaded', () => {
    const rentalForm = document.getElementById('rental-booking-form');
    const vehicleTypeSelect = document.getElementById('vehicle-type');
    const pickupDateInput = document.getElementById('pickup-date');
    const returnDateInput = document.getElementById('return-date');

    // Pricing Matrix (example rates)
    const VEHICLE_RATES = {
        car: {
            daily: 50,
            weekly: 300,
            monthly: 1200
        },
        bike: {
            daily: 20,
            weekly: 120,
            monthly: 500
        },
        scooty: {
            daily: 15,
            weekly: 90,
            monthly: 350
        },
        minivan: {
            daily: 80,
            weekly: 480,
            monthly: 1900
        }
    };

    rentalForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const vehicleType = vehicleTypeSelect.value;
        const rentalDuration = document.getElementById('rental-duration').value;
        const pickupDate = new Date(pickupDateInput.value);
        const returnDate = new Date(returnDateInput.value);

        // Calculate rental duration in days
        const timeDiff = returnDate - pickupDate;
        const days = Math.ceil(timeDiff / (1000 * 3600 * 24));

        if (days <= 0) {
            alert('Invalid date range. Return date must be after pickup date.');
            return;
        }

        // Determine rental category
        let rentalCategory;
        if (days <= 1) rentalCategory = 'daily';
        else if (days <= 7) rentalCategory = 'weekly';
        else rentalCategory = 'monthly';

        const rate = VEHICLE_RATES[vehicleType][rentalCategory];
        const totalCost = rate * (rentalCategory === 'daily' ? days : 1);

        // Display availability and cost
        alert(`
            Vehicle: ${vehicleType.toUpperCase()}
            Duration: ${days} day(s)
            Category: ${rentalCategory}
            Total Cost: $${totalCost}
            Status: Available
        `);
    });
});
