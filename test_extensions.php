<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Estensioni PHP caricate:<br>";
print_r(get_loaded_extensions());

echo "<br><br>Driver PDO disponibili:<br>";
print_r(PDO::getAvailableDrivers());

echo "<br><br>Funzione sqlsrv_connect disponibile: ";
echo function_exists('sqlsrv_connect') ? "Sì" : "No";
?>