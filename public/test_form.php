<?php
// Form submission test

session_start();

echo "<h1>Form Submission Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>✅ POST Request Received!</h2>";
    echo "<p><strong>POST Data:</strong></p>";
    echo "<pre>" . htmlspecialchars(json_encode($_POST, JSON_PRETTY_PRINT)) . "</pre>";
    
    echo "<p><strong>All Server Variables:</strong></p>";
    echo "<pre>" . htmlspecialchars(json_encode($_SERVER, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<h2>GET Request - Showing Form</h2>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, button { padding: 8px; font-size: 14px; }
        input[type="email"], input[type="password"] { width: 250px; }
        button { background: #007cba; color: white; border: none; cursor: pointer; }
        .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>

<form method="POST" action="/test_form.php" id="testForm">
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="admin@example.com" required>
    </div>
    
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" value="Admin@123" required>
    </div>
    
    <div class="form-group">
        <button type="submit" id="submitBtn">Test Submit</button>
    </div>
</form>

<div class="debug">
    <strong>Debug Info:</strong><br>
    Current URL: <span id="currentUrl"></span><br>
    Form Action: <span id="formAction"></span><br>
    Form Method: <span id="formMethod"></span><br>
</div>

<p><a href="/login">← Back to Real Login</a></p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('testForm');
    const btn = document.getElementById('submitBtn');
    
    // Display debug info
    document.getElementById('currentUrl').textContent = window.location.href;
    document.getElementById('formAction').textContent = form.action;
    document.getElementById('formMethod').textContent = form.method;
    
    console.log('Test form loaded');
    console.log('Form:', form);
    console.log('Button:', btn);
    
    form.addEventListener('submit', function(e) {
        console.log('Test form submitted!');
        btn.disabled = true;
        btn.textContent = 'Submitting...';
    });
    
    btn.addEventListener('click', function(e) {
        console.log('Test button clicked!');
    });
});
</script>

</body>
</html>