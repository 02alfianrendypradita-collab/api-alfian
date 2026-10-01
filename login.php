<?php

session_start();

include "koneksi.php";

$error = "";
$success = "";

// ========================================
// TOKEN FONNTE
// ========================================

$FONNTE_TOKEN = "pvrvU839HCM8Bsw3oGTr";

// ========================================
// FUNCTION KIRIM WHATSAPP
// ========================================

function kirimWhatsAppFonnte($token, $target, $message)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => "POST",

        CURLOPT_POSTFIELDS => [
            "target" => $target,
            "message" => $message,
            "countryCode" => "62"
        ],

        CURLOPT_HTTPHEADER => [
            "Authorization: " . $token
        ],
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {

        $error = curl_error($curl);

        curl_close($curl);

        return [
            "status" => false,
            "message" => $error
        ];
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    return [
        "status" => true,
        "http_code" => $httpCode,
        "response" => json_decode($response, true)
    ];
}

// ========================================
// PESAN SETELAH REGISTER
// ========================================

if (
    isset($_GET["register"]) &&
    $_GET["register"] === "success"
) {
    $success = "Registrasi berhasil! Silakan login dengan akun baru kamu.";
}

// ========================================
// PROSES LOGIN
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // ========================================
    // VALIDASI
    // ========================================

    if ($email === "" || $password === "") {

        $error = "Email dan password wajib diisi.";

    } else {

        // ========================================
        // CARI USER
        // ========================================

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password, whatsapp
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {

            $error = "Terjadi kesalahan pada sistem.";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $email
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            // ========================================
            // USER DITEMUKAN
            // ========================================

            if ($user = mysqli_fetch_assoc($result)) {

                // ========================================
                // CEK PASSWORD
                // ========================================

                if (
                    password_verify(
                        $password,
                        $user["password"]
                    )
                ) {

                    // ========================================
                    // REGENERATE SESSION
                    // ========================================

                    session_regenerate_id(true);

                    // ========================================
                    // SIMPAN SESSION USER
                    // ========================================

                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_email"] = $user["email"];

                    // ========================================
                    // NOTIFIKASI WHATSAPP
                    // ========================================

                    $whatsapp = trim(
                        $user["whatsapp"] ?? ""
                    );

                    $pesanWhatsApp =
                        "🔐 *LOGIN BERHASIL*\n\n" .
                        "Halo *" .
                        $user["name"] .
                        "* 👋\n\n" .

                        "Kami mendeteksi bahwa akun kamu berhasil login ke sistem *Ujian ASTS*.\n\n" .

                        "📋 *Detail Login*\n" .
                        "━━━━━━━━━━━━━━━━\n" .

                        "👤 Nama : " .
                        $user["name"] .
                        "\n" .

                        "📧 Email : " .
                        $user["email"] .
                        "\n" .

                        "🕐 Waktu : " .
                        date("d-m-Y H:i:s") .
                        "\n\n" .

                        "✅ Status : *LOGIN BERHASIL*\n\n" .

                        "Jika kamu tidak merasa melakukan login ini, segera hubungi administrator.";

                    // ========================================
                    // KIRIM WHATSAPP KE NOMOR USER
                    // ========================================

                    if ($whatsapp !== "") {

                        kirimWhatsAppFonnte(
                            $FONNTE_TOKEN,
                            $whatsapp,
                            $pesanWhatsApp
                        );
                    }

                    // ========================================
                    // LOGIN BERHASIL
                    // ARAHKAN KE INDEX.HTML
                    // ========================================

                    header("Location: index.html");
                    exit;

                } else {

                    $error = "Email atau password salah.";
                }

            } else {

                $error = "Email atau password salah.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Ujian ASTS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;
        }

        /* =================================
           BODY
        ================================= */

        body {

            min-height: 100vh;

            overflow: hidden;

            display: flex;

            align-items: center;

            justify-content: center;

            background:

                radial-gradient(
                    circle at 10% 10%,
                    #ffd6ea 0%,
                    transparent 30%
                ),

                radial-gradient(
                    circle at 90% 90%,
                    #f5d5ff 0%,
                    transparent 30%
                ),

                linear-gradient(
                    135deg,
                    #fff5fa,
                    #ffffff
                );
        }

        /* =================================
           BACKGROUND
        ================================= */

        .background {

            position: fixed;

            inset: 0;

            overflow: hidden;

            pointer-events: none;
        }

        .circle {

            position: absolute;

            border-radius: 50%;

            filter: blur(3px);

            opacity: .55;

            animation:
                floating
                7s
                ease-in-out
                infinite;
        }

        .circle-1 {

            width: 260px;

            height: 260px;

            background: #f9a8d4;

            top: -100px;

            left: -80px;
        }

        .circle-2 {

            width: 350px;

            height: 350px;

            background: #e9d5ff;

            bottom: -170px;

            right: -100px;

            animation-delay: 2s;
        }

        .circle-3 {

            width: 100px;

            height: 100px;

            background: #fbcfe8;

            top: 20%;

            right: 15%;

            animation-delay: 1s;
        }

        .circle-4 {

            width: 70px;

            height: 70px;

            background: #ddd6fe;

            bottom: 20%;

            left: 12%;

            animation-delay: 3s;
        }

        @keyframes floating {

            0%,
            100% {

                transform:
                    translateY(0)
                    translateX(0);
            }

            50% {

                transform:
                    translateY(-25px)
                    translateX(15px);
            }
        }

        /* =================================
           LOGIN CONTAINER
        ================================= */

        .login-container {

            position: relative;

            z-index: 5;

            width: 950px;

            max-width: 92%;

            min-height: 580px;

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            background:
                rgba(255, 255, 255, .78);

            backdrop-filter:
                blur(25px);

            -webkit-backdrop-filter:
                blur(25px);

            border:
                1px solid
                rgba(255, 255, 255, .9);

            border-radius: 30px;

            overflow: hidden;

            box-shadow:
                0 30px 80px
                rgba(190, 24, 93, .15);

            animation:
                showLogin
                .8s
                ease;
        }

        @keyframes showLogin {

            from {

                opacity: 0;

                transform:
                    translateY(35px)
                    scale(.96);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0)
                    scale(1);
            }
        }

        /* =================================
           LEFT SIDE
        ================================= */

        .left-side {

            position: relative;

            overflow: hidden;

            padding: 55px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            color: white;

            background:

                linear-gradient(
                    145deg,
                    #ec4899,
                    #d946ef
                );
        }

        .left-side::before {

            content: "";

            position: absolute;

            width: 350px;

            height: 350px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.10);

            top: -150px;

            right: -130px;
        }

        .left-side::after {

            content: "";

            position: absolute;

            width: 220px;

            height: 220px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.08);

            bottom: -100px;

            left: -80px;
        }

        /* =================================
           LOGO
        ================================= */

        .logo {

            position: relative;

            z-index: 2;

            width: 70px;

            height: 70px;

            border-radius: 22px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 34px;

            margin-bottom: 30px;

            background:
                rgba(255,255,255,.18);

            border:
                1px solid
                rgba(255,255,255,.3);

            box-shadow:
                0 15px 30px
                rgba(0,0,0,.12);

            animation:
                logoFloat
                4s
                ease-in-out
                infinite;
        }

        @keyframes logoFloat {

            0%,
            100% {

                transform:
                    translateY(0)
                    rotate(0deg);
            }

            50% {

                transform:
                    translateY(-8px)
                    rotate(3deg);
            }
        }

        .left-side h1 {

            position: relative;

            z-index: 2;

            font-size: 45px;

            line-height: 1.15;

            margin-bottom: 20px;
        }

        .left-side h1 span {

            color: #ffe4f1;
        }

        .description {

            position: relative;

            z-index: 2;

            max-width: 400px;

            color:
                rgba(255,255,255,.88);

            font-size: 16px;

            line-height: 1.8;
        }

        /* =================================
           FEATURES
        ================================= */

        .features {

            position: relative;

            z-index: 2;

            margin-top: 35px;

            display: flex;

            flex-direction: column;

            gap: 15px;
        }

        .feature {

            display: flex;

            align-items: center;

            gap: 12px;

            color:
                rgba(255,255,255,.92);

            font-size: 14px;
        }

        .check {

            width: 30px;

            height: 30px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background:
                rgba(255,255,255,.17);

            border:
                1px solid
                rgba(255,255,255,.15);
        }

        /* =================================
           RIGHT SIDE
        ================================= */

        .right-side {

            padding: 55px;

            display: flex;

            flex-direction: column;

            justify-content: center;
        }

        .mobile-logo {

            display: none;

            text-align: center;

            margin-bottom: 25px;

            color: #db2777;

            font-size: 26px;

            font-weight: 800;
        }

        .right-side h2 {

            font-size: 34px;

            color: #18181b;

            margin-bottom: 8px;
        }

        .subtitle {

            color: #71717a;

            font-size: 14px;

            margin-bottom: 25px;
        }

        /* =================================
           SUCCESS MESSAGE
        ================================= */

        .success-message {

            padding: 13px 15px;

            margin-bottom: 20px;

            border-radius: 12px;

            background: #f0fdf4;

            border:
                1px solid
                #bbf7d0;

            color: #15803d;

            font-size: 13px;

            animation:
                messageShow
                .4s
                ease;
        }

        /* =================================
           ERROR MESSAGE
        ================================= */

        .error-message {

            padding: 13px 15px;

            margin-bottom: 20px;

            border-radius: 12px;

            background: #fff1f2;

            border:
                1px solid
                #fecdd3;

            color: #e11d48;

            font-size: 13px;

            animation:
                messageShow
                .4s
                ease;
        }

        @keyframes messageShow {

            from {

                opacity: 0;

                transform:
                    translateY(-8px);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0);
            }
        }

        /* =================================
           FORM
        ================================= */

        .form-group {

            margin-bottom: 20px;
        }

        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #3f3f46;

            font-size: 14px;

            font-weight: 600;
        }

        .input-box {

            position: relative;
        }

        .input-icon {

            position: absolute;

            left: 15px;

            top: 50%;

            transform:
                translateY(-50%);

            font-size: 18px;

            opacity: .7;
        }

        .input-box input {

            width: 100%;

            padding:
                15px 45px;

            border:
                1px solid
                #e4e4e7;

            border-radius: 13px;

            outline: none;

            background:
                rgba(255,255,255,.85);

            color: #18181b;

            font-size: 14px;

            transition: .3s;
        }

        .input-box input::placeholder {

            color: #a1a1aa;
        }

        .input-box input:focus {

            border-color: #ec4899;

            box-shadow:
                0 0 0 4px
                rgba(236,72,153,.10);

            transform:
                translateY(-2px);
        }

        /* =================================
           PASSWORD BUTTON
        ================================= */

        .password-button {

            position: absolute;

            right: 14px;

            top: 50%;

            transform:
                translateY(-50%);

            border: none;

            background: transparent;

            cursor: pointer;

            font-size: 17px;

            opacity: .55;

            transition: .2s;
        }

        .password-button:hover {

            opacity: 1;
        }

        /* =================================
           REMEMBER
        ================================= */

        .remember {

            display: flex;

            align-items: center;

            justify-content:
                space-between;

            margin:
                5px 0 25px;

            font-size: 13px;

            color: #71717a;
        }

        .remember label {

            display: flex;

            align-items: center;

            gap: 8px;

            cursor: pointer;
        }

        .remember input {

            width: 15px;

            height: 15px;

            accent-color:
                #ec4899;
        }

        .forgot {

            color: #db2777;

            text-decoration: none;

            font-weight: 600;
        }

        .forgot:hover {

            color: #be185d;
        }

        /* =================================
           LOGIN BUTTON
        ================================= */

        .login-button {

            width: 100%;

            border: none;

            padding: 16px;

            border-radius: 13px;

            background:

                linear-gradient(
                    135deg,
                    #ec4899,
                    #c026d3
                );

            color: white;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 12px 25px
                rgba(219,39,119,.25);

            transition: .3s;
        }

        .login-button:hover {

            transform:
                translateY(-3px);

            box-shadow:
                0 18px 35px
                rgba(219,39,119,.35);
        }

        .login-button:active {

            transform:
                scale(.98);
        }

        /* =================================
           REGISTER
        ================================= */

        .register {

            margin-top: 25px;

            text-align: center;

            color: #71717a;

            font-size: 14px;
        }

        .register a {

            color: #db2777;

            font-weight: 700;

            text-decoration: none;
        }

        .register a:hover {

            text-decoration: underline;
        }

        /* =================================
           FOOTER
        ================================= */

        .footer {

            margin-top: 30px;

            text-align: center;

            color: #a1a1aa;

            font-size: 11px;
        }

        /* =================================
           RESPONSIVE
        ================================= */

        @media (max-width: 800px) {

            body {

                overflow-y: auto;

                padding: 20px;
            }

            .login-container {

                grid-template-columns: 1fr;

                min-height: auto;

                max-width: 500px;
            }

            .left-side {

                display: none;
            }

            .right-side {

                padding: 40px 28px;
            }

            .mobile-logo {

                display: block;
            }
        }

        @media (max-width: 400px) {

            .right-side {

                padding: 35px 20px;
            }

            .right-side h2 {

                font-size: 29px;
            }
        }

    </style>

