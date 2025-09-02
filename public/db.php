<?php
// config/db.php

// $host = "host.docker.internal"; // connect via host machine
// $port = 3306;                   // MySQL port published in docker-compose of MySQL
// $database = "find_vacant_room";
// $user = "mysql";                // from your .env MYSQL_USER
// $password = "mysql";            // from your .env MYSQL_PASSWORD

// try {
//     $conn = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", $user, $password);
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     echo "Connected successfully"; // enable only for testing
// } catch (PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }

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

try {
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
    echo "Connected successfully";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
