<?php
// login.php
// This page allows users to log in to their account.

// Include the minimal authentication header which contains database connection and starts HTML
include 'auth_header.php'; // <--- CHANGED THIS LINE

$login_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    // Input Validation
    if (empty($email) || empty($password)) {
        $login_message = "<div class='message error'>Please enter your email and password.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_message = "<div class='message error'>Please enter a valid email address.</div>";
    } else {
        // Fetch user from database
        $sql_fetch_user = "SELECT id, full_name, password_hash FROM `users` WHERE email = ?";
        $stmt = $conn->prepare($sql_fetch_user);
        
        if ($stmt === FALSE) {
            $login_message = "<div class='message error'>Database error during login: " . $conn->error . "</div>";
            error_log("SQL Error in login.php prepare: " . $conn->error);
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Verify password
                if (password_verify($password, $user['password_hash'])) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_name'] = $user['full_name'];

                    $login_message = "<div class='message success'>Login successful!</div>"; // This message might flash before redirect
                    header("Location: my-account.php"); // Redirect to a user dashboard page
                    exit();
                } else {
                    $login_message = "<div class='message error'>Incorrect password.</div>";
                }
            } else {
                $login_message = "<div class='message error'>Email not found.</div>";
            }
            $stmt->close();
        }
    }
}
?>
<div class="container">
    <div class="auth-container">
        <h2>Login</h2>
        <?php echo $login_message; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-actions">
                <button type="submit" name="login"><i class="fa-solid fa-sign-in-alt"></i> Login</button>
            </div>
        </form>
        <p class="form-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
</div>

<?php
// Include the authentication footer which closes HTML tags and database connection
include 'auth_footer.php'; // <--- CHANGED THIS LINE
?>
