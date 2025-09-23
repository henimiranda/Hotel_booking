<?php
include 'koneksi.php';

echo "<h1>Check Database Structure</h1>";

// Cek struktur tabel bookings
$query = "DESCRIBE bookings";
$result = mysqli_query($koneksi, $query);

echo "<h2>Struktur Tabel bookings:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Cek data sample
echo "<h2>Data Sample di bookings:</h2>";
$query_sample = "SELECT * FROM bookings LIMIT 3";
$result_sample = mysqli_query($koneksi, $query_sample);

if (mysqli_num_rows($result_sample) > 0) {
    echo "<table border='1' cellpadding='10'>";
    $first = true;
    while ($row = mysqli_fetch_assoc($result_sample)) {
        if ($first) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<th>{$key}</th>";
            }
            echo "</tr>";
            $first = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>{$value}</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Tidak ada data di tabel bookings</p>";
}
?>