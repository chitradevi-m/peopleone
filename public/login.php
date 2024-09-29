<?php
session_start();
$_SESSION['expiration'] = time() + 600;
// Generate a CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../config/db.php';
require_once '../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403); // Forbidden
        echo json_encode(['error' => 'Invalid CSRF token.']);
        exit;
    }
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);

    // Debugging log
    error_log("Username: $username, Password: $password");

    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();
        // Debugging log
        error_log("Fetched user: $username, Hashed password: $hashed_password");

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            echo json_encode(['success' => true]); // Login successful
            exit;
        } else {
            error_log("Password verification failed for user: $username");

            echo json_encode(['error' => 'Invalid password.']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'No account found with that username.']);
        
    }

    $stmt->close();
    $conn->close();
}
?>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<style>
    body {

        background-image: url('https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.vecteezy.com%2Ffree-vector%2Flogin-background&psig=AOvVaw37sh99F7Nfk_OfEpMKzfSG&ust=1727538327963000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCPjkvPa744gDFQAAAAAdAAAAABAK');
        /* Replace with your image URL */
        background-size: cover;
        /* Ensures the image covers the entire background */
        background-repeat: no-repeat;
        /* Prevents the image from repeating */
        background-position: center;
        /* Centers the image */
    }

    .container {
        margin-top: 100px;
        max-width: 500px;
        border: 3px solid black;
        border-radius: 26px;

    }

    .form-group {
        margin-bottom: 15px;

    }

    .text-danger {
        font-size: 0.875rem;
        /* Adjust the font size for error messages */

    }

    label {
        font-size: medium;
        font-weight: bold;
    }

    .submission {
        display: flex;
        justify-content: center;
    }

    .register {
        display: flex;
        justify-content: center;
    }
</style>
<div class="container mt-5">
    <h2 class="text-center">Login</h2>
    <form id="loginForm" class="loginForm" method="post" action="login.php" class="mt-4">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="form-group">
            <label for="username">Username:<span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" id="username" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="password">Password:<span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" id="password" required autocomplete="off">
        </div>
        <div class="col-md-12 register">
            <span class="col-md-9" style="text-align: center;">Create an account <a href="register.php">Register</a></span>
        </div>

        <div class="col-md-12 submission">
            <button type="submit" class="btn btn-primary btn-block col-md-4 text-center" onclick="return validateForm()">Login</button>
        </div>
        <!-- <button type="submit" class="btn btn-primary btn-block">Login</button> -->
    </form>
</div>
<!-- Include SweetAlert CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    function validateForm() {
        const username = document.querySelector("#username").value;
        const password = document.querySelector("#password").value;

        // Username validation
        if (username.length < 3) {
            Swal.fire({
                icon: 'error',
                title: 'Please Enter the Username',
                text: 'Username must be at least 3 characters long.',
                confirmButtonText: 'OK'
            });
            return false;
        }
        // Password validation
        const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/; // At least 8 characters, at least 1 letter and 1 number
        if (!passwordPattern.test(password)) {
            Swal.fire({
                icon: 'error',
                title: 'Please Enter the valid Password',
                text: 'Password must be at least 8 characters long, and contain at least 1 letter and 1 number.',
                confirmButtonText: 'OK'
            });
            return false;
        }

        return true;
    }

    document.querySelector(".loginForm").addEventListener("submit", function(e) {
        e.preventDefault(); // Prevent default form submission

        if (!validateForm()) {
            return; // Stop the function if validation fails
        }

        const formData = new FormData(this);

        fetch('login.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'An error occurred');
                    });
                }
                return response.json();
            })
            .then(data => {
                // //alert(data.success);
                // Swal.fire({
                //     icon: 'success',
                //     title: 'Success',
                //     text: data.success,
                // }).then(() => {
                    // Redirect to a desired page after the user clicks "OK"
                    window.location.href = 'index.php';
                // });

            })
            .catch(error => {

                // alert(error.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                });
                console.error('Error:', error);
            });
    });
</script>

