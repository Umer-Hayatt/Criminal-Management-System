<?php
$conn = mysqli_connect("localhost", "root", "", "criminal_record_db");
if (!$conn) {
 die("<div style='font-family:sans-serif;padding:40px;text-align:center;'>
 <h2 style='color:red'> Database Connection Failed</h2>
 <p>" . mysqli_connect_error() . "</p>
 <p>Make sure XAMPP MySQL is running!</p>
 </div>");
}
?>
