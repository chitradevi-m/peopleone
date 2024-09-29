<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $conn = db_connect();

    // Check for username match with LIKE
    $check_username = $conn->prepare("SELECT * FROM users WHERE username LIKE ?");
    $like_username = "%$username%";
    $check_username->bind_param("s", $like_username);
    $check_username->execute();
    $result = $check_username->get_result();

    if ($result->num_rows > 0) {
        // Username already exists
        http_response_code(500); // Set HTTP response code
        echo json_encode(['error' => 'Username already Exist!']);
        exit;
    } else {
        // Proceed to insert the new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Registration successful!']);
            exit;
        } else {
            echo ("reached fail");
            echo json_encode(['error' => 'Error: ' . $stmt->error]);
        }
    }
    $stmt->close();
    $check_username->close();
    $conn->close();
}
?>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    body {
        background-color: #f8f9fa;
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
</style>
<div class="container">
    <h2 class="text-center">Registration</h2>
    <form name="registrationForm" method="post" action="register.php">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="form-group">
            <label for="username">Username:<span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control username" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="email">Email:<span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control email" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="password">Password:<span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control password" required autocomplete="off">
        </div>
        <div class="col-md-12 submission">
            <button type="submit" class="btn btn-primary btn-block col-md-4 text-center" onclick="return validateForm()">Register</button>
        </div>
    </form>
</div>
<script>
    function validateForm() {
        const username = document.querySelector(".username").value;
        const email = document.querySelector(".email").value;
        const password = document.querySelector(".password").value;

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

        // Email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Email',
                text: 'Please Enter the valid Email',
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

    document.querySelector("form").addEventListener("submit", function(e) {
        e.preventDefault(); // Prevent default form submission

        if (!validateForm()) {
            return; // Stop the function if validation fails
        }

        const formData = new FormData(this);

        fetch('register.php', {
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
                //alert(data.success);
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.success,
                }).then(() => {
                    // Redirect to a desired page after the user clicks "OK"
                    window.location.href = "login.php"; // Change this to the desired URL
                });

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