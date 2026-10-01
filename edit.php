<?php

header("Content-Type: application/json");

include "koneksi.php";


/*
|--------------------------------------------------------------------------
| TOKEN FONNTE
|--------------------------------------------------------------------------
| Gunakan TOKEN FONNTE BARU milik kamu.
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
| AMBIL DATA JSON
|--------------------------------------------------------------------------
*/

$input = json_decode(
    file_get_contents("php://input"),
    true
);


/*
|--------------------------------------------------------------------------
| VALIDASI DATA
|--------------------------------------------------------------------------
*/

$id      = intval($input["id"] ?? 0);
$name    = trim($input["name"] ?? "");
$nisn    = trim($input["nisn"] ?? "");
$ttl     = trim($input["ttl"] ?? "");
$gender  = trim($input["gender"] ?? "");
$email   = trim($input["email"] ?? "");
$address = trim($input["address"] ?? "");


if ($id <= 0) {

    echo json_encode([
        "status" => "error",
        "pesan" => "ID user tidak valid."
    ]);

    exit;
}


if (
    $name === "" ||
    $nisn === "" ||
    $ttl === "" ||
    $gender === "" ||
    $email === "" ||
    $address === ""
) {

    echo json_encode([
        "status" => "error",
        "pesan" => "Semua data wajib diisi."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CEK USER
|--------------------------------------------------------------------------
|
| Kita mengambil nomor WhatsApp dari user berdasarkan ID.
|
*/

$cek = mysqli_prepare(
    $conn,
    "SELECT id, name, whatsapp
     FROM users
     WHERE id = ?
     LIMIT 1"
);


mysqli_stmt_bind_param(
    $cek,
    "i",
    $id
);


mysqli_stmt_execute($cek);


$resultCek = mysqli_stmt_get_result($cek);


if (mysqli_num_rows($resultCek) === 0) {

    mysqli_stmt_close($cek);

    echo json_encode([
        "status" => "error",
        "pesan" => "Data user tidak ditemukan."
    ]);

    exit;
}


$userLama = mysqli_fetch_assoc($resultCek);


$whatsapp = $userLama["whatsapp"];


mysqli_stmt_close($cek);


/*
|--------------------------------------------------------------------------
| CEK EMAIL
|--------------------------------------------------------------------------
|
| Email tidak boleh digunakan oleh user lain.
|
*/

$cekEmail = mysqli_prepare(
    $conn,
    "SELECT id
     FROM users
     WHERE email = ?
     AND id != ?
     LIMIT 1"
);


mysqli_stmt_bind_param(
    $cekEmail,
    "si",
    $email,
    $id
);


mysqli_stmt_execute($cekEmail);


$resultEmail = mysqli_stmt_get_result($cekEmail);


if (mysqli_num_rows($resultEmail) > 0) {

    mysqli_stmt_close($cekEmail);

    echo json_encode([
        "status" => "error",
        "pesan" => "Email sudah digunakan oleh user lain."
    ]);

    exit;
}


mysqli_stmt_close($cekEmail);


/*
|--------------------------------------------------------------------------
| UPDATE DATA
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET
        name = ?,
        nisn = ?,
        ttl = ?,
        gender = ?,
        email = ?,
        address = ?
     WHERE id = ?"
);


mysqli_stmt_bind_param(
    $stmt,
    "ssssssi",
    $name,
    $nisn,
    $ttl,
    $gender,
    $email,
    $address,
    $id
);


/*
|--------------------------------------------------------------------------
| EKSEKUSI UPDATE
|--------------------------------------------------------------------------
*/

if (mysqli_stmt_execute($stmt)) {

    /*
    |--------------------------------------------------------------------------
    | PESAN WHATSAPP
    |--------------------------------------------------------------------------
    */

    $pesanWhatsApp =
        "🔔 *DATA AKUN DIPERBARUI*\n\n" .

        "Halo *" . $name . "* 👋\n\n" .

        "Data akun kamu di sistem *Ujian ASTS* telah berhasil diperbarui.\n\n" .

        "📋 *Data Terbaru*\n" .
        "━━━━━━━━━━━━━━━━\n" .

        "👤 Nama : " . $name . "\n" .
        "🆔 NISN : " . $nisn . "\n" .
        "📅 TTL : " . $ttl . "\n" .
        "⚧ Gender : " . $gender . "\n" .
        "📧 Email : " . $email . "\n" .
        "🏠 Alamat : " . $address . "\n\n" .

        "✅ Perubahan data telah berhasil disimpan.\n\n" .

        "Jika kamu tidak melakukan perubahan ini, segera hubungi administrator.";


    /*
    |--------------------------------------------------------------------------
    | KIRIM WHATSAPP
    |--------------------------------------------------------------------------
    */

    $hasilWhatsApp = null;

    if (!empty($whatsapp)) {

        $hasilWhatsApp = kirimWhatsAppFonnte(
            $FONNTE_TOKEN,
            $whatsapp,
            $pesanWhatsApp
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        "status" => "ok",

        "pesan" => "Data berhasil diubah.",

        "whatsapp" => !empty($whatsapp)

    ]);

}

else {

    echo json_encode([

        "status" => "error",

        "pesan" => "Gagal mengubah data: " .
                   mysqli_error($conn)

    ]);
}


mysqli_stmt_close($stmt);

?>