<?php
include 'db.php';
$id = intval($_GET['id']);
mysqli_query($conn, "DELETE FROM Criminal WHERE criminal_id=$id");
header("Location: records.php?msg=Criminal record deleted successfully.");
exit();
