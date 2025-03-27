<?php
// Load .env file manually
$envFile = '/var/www/html/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Database credentials
$dbHost = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];
$dbName = $_ENV['DB_NAME'];

// SSL configuration - add these to your .env file
$sslCa = $_ENV['DB_SSL_CA'] ?? '/etc/ssl/certs/rds-combined-ca-bundle.pem'; // AWS RDS CA path

try {
    // Set SSL options
    $con = mysqli_init();
    $con->ssl_set(
        null,       // No client key
        null,       // No client certificate 
        $sslCa,     // CA certificate
        null,       // No cipher
        null        // No other options
    );
    
    // Establish connection
    $con->real_connect(
        $dbHost,
        $dbUser,
        $dbPass,
        $dbName,
        null,       // Default port
        null,       // Default socket
        MYSQLI_CLIENT_SSL
    );

    if ($con->connect_error) {
        throw new Exception("Connection failed: " . $con->connect_error);
    }

    // Verify SSL is actually being used
    $result = $con->query("SHOW STATUS LIKE 'Ssl_cipher'");
    $row = $result->fetch_assoc();
    
    if (empty($row['Value'])) {
        throw new Exception("SSL not enabled for database connection");
    }

    // echo "Connected successfully to the database with SSL! Cipher: " . $row['Value'];
    
    // Use $con for your queries...
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}