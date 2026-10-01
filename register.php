<?php

include "koneksi.php";

$error = "";

/*
|--------------------------------------------------------------------------
| KONFIGURASI FONNTE
|--------------------------------------------------------------------------
| Masukkan TOKEN FONNTE BARU milik kamu di bawah ini.
|
| JANGAN masukkan token ke GitHub atau membagikannya ke orang lain.
|--------------------------------------------------------------------------
*/

$FONNTE_TOKEN = "pvrvU839HCM8Bsw3oGTr";


/*
|--------------------------------------------------------------------------
| FUNCTION KIRIM WHATSAPP
|--------------------------------------------------------------------------
*/

function kirimWhatsAppFonnte($token, $target, $message)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_ENCODING => "",

        CURLOPT_MAXREDIRS => 10,

        CURLOPT_TIMEOUT => 30,

        CURLOPT_FOLLOWLOCATION => true,

        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

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

    /*
    |--------------------------------------------------------------------------
    | CEK ERROR CURL
    |--------------------------------------------------------------------------
    */

    if (curl_errno($curl)) {

        $error = curl_error($curl);

        curl_close($curl);

        return [
            "status" => false,
            "message" => $error
        ];
    }

    curl_close($curl);

    return [
        "status" => true,
        "response" => $response
    ];
}


