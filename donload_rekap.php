<?php
session_start();

// Koneksi ke database
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "logbook1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil parameter dari URL
$petugas = isset($_GET['petugas']) ? $_GET['petugas'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Query untuk mengambil data
$sql = "SELECT t.*, 
        DATE_FORMAT(t.tanggal, '%d %M %Y') as formatted_date,
        DATE_FORMAT(t.approved_at, '%d %M %Y %H:%i') as approved_date
        FROM tata_usaha t
        WHERE t.petugas = ? 
        AND MONTH(t.tanggal) = ?
        AND YEAR(t.tanggal) = ?
        ORDER BY t.tanggal ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $petugas, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekap_Kinerja_' . $petugas . '_' . $month . '_' . $year . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Sheet1</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        td, th {
            mso-number-format:"\@";
        }
    </style>
</head>
<body>
<table border="1">
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-size: 16px; font-weight: bold;">
                Rekap Kinerja <?php echo htmlspecialchars($petugas); ?><br>
                Periode: <?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?>
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Tugas</th>
            <th>Output</th>
            <th>Tugas Lainnya</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while($row = $result->fetch_assoc()): 
        ?>
        <tr>
            <td style="mso-number-format:'\@';"><?php echo $no++; ?></td>
            <td style="mso-number-format:'\@';"><?php echo $row['formatted_date']; ?></td>
            <td style="mso-number-format:'\@';"><?php echo $row['melaksanakan_tugas']; ?></td>
            <td style="mso-number-format:'\@';"><?php echo $row['output']; ?></td>
            <td style="mso-number-format:'\@';"><?php echo $row['tugas_lainnya']; ?></td>
            <td style="mso-number-format:'\@';"><?php echo $row['catatan']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
