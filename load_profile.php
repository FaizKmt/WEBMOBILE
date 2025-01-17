<?php
// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'logbook1');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk mengambil data dari tabel profile_details
$sql = "SELECT id, nama, jabatan, nip, no_hp, foto_url FROM profile_details";
$result = $conn->query($sql);

if ($result === false) {
    // Jika query gagal, tampilkan pesan error
    die("Query error: " . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Membuat kartu profil
        echo '<div class="card" style="cursor: pointer;" onclick="window.location.href=\'detail_profile.php?id=' . $row['id'] . '\'">';
        
        // Jika ada foto yang diunggah, tampilkan foto tersebut, jika tidak tampilkan gambar default
        if (!empty($row['foto_url'])) {
            echo '<img src="' . htmlspecialchars($row['foto_url']) . '" alt="Profile Picture" height="80" width="80">';
        } else {
            echo '<img src="https://storage.googleapis.com/a1aa/image/D0xOyvHNMrYmCle4igBXk7xX2ljTXMTr3h1cX7OeeEO2LrbnA.jpg" alt="Default Profile Picture" height="80" width="80">';
        }

        // Tampilkan nama dan jabatan dengan format yang sesuai
        echo '<h3>' . htmlspecialchars($row['nama']) . '</h3>';
        echo '<p>JABATAN: ' . htmlspecialchars($row['jabatan']) . '</p>';
        
        echo '</div>';
    }
} else {
    echo "Tidak ada data.";
}

$conn->close();
?>

<style>
    .card {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 10px;
        padding: 20px;
        width: 200px;
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s; /* Menambahkan transisi untuk transformasi dan bayangan */
        color: inherit;
        cursor: pointer;
    }

    .card:hover {
        transform: scale(1.05); /* Membesarkan kartu saat hover */
        box-shadow: 0 8px 16px rgba(0, 0, 0 , 0.2); /* Menambahkan bayangan yang lebih besar saat hover */
    }
</style>