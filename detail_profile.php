<?php
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

session_start();
$userRole = $_SESSION['role']; // Mendapatkan role dari sesi

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1; // Default ke ID 1 jika tidak ada ID

// Ambil data profil berdasarkan ID
$sql = "SELECT * FROM profile_details WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Profil tidak ditemukan.");
}

// Mengambil ID sebelumnya dan berikutnya
$prev_sql = "SELECT id FROM profile_details WHERE id < $id ORDER BY id DESC LIMIT 1";
$next_sql = "SELECT id FROM profile_details WHERE id > $id ORDER BY id ASC LIMIT 1";

$prev_result = $conn->query($prev_sql);
$next_result = $conn->query($next_sql);

$prev_id = $prev_result->num_rows > 0 ? $prev_result->fetch_assoc()['id'] : null;
$next_id = $next_result->num_rows > 0 ? $next_result->fetch_assoc()['id'] : null;

$conn->close();
?>

<html>
<head>
  <title>Profile Details</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f8f9fa;
    }
    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: white;
      padding: 20px;
    }
    .header {
      width: 100%;
      height: 70px;
      background: linear-gradient(135deg, #1663DE, #3B82F6);
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      box-shadow: 0 2px 10px rgba(22, 99, 222, 0.2);
      position: fixed;
      top: 0;
      z-index: 1000;
      box-sizing: border-box;
    }
    .header .logo {
      display: flex;
      align-items: center;
      height: 100%;
      padding: 10px 0;
    }
    .header .logo img {
      height: 48px;
      width: 48px;
      margin-right: 15px;
      border-radius: 8px;
      object-fit: contain;
      background: white;
      padding: 4px;
      transition: transform 0.3s;
    }
    .header .logo img:hover {
      transform: scale(1.1);
    }
    .header .title {
      font-size: 1.4rem;
      font-weight: 600;
      flex-grow: 1;
      text-align: center;
      margin: 0 15px;
    }
    .home-button {
      background-color: #79BAEC;
      color: white;
      border: none;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    .home-button:hover {
      background-color: #64B5F6;
      transform: scale(1.1);
    }
    .card {
      background-color: white;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      padding: 35px;
      width: 100%;
      max-width: 500px;
      margin: 80px 20px 20px;
      transition: transform 0.3s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card h2 {
      margin-top: 0;
      font-size: 28px;
      color: #1565C0;
      text-align: center;
      margin-bottom: 25px;
    }
    .card img {
      width: 100%;
      border-radius: 10px;
      margin-top: 15px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card .info {
      margin-bottom: 20px;
      font-size: 16px;
      color: #546E7A;
      padding: 10px;
      border-bottom: 1px solid #E3F2FD;
    }
    .card .info span {
      font-weight: 600;
      color: #1565C0;
      display: inline-block;
      width: 100px;
    }
    .arrow-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: #90CAF9;
      color: white;
      border: none;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .arrow-button:hover {
      background-color: #64B5F6;
      transform: translateY(-50%) scale(1.1);
    }
    .arrow-button.left {
      left: 20px;
    }
    .arrow-button.right {
      right: 20px;
    }
    .edit-button {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #79BAEC;
      color: white;
      border: none;
      border-radius: 50%;
      width: 56px;
      height: 56px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 15px rgba(22, 99, 222, 0.15);
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .edit-button:hover {
      background-color: #64B5F6;
      transform: scale(1.1);
    }
    @media (max-width: 600px) {
      .header {
        height: 60px;
        padding: 0 15px;
      }
      .header .logo img {
        height: 32px;
        width: 32px;
      }
      .header .title {
        font-size: 1.2rem;
      }
      .home-button {
        width: 40px;
        height: 40px;
      }
      .card {
        padding: 25px;
        margin: 60px 15px 15px;
      }
      .card .info {
        font-size: 14px;
      }
      .arrow-button {
        width: 40px;
        height: 40px;
      }
      .edit-button {
        width: 45px;
        height: 45px;
      }
    }
    @media (max-width: 400px) {
      .header {
        height: 50px;
      }
      .header .title {
        font-size: 1.1rem;
      }
      .header .logo img {
        height: 70%;
        margin-right: 8px;
      }
      .home-button {
        width: 35px;
        height: 35px;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="logo">
      <img alt="Logo" src="img/logo.png"/>
    </div>
    <div class="title">Profile Details</div>
    <a href="profil.php" class="home-button">
      <i class="fas fa-home"></i>
    </a>
  </div>

  <div class="container">
    <?php if ($prev_id): ?>
        <button class="arrow-button left" onclick="window.location.href='detail_profile.php?id=<?php echo $prev_id; ?>'">
            <i class="fas fa-arrow-left"></i>
        </button>
    <?php endif; ?>

    <div class="card">
        <h2>Profile Details</h2>
        <div class="info">
            <span>NAMA:</span> <?php echo htmlspecialchars($row['nama']); ?>
        </div>
        <div class="info">
            <span>JABATAN:</span> <?php echo htmlspecialchars($row['jabatan']); ?>
        </div>
        <div class="info">
            <span>NIP:</span> <?php echo htmlspecialchars($row['nip']); ?>
        </div>
        <div class="info">
            <span>NO. HP:</span> <?php echo htmlspecialchars($row['no_hp']); ?>
        </div>
        <div class="info">
            <span>FOTO:</span><br>
            <img alt="Profile picture" src="<?php echo htmlspecialchars($row['foto_url']); ?>" width="150"/>
        </div>
    </div>

    <?php if ($next_id): ?>
        <button class="arrow-button right" onclick="window.location.href='detail_profile.php?id=<?php echo $next_id; ?>'">
            <i class="fas fa-arrow-right"></i>
        </button>
    <?php endif; ?>
  </div>

  <?php if ($userRole == 'admin'): ?>
    <button class="edit-button" onclick="window.location.href='edit_profile.php?id=<?php echo $row['id']; ?>'">
      <i class="fas fa-edit"></i>
    </button>
  <?php endif; ?>
</body>
</html>
