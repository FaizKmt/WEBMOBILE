<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userRole = $_SESSION['role']; // Mendapatkan role dari sesi
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FFE5B4, #FFDAB9, #FFE4C4); /* Gradient peach background */
        }
        .animated-button {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeIn 0.8s ease-out;
            width: 200px;
            height: 200px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .animated-button:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .header-animation {
            animation: slideDown 0.5s ease-out;
            background: linear-gradient(135deg, #1a75ff, #0052cc);
        }
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }
            to {
                transform: translateY(0);
            }
        }
        .menu-grid {
            animation: gridFadeIn 1s ease-out;
        }
        @keyframes gridFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .icon-spin {
            transition: transform 0.5s ease;
            color: rgba(255, 255, 255, 0.9);
        }
        .icon-spin:hover {
            transform: rotate(180deg);
            color: white;
        }
        .gradient-text {
            background: linear-gradient(45deg, #ffffff, #e6f3ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @media (max-width: 640px) {
            .animated-button {
                width: 150px;
                height: 150px;
                padding: 1rem;
            }
            .gradient-text {
                font-size: 1.5rem;
            }
        }
    </style>
    <script>
        function refreshPage() {
            location.reload();
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br to-[#FFE4C4]">
    <div class="bg-gradient-to-r from-[#1a75ff] to-[#0052cc] shadow-lg p-4 md:p-6 flex items-center justify-between header-animation">
        <div class="flex items-center space-x-2 md:space-x-4">
            <img alt="Logo" class="h-8 w-8 md:h-12 md:w-12 rounded-lg shadow-md hover:scale-110 transition-transform duration-300 bg-white p-1" 
                 src="img/logo.png"/>
            <span class="text-xl md:text-2xl font-bold text-white tracking-wider">LOGBOOK</span>
        </div>
        <div>
            <i class="fas fa-sync-alt text-lg md:text-xl cursor-pointer icon-spin" onclick="refreshPage()"></i>
        </div>
    </div>

    <div class="p-4 md:p-8 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-10 menu-grid justify-items-center max-w-4xl mx-auto">
            <a href="tata_usaha.php" style="animation-delay: 0.1s" 
               class="animated-button rounded-2xl p-4 md:p-8 flex flex-col items-center justify-center hover:bg-blue-50 transition-all">
                <i class="fas fa-book text-3xl md:text-4xl text-blue-600 mb-2 md:mb-4 hover:scale-125 transition-transform"></i>
                <span class="font-semibold text-blue-800 text-center text-sm md:text-base">TATA USAHA</span>
            </a>

            <a href="profil.php" style="animation-delay: 0.4s"
               class="animated-button rounded-2xl p-4 md:p-8 flex flex-col items-center justify-center hover:bg-yellow-50 transition-all">
                <i class="fas fa-user-circle text-3xl md:text-4xl text-amber-500 mb-2 md:mb-4 hover:scale-125 transition-transform"></i>
                <span class="font-semibold text-black-700 text-center text-sm md:text-base">PROFIL</span>
            </a>

            <a href="logout.php" style="animation-delay: 0.5s"
               class="animated-button rounded-2xl p-4 md:p-8 flex flex-col items-center justify-center hover:bg-red-50 transition-all">
                <i class="fas fa-power-off text-3xl md:text-4xl text-red-500 mb-2 md:mb-4 hover:scale-125 transition-transform"></i>
                <span class="font-semibold text-black-700 text-center text-sm md:text-base">LOG OUT</span>
            </a>
        </div>
    </div>
</body>
</html>