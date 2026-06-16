<?php
$conn = mysqli_connect("localhost", "root", "", "cea_rainfall", 3307);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>