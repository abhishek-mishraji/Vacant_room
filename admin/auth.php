<?php
// include this at top of admin pages to require login
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: /login.php');
    exit;
}
