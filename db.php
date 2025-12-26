<?php
$host = "db.fr-pari1.bengt.wasmernet.com";
$username = "ece82a27730b8000a28d880c4ce9";
$password = "0694ece8-2a27-7456-8000-fa8abae7efec";
$dbname = "dbcRdnh4W34AHWwhvtFt4Hea"; // Replace this correctly!

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "Connected Successfully!";
?>