</head>

<body>

    <!-- =================================
         BACKGROUND
    ================================= -->

    <div class="background">

        <div class="circle circle-1"></div>

        <div class="circle circle-2"></div>

        <div class="circle circle-3"></div>

        <div class="circle circle-4"></div>

    </div>


    <!-- =================================
         LOGIN CONTAINER
    ================================= -->

    <div class="login-container">


        <!-- =================================
             LEFT SIDE
        ================================= -->

        <div class="left-side">

            <div class="logo">
                ✨
            </div>

            <h1>

                Selamat<br>

                <span>
                    Datang Kembali!
                </span>

            </h1>

            <p class="description">

                Kelola data dengan lebih mudah,
                cepat, dan terorganisir melalui
                sistem informasi yang modern.

            </p>


            <div class="features">

                <div class="feature">

                    <div class="check">
                        ✓
                    </div>

                    Sistem data yang mudah digunakan

                </div>


                <div class="feature">

                    <div class="check">
                        ✓
                    </div>

                    Tampilan modern dan responsif

                </div>


                <div class="feature">

                    <div class="check">
                        ✓
                    </div>

                    Data tersimpan dengan aman

                </div>

            </div>

        </div>


        <!-- =================================
             RIGHT SIDE
        ================================= -->

        <div class="right-side">


            <div class="mobile-logo">
                ✨ Ujian ASTS
            </div>


            <h2>
                Login
            </h2>

            <p class="subtitle">
                Silakan masuk untuk melanjutkan
            </p>


            <!-- SUCCESS -->

            <?php if ($success !== ""): ?>

                <div class="success-message">

                    ✓

                    <?= htmlspecialchars($success) ?>

                </div>

            <?php endif; ?>


            <!-- ERROR -->

            <?php if ($error !== ""): ?>

                <div class="error-message">

                    ⚠️

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                action="login.php"
                method="POST"
            >


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>


                    <div class="input-box">

                        <span class="input-icon">
                            ✉️
                        </span>


                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Masukkan email"
                            value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>


                    <div class="input-box">

                        <span class="input-icon">
                            🔒
                        </span>


                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            required
                        >


                        <button
                            type="button"
                            class="password-button"
                            onclick="togglePassword()"
                            id="togglePassword"
                        >
                            👁️
                        </button>

                    </div>

                </div>


                <!-- REMEMBER -->

                <div class="remember">

                    <label>

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        Ingat saya

                    </label>


                    <a
                        href="#"
                        class="forgot"
                        onclick="return false;"
                    >
                        Lupa password?
                    </a>

                </div>


                <!-- BUTTON -->

<button
    type="submit"
    class="login-button"
>
    Masuk ke Sistem →
</button>


            </form>


            <!-- REGISTER -->

            <div class="register">

                Belum memiliki akun?

                <a href="register.php">
                    Daftar sekarang
                </a>

            </div>


            <!-- FOOTER -->

            <div class="footer">

                © 2026 Ujian ASTS · All Rights Reserved

            </div>


        </div>

    </div>


    <!-- =================================
         JAVASCRIPT
    ================================= -->

    <script>

        function togglePassword() {

            const password =
                document.getElementById("password");

            const button =
                document.getElementById("togglePassword");


            if (password.type === "password") {

                password.type = "text";

                button.textContent = "🙈";

            } else {

                password.type = "password";

                button.textContent = "👁️";
            }

        }

    </script>

</body>

</html>