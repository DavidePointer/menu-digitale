<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Driver PDO disponibili:</h2>";
print_r(PDO::getAvailableDrivers());

echo "<h2>Estensioni PHP caricate:</h2>";
print_r(get_loaded_extensions());
?> 