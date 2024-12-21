// Get the form element
const form = document.querySelector('form');

// Regular expressions for validation
const validationRules = {
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    // Updated phone regex to handle both 10 and 11 digits
    phone: /^(\d{3}-\d{3}-\d{4}|\d{3}-\d{4}-\d{4})$/,
    postcode: /^\d{5}$/,
    name: /^[A-Za-z\s]{2,50}$/
};

// Function to validate email format
function isValidEmail(email) {
    if (!email.includes('@') || !email.includes('.')) {
        return false;
    }
    
    if (email.startsWith('@') || email.startsWith('.') || 
        email.endsWith('@') || email.endsWith('.')) {
        return false;
    }
    
    return validationRules.email.test(email);
}

// Function to show error message
function showError(message) {
    alert(message);
    return false;
}

// Main validation function
function validateForm(event) {
    event.preventDefault();
    
    // Get all required inputs
    const requiredInputs = form.querySelectorAll('[required]');
    
    // Check for empty fields
    for (const input of requiredInputs) {
        const value = input.value.trim();
        
        if (!value) {
            return showError(`Please fill in the ${input.id || input.name} field`);
        }
        
        // Validation for specific fields
        switch (input.type) {
            case 'email':
                if (!isValidEmail(value)) {
                    return showError('Please enter a valid email address');
                }
                break;
                
            case 'tel':
                if (!validationRules.phone.test(value)) {
                    return showError('Phone number must be either 10 digits (XXX-XXX-XXXX) or 11 digits (XXX-XXXX-XXXX)');
                }
                break;
                
            case 'text':
                if (input.id === 'fname' || input.id === 'lname') {
                    if (!validationRules.name.test(value)) {
                        return showError(`${input.id === 'fname' ? 'First' : 'Last'} name must be 2-50 characters long and contain only letters`);
                    }
                }
                if (input.id === 'postcode' && !validationRules.postcode.test(value)) {
                    return showError('Postcode must be exactly 5 digits');
                }
                break;
                
            case 'radio':
                // Check if any radio button in the group is checked
                const groupName = input.name;
                const radioGroup = form.querySelectorAll(`input[name="${groupName}"]`);
                let checked = false;
                for (const radio of radioGroup) {
                    if (radio.checked) {
                        checked = true;
                        break;
                    }
                }
                if (!checked) {
                    return showError(`Please select a ${groupName}`);
                }
                break;
        }
    }
    
    // Check Terms and Conditions
    const termsCheckbox = document.getElementById('accept');
    if (!termsCheckbox.checked) {
        return showError('Please accept the Terms and Conditions');
    }
    
    // If all validations pass, submit the form
    alert('Form submitted successfully!');
    form.submit();
}

// Add event listener for form submission
form.addEventListener('submit', validateForm);

// Add real-time validation for email
const emailInput = document.getElementById('email');
emailInput.addEventListener('input', function() {
    const email = this.value.trim();
    if (email && !isValidEmail(email)) {
        this.setCustomValidity('Please enter a valid email address');
    } else {
        this.setCustomValidity('');
    }
});

// Add real-time validation for phone
const phoneInput = document.getElementById('phone');
phoneInput.addEventListener('input', function() {
    const phone = this.value.trim();
    if (phone && !validationRules.phone.test(phone)) {
        this.setCustomValidity('Phone number must be either 10 digits (XXX-XXX-XXXX) or 11 digits (XXX-XXXX-XXXX)');
    } else {
        this.setCustomValidity('');
    }
});

// Enhanced phone number formatter to handle both 10 and 11 digits
phoneInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
    
    if (value.length > 0) {
        // First 3 digits are always formatted the same
        if (value.length <= 3) {
            value = value;
        } 
        // Handle different formats based on total length
        else if (value.length <= 7) {
            // If total length will be 11 digits (based on current input)
            if (value.length === 7 || 
                (value.length === 6 && phoneInput.dataset.format === '11')) {
                value = value.slice(0, 3) + '-' + value.slice(3);
                phoneInput.dataset.format = '11';
            } 
            // If total length will be 10 digits
            else {
                value = value.slice(0, 3) + '-' + value.slice(3, 6);
                phoneInput.dataset.format = '10';
            }
        } 
        else {
            // Format for 11 digits
            if (value.length > 10 || phoneInput.dataset.format === '11') {
                value = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7, 11);
                phoneInput.dataset.format = '11';
            }
            // Format for 10 digits
            else {
                value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                phoneInput.dataset.format = '10';
            }
        }
        e.target.value = value;
    }
});