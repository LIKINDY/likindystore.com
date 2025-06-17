<?php
// register.php
// This page allows new users to register for an account.

// Include the minimal authentication header which contains database connection and starts HTML
include 'auth_header.php'; // <--- CHANGED THIS LINE

$registration_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $full_name = $conn->real_escape_string(trim($_POST['full_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone_number = $conn->real_escape_string(trim($_POST['phone_number']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Input Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $registration_message = "<div class='message error'>Please fill in all required fields.</div>";
    } elseif (empty($email)) { // Explicitly check for empty email after trim
        $registration_message = "<div class='message error'>Email address cannot be empty.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_message = "<div class='message error'>Please enter a valid email address.</div>";
    } elseif ($password !== $confirm_password) {
        $registration_message = "<div class='message error'>Passwords do not match.</div>";
    } elseif (strlen($password) < 6) {
        $registration_message = "<div class='message error'>Password must be at least 6 characters long.</div>";
    } else {
        // Check if email already exists
        $sql_check_email = "SELECT id FROM `users` WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check_email);
        
        if ($stmt_check === FALSE) {
            $registration_message = "<div class='message error'>Database error checking email: " . $conn->error . "</div>";
            error_log("SQL Error in register.php email check prepare: " . $conn->error);
        } else {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check_email = $stmt_check->get_result();
            $stmt_check->close(); // Close the statement after use

            if ($result_check_email->num_rows > 0) {
                $registration_message = "<div class='message error'>This email is already registered. Please login or use a different email.</div>";
            } else {
                // Hash the password for security
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into database
                $sql_insert_user = "INSERT INTO `users` (`full_name`, `email`, `phone_number`, `password_hash`) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert_user);

                if ($stmt_insert === FALSE) {
                    $registration_message = "<div class='message error'>Database error during registration: " . $conn->error . "</div>";
                    error_log("SQL Error in register.php insert prepare: " . $conn->error);
                } else {
                    $stmt_insert->bind_param("ssss", $full_name, $email, $phone_number, $password_hash);

                    if ($stmt_insert->execute()) {
                        $registration_message = "<div class='message success'>Registration successful! You can now <a href='login.php' style='color: #155724; text-decoration: underline;'>login here</a>.</div>";
                        // Optionally, automatically log in the user after registration
                        // $_SESSION['user_id'] = $conn->insert_id;
                        // $_SESSION['user_email'] = $email;
                        // header("Location: my-account.php"); // Redirect to a user dashboard
                        // exit();
                    } else {
                        $registration_message = "<div class='message error'>Error during registration: " . $conn->error . "</div>";
                    }
                    $stmt_insert->close();
                }
            }
        }
    }
}
?>
<div class="container">
    <div class="auth-container">
        <h2>Register Account</h2>
        <?php echo $registration_message; ?>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number (Optional):</label>
                <input type="tel" id="phone_number" name="phone_number" placeholder="+255 7XX XXX XXX">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit" name="register"><i class="fa-solid fa-user-plus"></i> Register</button>
            </div>
        </form>
        <p class="form-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php
// Include the authentication footer which closes HTML tags and database connection
include 'auth_footer.php'; // <--- CHANGED THIS LINE
?>
