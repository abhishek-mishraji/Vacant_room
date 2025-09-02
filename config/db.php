<?php
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

    $dsn = "mysql:";
    $dsn .= "host=" . $db_host;
    $dsn .= ";port=" . $db_port;
    $dsn .= ";dbname=" . $db_name;
    $dsn .= ";charset=utf8";

    if ($db_ssl === "true") {
        $dsn .= ";sslmode=REQUIRED";
        $options = [
            PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca.pem', // Make sure ca.pem is present in config/
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
    } else {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
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
    die("Database connection failed: " . $e->getMessage());
}
