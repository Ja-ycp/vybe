<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
  echo "MySQL connected successfully!<br>";
  $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
  echo "Users: " . $stmt->fetch()['count'];
} else {
  echo "Connection failed.";
}
?>

