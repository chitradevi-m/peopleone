<?php
session_start();

// Set session expiration time if it doesn't exist
if (!isset($_SESSION['expiration'])) {
    $_SESSION['expiration'] = time() + 600; // Set expiration to 10 minutes from now
}

// Check if the session has expired
if (time() > $_SESSION['expiration']) {
    header("location: logout.php"); // Redirect to logout if expired
    exit;
}

$expiration = $_SESSION['expiration'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script>
        function startCountdown(expiration) {
            const countdownElement = document.getElementById('countdown');
            const interval = setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const timeLeft = expiration - now;

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    countdownElement.innerHTML = "Session expired";
                    window.location.href = "logout.php"; // Redirect to logout
                } else {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    countdownElement.innerHTML = `${minutes}m ${seconds}s`; // Use template literals
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const expiration = <?php echo json_encode($expiration); ?>; // Ensure it's a valid JSON
            startCountdown(expiration);
        });
    </script>
</head>
<body>
    <h1>Welcome to your Dashboard</h1>
    <p>Session expires in: <span id="countdown"></span></p>
    <a href="logout.php">Logout</a>
</body>
</html>
