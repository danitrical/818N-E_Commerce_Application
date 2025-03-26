<?php
// Load .env file manually
$envFile = '/var/www/html/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) { // Skip comments
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Access environment variables
$dbHost = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];
$dbName = $_ENV['DB_NAME'];

// AWS RDS SSL Configuration
$sslCa = $_ENV['DB_SSL_CA'] ?? '/etc/ssl/certs/rds-combined-ca-bundle.pem'; // AWS RDS CA bundle
$sslVerify = $_ENV['DB_SSL_VERIFY'] ?? true;

// Create connection with SSL options
$con = new mysqli();

// Configure SSL
$con->ssl_set(
    null,               // No client key
    null,               // No client certificate
    $sslCa,             // Path to AWS RDS CA bundle
    null,               // No cipher
    null                // No other options
);

// Establish the connection
$con->real_connect(
    $dbHost,
    $dbUser,
    $dbPass,
    $dbName,
    null,               // Default port
    null,               // Default socket
    MYSQLI_CLIENT_SSL   // Enable SSL
);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Verify SSL is actually being used
$result = $con->query("SHOW STATUS LIKE 'Ssl_cipher'");
$row = $result->fetch_assoc();

if (empty($row['Value'])) {
    die("SSL not enabled for database connection");
}

echo "Connected successfully to AWS RDS database with SSL encryption!";
echo " SSL Cipher: " . $row['Value'];
?>