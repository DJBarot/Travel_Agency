document.addEventListener("DOMContentLoaded", () => {
    const totalCostElement = document.getElementById("total-cost");
    const confirmBookingBtn = document.getElementById("confirm-booking-btn");
    const calculateCostBtn = document.getElementById("calculate-cost-btn");
    const vehicleModelDropdown = document.getElementById("vehicle-model");
    const modelImage = document.getElementById("model-image");
    const modelName = document.getElementById("model-name");
    const summaryDetails = document.getElementById("summary-details");

    const VEHICLE_MODELS = {
        car: { "Toyota Camry": "images/camry.jpg", "Honda Civic": "images/civic.jpg" },
        bike: { "Yamaha R15": "images/r15.jpg", "Royal Enfield": "images/enfield.jpg" },
        scooty: { "Honda Activa": "images/activa.jpg", "TVS Jupiter": "images/jupiter.jpg" },
        minivan: { "Toyota Sienna": "images/sienna.jpg", "Honda Odyssey": "images/odyssey.jpg" }
    };

    const VEHICLE_RATES = { car: 50, bike: 20, scooty: 15, minivan: 80 };
    const ADDITIONAL_SERVICES = { insurance: 20, gps: 10, "child-seat": 15 };

    // Event listener for category selection
    document.querySelectorAll('input[name="vehicle-category"]').forEach(input => {
        input.addEventListener("change", (e) => {
            updateVehicleModels(e.target.value);
        });
    });

    function updateVehicleModels(category) {
        vehicleModelDropdown.innerHTML = `<option value="">Choose a model</option>`;
        Object.keys(VEHICLE_MODELS[category]).forEach(model => {
            const option = document.createElement("option");
            option.value = model;
            option.textContent = model;
            vehicleModelDropdown.appendChild(option);
        });

        vehicleModelDropdown.addEventListener("change", function () {
            displayModelDetails(category, this.value);
        });
    }

    function displayModelDetails(category, model) {
        if (model && VEHICLE_MODELS[category][model]) {
            modelImage.src = VEHICLE_MODELS[category][model];
            modelImage.style.display = "block";
            modelName.textContent = model;
        } else {
            modelImage.style.display = "none";
            modelName.textContent = "";
        }
    }

    calculateCostBtn.addEventListener("click", () => {
        const vehicleCategory = document.querySelector('input[name="vehicle-category"]:checked')?.value;
        const vehicleModel = vehicleModelDropdown.value;
        const pickupDate = document.getElementById("pickup-date").value;
        const returnDate = document.getElementById("return-date").value;

        if (!vehicleCategory || !vehicleModel || !pickupDate || !returnDate) {
            alert("Please complete all fields before calculating cost.");
            return;
        }

        const startDate = new Date(pickupDate);
        const endDate = new Date(returnDate);
        const days = (endDate - startDate) / (1000 * 60 * 60 * 24);

        if (days <= 0) {
            alert("Return date must be after pickup date.");
            return;
        }

        let totalCost = days * VEHICLE_RATES[vehicleCategory];

        const selectedServices = Array.from(document.querySelectorAll('input[name="services"]:checked'))
            .map(service => service.value);

        selectedServices.forEach(service => {
            totalCost += days * ADDITIONAL_SERVICES[service];
        });

        totalCostElement.textContent = `$${totalCost.toFixed(2)}`;
        confirmBookingBtn.disabled = false;

        // Display summary details
        summaryDetails.innerHTML = `
            <strong>Vehicle:</strong> ${vehicleModel} (${vehicleCategory.toUpperCase()})<br>
            <strong>Pickup Date:</strong> ${pickupDate}<br>
            <strong>Return Date:</strong> ${returnDate}<br>
            <strong>Days:</strong> ${days}<br>
            <strong>Services:</strong> ${selectedServices.length > 0 ? selectedServices.join(", ") : "None"}<br>
            <strong>Total Cost:</strong> $${totalCost.toFixed(2)}
        `;
    });

    confirmBookingBtn.addEventListener("click", () => {
        const vehicleCategory = document.querySelector('input[name="vehicle-category"]:checked')?.value;
        const vehicleModel = vehicleModelDropdown.value;
        const pickupDate = document.getElementById("pickup-date").value;
        const returnDate = document.getElementById("return-date").value;
        const totalCost = parseFloat(totalCostElement.textContent.replace("$", ""));
    
        if (!vehicleCategory || !vehicleModel || !pickupDate || !returnDate || isNaN(totalCost) || totalCost <= 0) {
            alert("Please calculate the cost before confirming booking.");
            return;
        }
    
        const selectedServices = Array.from(document.querySelectorAll('input[name="services"]:checked'))
            .map(service => service.value);
    
        const bookingData = {
            vehicleCategory,
            vehicleModel,
            pickupDate,
            returnDate,
            selectedServices,
            totalCost
        };
    
        fetch("booking_process.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(bookingData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert(`
                    Booking Confirmed! 🎉
                    Confirmation Code: ${data.booking.confirmationCode}
                    Vehicle: ${data.booking.vehicleCategory} - ${data.booking.vehicleModel}
                    Pickup Date: ${data.booking.pickupDate}
                    Return Date: ${data.booking.returnDate}
                    Services: ${data.booking.selectedServices}
                    Total Cost: $${data.booking.totalCost}
                `);
                resetForm();
            } else {
                alert(`Booking Failed: ${data.message}`);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while processing your booking. Please try again.");
        });
    });
    

    function resetForm() {
        document.querySelectorAll('input[name="vehicle-category"]').forEach(input => (input.checked = false));
        vehicleModelDropdown.innerHTML = `<option value="">Choose a model</option>`;
        document.getElementById("pickup-date").value = "";
        document.getElementById("return-date").value = "";
        document.querySelectorAll('input[name="services"]:checked').forEach(input => (input.checked = false));
        totalCostElement.textContent = "$0.00";
        confirmBookingBtn.disabled = true;
        modelImage.style.display = "none";
        modelName.textContent = "";
        summaryDetails.innerHTML = "";
    }
});
 