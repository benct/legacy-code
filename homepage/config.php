<?php

// Show errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Settings
define('BASE_URL', 'http://tomlin.no/');
define('SITE_NAME', 'tomlin.no');
define('WEBMASTER_EMAIL', 'ben@tomlin.no');

// Administration user
define('USERNAME', 'admin');
define('PASSWORD', '#####');

// Load session
session_start();
session_regenerate_id(true);

// Check if user is logged in
if (isset($_COOKIE['bct']) && $_COOKIE['bct'] == md5(USERNAME . PASSWORD))
    define('ADMIN', true);
else
    define('ADMIN', false);


// Send an email using the php mail function
function sendEmail($toEmail, $fromEmail, $subject, $message)
{
    $headers  = "From: {$fromEmail}\r\n";
    $headers .= "Reply-To: " . WEBMASTER_EMAIL . "\r\n";
    $headers .= "Return-Path: " . BASE_URL . "\r\n";
    
    if (!mail($toEmail, $subject, $message, $headers)) {
        $_SESSION['error'] = 'Error: Message delivery failed!';
    }
}

?>