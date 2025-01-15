<?php

// Check if running on Heroku
if(getenv("JAWSDB_URL")) {
    // Parse Heroku database URL
    $url = parse_url(getenv("JAWSDB_URL"));
    
    define('DB_SERVER', $url["host"]);
    define('DB_USERNAME', $url["user"]);
    define('DB_PASSWORD', $url["pass"]);
    define('DB_NAME', substr($url["path"], 1));
} else {
    // Local database settings
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');     
    define('DB_PASSWORD', '');          
    define('DB_NAME', 'university');
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