/*
|--------------------------------------------------------------------------
| PROSES REGISTER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA FORM
    |--------------------------------------------------------------------------
    */

    $name     = trim($_POST["name"] ?? "");
    $nisn     = trim($_POST["nisn"] ?? "");
    $ttl      = trim($_POST["ttl"] ?? "");
    $gender   = trim($_POST["gender"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $whatsapp = trim($_POST["whatsapp"] ?? "");
    $password = $_POST["password"] ?? "";
    $address  = trim($_POST["address"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | VALIDASI DATA
    |--------------------------------------------------------------------------
    */

    if (
        $name === "" ||
        $nisn === "" ||
        $ttl === "" ||
        $gender === "" ||
        $email === "" ||
        $whatsapp === "" ||
        $password === "" ||
        $address === ""
    ) {

        $error = "Semua data wajib diisi.";

    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI EMAIL
    |--------------------------------------------------------------------------
    */

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI PASSWORD
    |--------------------------------------------------------------------------
    */

    elseif (strlen($password) < 6) {

        $error = "Password minimal 6 karakter.";

    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI WHATSAPP
    |--------------------------------------------------------------------------
    */

    elseif (!preg_match('/^08[0-9]{8,13}$/', $whatsapp)) {

        $error = "Nomor WhatsApp harus diawali 08 dan hanya berisi angka.";

    }

    else {

        /*
        |--------------------------------------------------------------------------
        | CEK EMAIL SUDAH TERDAFTAR ATAU BELUM
        |--------------------------------------------------------------------------
        */

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $email
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);


        if (mysqli_num_rows($result) > 0) {

            $error = "Email sudah terdaftar.";

        }

        else {

            /*
            |--------------------------------------------------------------------------
            | HASH PASSWORD
            |--------------------------------------------------------------------------
            */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATA USER
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (
                    name,
                    nisn,
                    ttl,
                    gender,
                    email,
                    whatsapp,
                    password,
                    address
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );


            mysqli_stmt_bind_param(
                $stmt,
                "ssssssss",
                $name,
                $nisn,
                $ttl,
                $gender,
                $email,
                $whatsapp,
                $hashedPassword,
                $address
            );


            /*
            |--------------------------------------------------------------------------
            | EKSEKUSI INSERT
            |--------------------------------------------------------------------------
            */

            if (mysqli_stmt_execute($stmt)) {

                /*
                |--------------------------------------------------------------------------
                | BUAT PESAN WHATSAPP
                |--------------------------------------------------------------------------
                */

                $pesanWhatsApp =
                    "🎉 *REGISTRASI BERHASIL*\n\n" .

                    "Halo *" . $name . "* 👋\n\n" .

                    "Akun kamu berhasil didaftarkan pada sistem *Ujian ASTS*.\n\n" .

                    "📋 *Data Registrasi*\n" .
                    "━━━━━━━━━━━━━━━━\n" .

                    "👤 Nama : " . $name . "\n" .
                    "🆔 NISN : " . $nisn . "\n" .
                    "📧 Email : " . $email . "\n" .
                    "📱 WhatsApp : " . $whatsapp . "\n\n" .

                    "🔐 Password kamu telah disimpan secara aman.\n\n" .

                    "Silakan login untuk melanjutkan ke sistem Ujian ASTS.\n\n" .

                    "✨ Terima kasih telah melakukan registrasi.";


                /*
                |--------------------------------------------------------------------------
                | KIRIM NOTIFIKASI WHATSAPP
                |--------------------------------------------------------------------------
                |
                | $whatsapp berasal dari input masing-masing user.
                | Jadi setiap user akan mendapatkan notifikasi ke nomor
                | WhatsApp mereka sendiri.
                |--------------------------------------------------------------------------
                */

                kirimWhatsAppFonnte(
                    $FONNTE_TOKEN,
                    $whatsapp,
                    $pesanWhatsApp
                );


                /*
                |--------------------------------------------------------------------------
                | TUTUP STATEMENT
                |--------------------------------------------------------------------------
                */

                mysqli_stmt_close($stmt);

                mysqli_stmt_close($check);


                /*
                |--------------------------------------------------------------------------
                | REDIRECT KE LOGIN
                |--------------------------------------------------------------------------
                */

                header(
                    "Location: login.php?register=success"
                );

                exit;

            }

            else {

                $error =
                    "Registrasi gagal: " .
                    mysqli_error($conn);

                mysqli_stmt_close($stmt);
            }
        }


        if (isset($check)) {

            mysqli_stmt_close($check);
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

    <title>Register | Ujian ASTS</title>


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


        body {

            min-height: 100vh;

            display: flex;

            align-items: center;
            justify-content: center;

            padding: 30px 20px;

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


        .container {

            width: 950px;

            max-width: 95%;

            display: grid;

            grid-template-columns:
                0.8fr
                1.2fr;

            background:
                rgba(255,255,255,.9);

            border-radius: 28px;

            overflow: hidden;

            box-shadow:
                0 25px 70px
                rgba(190,24,93,.15);

            animation:
                muncul .7s ease;
        }


        @keyframes muncul {

            from {

                opacity: 0;

                transform:
                    translateY(30px)
                    scale(.97);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0)
                    scale(1);
            }
        }


        /* =====================
           LEFT
        ===================== */

        .left {

            padding: 50px;

            color: white;

            background:
                linear-gradient(
                    145deg,
                    #ec4899,
                    #c026d3
                );

            display: flex;

            flex-direction: column;

            justify-content: center;
        }


        .logo {

            width: 65px;
            height: 65px;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                rgba(255,255,255,.2);

            border-radius: 20px;

            font-size: 30px;

            margin-bottom: 25px;
        }


        .left h1 {

            font-size: 40px;

            line-height: 1.15;

            margin-bottom: 20px;
        }


        .left h1 span {

            color: #ffe4f1;
        }


        .left p {

            line-height: 1.8;

            color:
                rgba(255,255,255,.88);
        }


        .features {

            margin-top: 30px;

            display: flex;

            flex-direction: column;

            gap: 14px;
        }


        .feature {

            display: flex;

            align-items: center;

            gap: 10px;

            font-size: 14px;
        }


        .check {

            width: 28px;
            height: 28px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background:
                rgba(255,255,255,.18);
        }


        /* =====================
           RIGHT
        ===================== */

        .right {

            padding: 40px 50px;
        }


        .right h2 {

            font-size: 30px;

            color: #18181b;

            margin-bottom: 7px;
        }


        .subtitle {

            color: #71717a;

            font-size: 14px;

            margin-bottom: 25px;
        }


        /* ERROR */

        .error {

            padding: 12px 15px;

            margin-bottom: 18px;

            border-radius: 10px;

            background: #fff1f2;

            border:
                1px solid
                #fecdd3;

            color: #e11d48;

            font-size: 13px;
        }


        /* FORM */

        .form-grid {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 15px;
        }


        .full {

            grid-column:
                1 / -1;
        }


        label {

            display: block;

            margin-bottom: 7px;

            color: #3f3f46;

            font-size: 13px;

            font-weight: 600;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 12px 14px;

            border:
                1px solid
                #e4e4e7;

            border-radius: 11px;

            outline: none;

            font-size: 13px;

            background: white;

            transition: .3s;
        }


        textarea {

            resize: vertical;

            min-height: 70px;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #ec4899;

            box-shadow:
                0 0 0 4px
                rgba(236,72,153,.1);
        }


        /* BUTTON */

        .button {

            width: 100%;

            margin-top: 22px;

            padding: 14px;

            border: none;

            border-radius: 12px;

            color: white;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            background:
                linear-gradient(
                    135deg,
                    #ec4899,
                    #c026d3
                );

            box-shadow:
                0 10px 25px
                rgba(219,39,119,.25);

            transition: .3s;
        }


        .button:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 15px 30px
                rgba(219,39,119,.35);
        }


        .login {

            text-align: center;

            margin-top: 20px;

            color: #71717a;

            font-size: 13px;
        }


        .login a {

            color: #db2777;

            font-weight: 700;

            text-decoration: none;
        }


        .login a:hover {

            text-decoration: underline;
        }


        /* MOBILE */

        @media(max-width: 800px) {

            .container {

                grid-template-columns: 1fr;

                max-width: 600px;
            }


            .left {

                display: none;
            }


            .right {

                padding: 35px 25px;
            }
        }


        @media(max-width: 500px) {

            .form-grid {

                grid-template-columns: 1fr;
            }


            .full {

                grid-column: auto;
            }
        }

    </style>

</head>


<body>


<div class="container">


    <!-- =====================
         LEFT
    ====================== -->

    <div class="left">

        <div class="logo">
            ✨
        </div>


        <h1>

            Buat Akun

            <br>

            <span>Baru Sekarang!</span>

        </h1>


        <p>

            Daftarkan akun kamu untuk
            menggunakan sistem Ujian ASTS
            dengan mudah dan aman.

        </p>


        <div class="features">

            <div class="feature">

                <div class="check">
                    ✓
                </div>

                Pendaftaran cepat

            </div>


            <div class="feature">

                <div class="check">
                    ✓
                </div>

                Password terenkripsi

            </div>


            <div class="feature">

                <div class="check">
                    ✓
                </div>

                Notifikasi WhatsApp otomatis

            </div>


            <div class="feature">

                <div class="check">
                    ✓
                </div>

                Data tersimpan ke database

            </div>

        </div>

    </div>


    <!-- =====================
         RIGHT
    ====================== -->

    <div class="right">

        <h2>
            Daftar Akun
        </h2>


        <p class="subtitle">

            Lengkapi data untuk membuat akun baru

        </p>


        <?php if ($error !== ""): ?>

            <div class="error">

                ⚠️

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="register.php"
        >

            <div class="form-grid">


                <!-- NAMA -->

                <div>

                    <label>
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Masukkan nama lengkap"
                        value="<?= htmlspecialchars($_POST["name"] ?? "") ?>"
                        required
                    >

                </div>


                <!-- NISN -->

                <div>

                    <label>
                        NISN
                    </label>

                    <input
                        type="text"
                        name="nisn"
                        placeholder="Masukkan NISN"
                        value="<?= htmlspecialchars($_POST["nisn"] ?? "") ?>"
                        required
                    >

                </div>


                <!-- TTL -->

                <div>

                    <label>
                        Tempat & Tanggal Lahir
                    </label>

                    <input
                        type="text"
                        name="ttl"
                        placeholder="Contoh: Ponorogo, 10 Maret 2008"
                        value="<?= htmlspecialchars($_POST["ttl"] ?? "") ?>"
                        required
                    >

                </div>


                <!-- GENDER -->

                <div>

                    <label>
                        Jenis Kelamin
                    </label>

                    <select
                        name="gender"
                        required
                    >

                        <option value="">
                            Pilih jenis kelamin
                        </option>

                        <option
                            value="MALE"
                            <?= (($_POST["gender"] ?? "") === "MALE") ? "selected" : "" ?>
                        >
                            Laki-laki
                        </option>

                        <option
                            value="FEMALE"
                            <?= (($_POST["gender"] ?? "") === "FEMALE") ? "selected" : "" ?>
                        >
                            Perempuan
                        </option>

                    </select>

                </div>


                <!-- EMAIL -->

                <div class="full">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="contoh@email.com"
                        value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                        required
                    >

                </div>


                <!-- WHATSAPP -->

                <div class="full">

                    <label>
                        Nomor WhatsApp
                    </label>

                    <input
                        type="text"
                        name="whatsapp"
                        placeholder="Contoh: 081234567890"
                        value="<?= htmlspecialchars($_POST["whatsapp"] ?? "") ?>"
                        maxlength="15"
                        required
                    >

                    <small
                        style="
                            display:block;
                            margin-top:6px;
                            color:#71717a;
                            font-size:12px;
                        "
                    >
                        Nomor ini akan menerima notifikasi setelah registrasi berhasil.
                    </small>

                </div>


                <!-- PASSWORD -->

                <div>

                    <label>
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 6 karakter"
                        required
                    >

                </div>


                <!-- ALAMAT -->

                <div>

                    <label>
                        Alamat
                    </label>

                    <input
                        type="text"
                        name="address"
                        placeholder="Masukkan alamat"
                        value="<?= htmlspecialchars($_POST["address"] ?? "") ?>"
                        required
                    >

                </div>


            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="button"
            >

                Buat Akun ✨

            </button>


        </form>


        <!-- LOGIN -->

        <div class="login">

            Sudah memiliki akun?

            <a href="login.php">
                Login sekarang
            </a>

        </div>


    </div>


</div>


</body>

</html>