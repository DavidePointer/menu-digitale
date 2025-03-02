<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Driver SQL Server</h2>";

// Verifica estensione sqlsrv
if (extension_loaded('sqlsrv')) {
    echo "✅ Driver SQLSRV installato<br>";
    echo "Versione driver: " . phpversion('sqlsrv') . "<br>";
} else {
    echo "❌ Driver SQLSRV NON installato<br>";
}

// Verifica estensione PDO SQLSRV
if (in_array('sqlsrv', PDO::getAvailableDrivers())) {
    echo "✅ Driver PDO SQLSRV installato<br>";
} else {
    echo "❌ Driver PDO SQLSRV NON installato<br>";
}

// Mostra tutti i driver PDO disponibili
echo "<br>Driver PDO disponibili:<br>";
print_r(PDO::getAvailableDrivers());
?> 