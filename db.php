<?php

$host = "db.fr-pari1.bengt.wasmernet.com"; // correct spelling: pari1
$port = "10272"; 
$username = "ece82a27730b8000a28d880c4ce9";
$password = "0694ece8-2a27-7456-8000-fa8abae7efec";
$dbname = "dbcRdnh4W34AHWwhvtFt4Hea"; // must match Wasmer DB name

$conn = mysqli_connect($host, $username, $password, $dbname, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "Connected Successfully!";
?>
