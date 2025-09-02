<?php
// Load environment variables from .env file if not already loaded
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

// Get Heroku ClearDB connection information or use local database
$cleardb_url = getenv("CLEARDB_DATABASE_URL");
if ($cleardb_url) {
    $cleardb_server = parse_url($cleardb_url, PHP_URL_HOST);
    $cleardb_username = parse_url($cleardb_url, PHP_URL_USER);
    $cleardb_password = parse_url($cleardb_url, PHP_URL_PASS);
    $cleardb_db = substr(parse_url($cleardb_url, PHP_URL_PATH), 1);
    $cleardb_port = parse_url($cleardb_url, PHP_URL_PORT) ?: 3306;

    $dsn = "mysql:host=$cleardb_server;port=$cleardb_port;dbname=$cleardb_db;charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
} else {
    // Local database or other cloud database like Aiven
    $db_host = getenv("DB_HOST") ?: "localhost";
    $db_port = getenv("DB_PORT") ?: "3306";
    $db_name = getenv("DB_NAME") ?: "find_vacant_room";
    $db_user = getenv("DB_USER") ?: "root";
    $db_pass = getenv("DB_PASS") ?: "";
    $db_ssl = getenv("DB_SSL") ?: "false";

    // For Aiven MySQL, we need to use TCP explicitly and ensure SSL cert is found
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
    if ($db_ssl === "true") {
        // Ensure we're using absolute path to the certificate
        $ca_cert_path = realpath(__DIR__ . '/ca.pem');
        if (!$ca_cert_path) {
            die("SSL Certificate not found at " . __DIR__ . '/ca.pem');
        }
        $options = [
            PDO::MYSQL_ATTR_SSL_CA => $ca_cert_path,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30
        ];
    } else {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30
        ];
    }
}

// Create connection
try {
    if ($cleardb_url) {
        $conn = new PDO($dsn, $cleardb_username, $cleardb_password, $options);
    } else {
        $conn = new PDO($dsn, $db_user, $db_pass, $options);
    }
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    echo "<br>DSN: " . $dsn;
    echo "<br>Host: " . ($cleardb_url ? $cleardb_server : $db_host);
    echo "<br>Port: " . ($cleardb_url ? $cleardb_port : $db_port);
    echo "<br>SSL: " . ($db_ssl === "true" ? "Enabled" : "Disabled");
    if ($db_ssl === "true") {
        echo "<br>CA Cert Path: " . $ca_cert_path;
        echo "<br>CA Cert exists: " . (file_exists($ca_cert_path) ? "Yes" : "No");
    }
    die();
}
