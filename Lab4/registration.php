<?php
session_start();
// Database Configuration
$databaseHost = 'localhost';
$databaseName = 'registration_db';
$databaseUsername = 'root';  // Default XAMPP MySQL username
$databasePassword = '';      // Default XAMPP MySQL password (usually empty)

// Establish Database Connection
$mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);

// Check Connection
if (!$mysqli) {
    die("Connection failed: " . mysqli_connect_error());
}

// Validation Functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateName($name) {
    return preg_match('/^[A-Za-z\s]{2,50}$/', $name);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    return preg_match('/^(0\d{2}-\d{3}-\d{4}|0\d{2}-\d{4}-\d{4})$/', $phone);
}

function validatePostcode($postcode) {
    return preg_match('/^\d{5}$/', $postcode);
}

// Form Submission Handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $errors = [];

    // Validate First Name
    $firstName = sanitizeInput($_POST['first_name']);
    if (empty($firstName)) {
        $errors[] = "First Name is required";
    } elseif (!validateName($firstName)) {
        $errors[] = "Invalid First Name format";
    }

    // Validate Last Name
    $lastName = sanitizeInput($_POST['last_name']);
    if (empty($lastName)) {
        $errors[] = "Last Name is required";
    } elseif (!validateName($lastName)) {
        $errors[] = "Invalid Last Name format";
    }

    // Validate Email
    $email = sanitizeInput($_POST['email']);
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Invalid Email format";
    }

    // Validate Phone
    $phone = sanitizeInput($_POST['phone']);
    if (empty($phone)) {
        $errors[] = "Phone is required";
    } elseif (!validatePhone($phone)) {
        $errors[] = "Invalid Phone number format";
    }

    // Validate Date of Birth
    $dob = sanitizeInput($_POST['date_of_birth']);
    if (empty($dob)) {
        $errors[] = "Date of Birth is required";
    }

    // Validate Gender
    $gender = isset($_POST['gender']) ? sanitizeInput($_POST['gender']) : '';
    if (empty($gender)) {
        $errors[] = "Gender selection is required";
    }

    // Validate Street
    $street = sanitizeInput($_POST['street']);
    
    // Validate City
    $city = sanitizeInput($_POST['city']);

    // Validate State
    $state = sanitizeInput($_POST['state']);
    if (empty($state)) {
        $errors[] = "State selection is required";
    }

    // Validate Country
    $country = sanitizeInput($_POST['country']);
    if (empty($country)) {
        $errors[] = "Country is required";
    }

    // Validate Postcode
    $postcode = sanitizeInput($_POST['postcode']);
    if (empty($postcode)) {
        $errors[] = "Postcode is required";
    } elseif (!validatePostcode($postcode)) {
        $errors[] = "Invalid Postcode format";
    }

    // Validate Terms and Conditions
    $acceptTerms = isset($_POST['terms_accepted']) ? 1 : 0;
    if (!$acceptTerms) {
        $errors[] = "You must accept the Terms and Conditions";
    }

    // If no validation errors, proceed with database insertion
    if (empty($errors)) {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $mysqli->prepare("INSERT INTO user_registration (
            first_name, 
            last_name, 
            email, 
            phone, 
            date_of_birth, 
            gender, 
            street, 
            city, 
            state, 
            country, 
            postcode, 
            terms_accepted
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param(
            "sssssssssssi", 
            $firstName, 
            $lastName, 
            $email, 
            $phone, 
            $dob, 
            $gender, 
            $street, 
            $city, 
            $state, 
            $country, 
            $postcode, 
            $acceptTerms
        );

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['registration_success'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // Check for duplicate email
            if ($mysqli->errno == 1062) {
                echo "<div style='color: red;'>Email already exists. Please use a different email.</div>";
            } else {
                echo "<div style='color: red;'>Registration Failed: " . htmlspecialchars($stmt->error) . "</div>";
            }
        }

        $stmt->close();
    } else {
        // Store errors in session to display after redirect
        $_SESSION['registration_errors'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_SESSION['registration_errors'])) {
    echo "<div style='color: red; margin: 10px; padding: 10px; background: #ffeeee; border: 1px solid red;'>";
    foreach ($_SESSION['registration_errors'] as $error) {
        echo htmlspecialchars($error) . "<br>";
    }
    echo "</div>";
    unset($_SESSION['registration_errors']); // Clear errors
}

// Close database connection
mysqli_close($mysqli);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php
    if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
        echo "<div style='color: green; position: fixed; top: 10px; left: 10px; background: white; padding: 10px; z-index: 1000;'>Registration Successful!</div>";
        unset($_SESSION['registration_success']); // Clear the message
    }
    ?>
    <h1>Registration Form</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <fieldset>
            <legend>Personal Information</legend>
            <div>
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required autofocus
                    placeholder="Enter your first name" autocomplete="given-name"
                    pattern="[A-Za-z\s]{2,50}" title="2-50 characters, letters only">
            </div>
            <div>
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required
                    placeholder="Enter your last name" autocomplete="family-name"
                    pattern="[A-Za-z\s]{2,50}" title="2-50 characters, letters only">
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                    placeholder="your.email@example.com">
            </div>
            <div>
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" required
                    placeholder="XXX-XXX-XXXX or XXX-XXXX-XXXX" autocomplete="tel"
                    pattern="^(0\d{2}-\d{3}-\d{4}|0\d{2}-\d{4}-\d{4})$"
                    title="Phone number must be either 10 digits (XXX-XXX-XXXX) or 11 digits (XXX-XXXX-XXXX)">
            </div>
            <div>
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" required
                    min="1900-01-01" max="2024-12-31">
            </div>
            <div>
                <label>Gender:</label>
                <div>
                    <input type="radio" id="male" name="gender" required value="Male">
                    <label for="male">Male</label>
                    
                    <input type="radio" id="female" name="gender" required value="Female">
                    <label for="female">Female</label>
                    
                    <input type="radio" id="others" name="gender" required value="Others">
                    <label for="others">Others</label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Address</legend>
            <div>
                <label for="street">Street</label>
                <input type="text" id="street" name="street" required
                    placeholder="Enter street address" autocomplete="street-address">
            </div>
            <div>
                <label for="city">City</label>
                <input type="text" id="city" name="city" required
                    placeholder="Enter city name" autocomplete="address-level2">
            </div>
            <div>
                <label for="state">State</label>
                <select id="state" name="state" required autocomplete="address-level1">
                    <option value="">Select a state</option>
                    <option value="Sarawak">Sarawak</option>
                    <option value="Sabah">Sabah</option>
                    <option value="Selangor">Selangor</option>
                    <option value="Johor">Johor</option>
                    <option value="Penang">Penang</option>
                </select>
            </div>
            <div>
                <label for="country">Country</label>
                <input type="text" id="country" name="country" required
                    placeholder="Enter country" autocomplete="country">
            </div>
            <div>
                <label for="postcode">Postcode</label>
                <input type="text" id="postcode" name="postcode" required
                    placeholder="Enter 5-digit postcode" pattern="[0-9]{5}"
                    title="5-digit postcode" autocomplete="postal-code">
            </div>
        </fieldset>

        <div>
            <textarea cols="65" rows="3" readonly style="margin:5px 0 0 2px">
TERMS AND CONDITIONS
1. All information provided must be true and accurate.
2. By submitting this form, you agree to our privacy policy.
3. We may contact you via email or phone for verification purposes.
            </textarea>
        </div>
        <div>
            <input type="checkbox" id="terms_accepted" name="terms_accepted" required>
            <label for="terms_accepted" style="width:90%">I accept the above Terms and Conditions.</label>
        </div>

        <div>
            <input type="submit" value="Register">
            <input type="reset" value="Clear">
        </div>
    </form>
    <br>
    <div style="text-align: center;">
        <a>Back to Main Page</a>
    </div>
    <script src="validation.js"></script>
</body>
</html>