<?php
// Allow all domains to access this resource (for development purposes)
header("Access-Control-Allow-Origin: *"); // Allows all origins (be cautious in production)

// Allow the necessary methods, including PUT
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS"); // Allow GET, POST, PUT, OPTIONS methods

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow headers such as Content-Type and Authorization

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respond with a 200 status code for preflight requests
    header("HTTP/1.1 200 OK");
    exit(); // Stop further processing
}
?>
