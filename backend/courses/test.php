<?php
// Disable error display in the output
ini_set('display_errors', 0);
error_reporting(0);

// Send JSON header
header('Content-Type: application/json');

// Simple response
echo json_encode(["success" => true, "message" => "Test successful"]);
exit;