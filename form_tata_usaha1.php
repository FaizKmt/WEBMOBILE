<?php
// Memulai session
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil user_id dan role dari session
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Koneksi ke database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "logbook1";

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fetch petugas from the database
$petugasList = [];
$resultPetugas = $conn->query("SELECT * FROM petugas");
while ($row = $resultPetugas->fetch_assoc()) {
    $petugasList[] = $row['nama'];
}

// Fetch tugas berdasarkan role
$tugas = [];
if ($userRole == 'user') {
    $sql_tugas = "SELECT DISTINCT melaksanakan_tugas FROM tata_usaha WHERE created_by IN (SELECT id FROM users WHERE role = 'user')";
} else {
    $sql_tugas = "SELECT DISTINCT melaksanakan_tugas FROM tata_usaha WHERE created_by IN (SELECT id FROM users WHERE role = 'admin')";
}
$result_tugas = $conn->query($sql_tugas);
while($row = $result_tugas->fetch_assoc()) {
    $tugas[] = $row['melaksanakan_tugas'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tata Usaha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-lg form-container">
        <div class="flex items-center mb-8">
            <i class="fas fa-arrow-left text-2xl text-gray-600 mr-3 cursor-pointer hover:text-gray-800" onclick="window.location.href='tata_usaha.php'"></i>
            <h1 class="text-3xl font-bold text-gray-800">Form Tata Usaha</h1>
        </div>
        <form id="formTataUsaha" action="simpan_tata_usaha.php" method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="tanggal" class="block text-gray-700 font-semibold">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" 
                       value="<?= date('Y-m-d'); ?>" 
                       max="<?= date('Y-m-d'); ?>" 
                       required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
            </div>
            <div>
                <label for="petugas" class="block text-gray-700 font-semibold">Petugas</label>
                <select id="petugas" name="petugas" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
                    <option value="">Pilih Petugas</option>
                    <?php foreach ($petugasList as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="melaksanakan_tugas" class="block text-gray-700 font-semibold">Melaksanakan Tugas</label>
                <select id="melaksanakan_tugas" name="melaksanakan_tugas" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
                    <option value="">Pilih Tugas</option>
                    <?php foreach($tugas as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                    <?php endforeach; ?>
                    <?php if($userRole == 'admin' || $userRole == 'user'): ?>
                        <option value="tambah_baru">+ Tambah Tugas Baru</option>
                    <?php endif; ?>
                </select>
                <?php if($userRole == 'admin' || $userRole == 'user'): ?>
                    <div id="tambah_tugas_form" class="hidden mt-2">
                        <div class="flex gap-2">
                            <input type="text" id="tugas_baru" placeholder="Masukkan tugas baru" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
                            <button type="button" onclick="tambahTugas()" class="bg-green-500 text-white px-4 rounded-lg hover:bg-green-600">Tambah</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <label for="output" class="block text-gray-700 font-semibold">Output</label>
                <input type="text" id="output" name="output" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
            </div>
            <div>
                <label for="tugas_lainnya" class="block text-gray-700 font-semibold">Tugas Lainnya</label>
                <input type="text" id="tugas_lainnya" name="tugas_lainnya" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
            </div>
            <div>
                <label for="catatan" class="block text-gray-700 font-semibold">Catatan</label>
                <input type="text" id="catatan" name="catatan" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
            </div>
            <div>
                <label for="data_dukung_1" class="block text-gray-700 font-semibold">Data Dukung 1</label>
                <input type="file" id="data_dukung_1" name="data_dukung_1" accept=".pdf, .doc, .docx, .jpg, .png" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
            </div>
            <div>
                <label for="data_dukung_2" class="block text-gray-700 font-semibold">Data Dukung 2</label>
                <input type="file" id="data_dukung_2" name="data_dukung_2" accept=".pdf, .doc, .docx, .jpg, .png" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-200">
            </div>
            <div class="flex justify-between mt-8">
                <button type="button" class="bg-gray-300 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-400 transition duration-200" onclick="handleCancel()">Cancel</button>
                <button type="submit" class="bg-purple-500 text-white py-3 px-6 rounded-lg hover:bg-purple-600 transition duration-200">Save</button>
            </div>
        </form>
    </div>
    <script>
        function handleCancel() {
            window.location.href = 'tata_usaha.php';
        }

        document.getElementById('petugas').addEventListener('change', function() {
            const tambahPetugasForm = document.getElementById('tambah_petugas_form');
            if (this.value === 'tambah_baru') {
                tambahPetugasForm.classList.remove('hidden');
            } else {
                tambahPetugasForm.classList.add('hidden');
            }
        });

        document.getElementById('melaksanakan_tugas').addEventListener('change', function() {
            const tambahTugasForm = document.getElementById('tambah_tugas_form');
            if (this.value === 'tambah_baru') {
                tambahTugasForm.classList.remove('hidden');
            } else {
                tambahTugasForm.classList.add('hidden');
            }
        });

        function tambahPetugas() {
            const petugasBaru = document.getElementById('petugas_baru').value;
            if (petugasBaru) {
                const select = document.getElementById('petugas');
                const option = new Option(petugasBaru, petugasBaru);
                select.insertBefore(option, select.lastElementChild);
                select.value = petugasBaru;
                document.getElementById('petugas_baru').value = '';
                document.getElementById('tambah_petugas_form').classList.add('hidden');
            }
        }

        function tambahTugas() {
            const tugasBaru = document.getElementById('tugas_baru').value;
            if (tugasBaru) {
                const select = document.getElementById('melaksanakan_tugas');
                const option = new Option(tugasBaru, tugasBaru);
                select.insertBefore(option, select.lastElementChild);
                select.value = tugasBaru;
                document.getElementById('tugas_baru').value = '';
                document.getElementById('tambah_tugas_form').classList.add('hidden');
            }
        }
    </script>
</body>
</html>
