<?php
$conn = mysqli_connect("db.fr-pari1.bengt.wasmernet.com", "ece82a27730b8000a28d880c4ce9", "0694ece8-2a27-7456-8000-fa8abae7efec", "school_enrollment");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>
