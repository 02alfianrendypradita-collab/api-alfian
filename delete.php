<?php

header("Content-Type: application/json");

include "koneksi.php";

// ===============================
// TOKEN FONNTE
// ===============================
$FONNTE_TOKEN = "pvrvU839HCM8Bsw3oGTr";

// ===============================
// FUNCTION KIRIM WHATSAPP
// ===============================
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


// ===============================
// AMBIL ID
// ===============================
$input = json_decode(file_get_contents("php://input"), true);

$id = intval($input["id"] ?? 0);

if ($id <= 0) {
    echo json_encode([
        "status" => "error",
        "pesan" => "ID user tidak valid."
    ]);
    exit;
}


// ===============================
// AMBIL DATA USER SEBELUM DIHAPUS
// ===============================
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, nisn, whatsapp
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "status" => "error",
        "pesan" => "Data user tidak ditemukan."
    ]);

    exit;
}

$user = mysqli_fetch_assoc($result);

$name = $user["name"];
$nisn = $user["nisn"];
$whatsapp = trim($user["whatsapp"] ?? "");

mysqli_stmt_close($stmt);


// ===============================
// HAPUS DATA
// ===============================
$delete = mysqli_prepare(
    $conn,
    "DELETE FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($delete, "i", $id);

if (!mysqli_stmt_execute($delete)) {

    echo json_encode([
        "status" => "error",
        "pesan" => "Gagal menghapus data: " . mysqli_error($conn)
    ]);

    mysqli_stmt_close($delete);
    exit;
}

mysqli_stmt_close($delete);


// ===============================
// PESAN WHATSAPP
// ===============================
$pesanWhatsApp =
    "🔔 *PEMBERITAHUAN AKUN*\n\n" .

    "Halo *" . $name . "* 👋\n\n" .

    "Data akun kamu pada sistem *Ujian ASTS* telah dihapus oleh administrator.\n\n" .

    "📋 *Data Akun*\n" .
    "━━━━━━━━━━━━━━━━\n" .

    "👤 Nama : " . $name . "\n" .
    "🆔 NISN : " . $nisn . "\n\n" .

    "🗑️ Status : *DATA DIHAPUS*\n\n" .

    "Akun tersebut sudah tidak dapat digunakan untuk login ke sistem Ujian ASTS.\n\n" .

    "Jika kamu merasa tidak melakukan permintaan penghapusan ini, silakan hubungi administrator.";

    
// ===============================
// KIRIM WHATSAPP
// ===============================
if ($whatsapp === "") {

    echo json_encode([
        "status" => "ok",
        "pesan" => "Data berhasil dihapus, tetapi nomor WhatsApp tidak tersedia.",
        "whatsapp" => false
    ]);

    exit;
}


$hasilWhatsApp = kirimWhatsAppFonnte(
    $FONNTE_TOKEN,
    $whatsapp,
    $pesanWhatsApp
);


// ===============================
// CEK HASIL WHATSAPP
// ===============================
if (!$hasilWhatsApp["status"]) {

    echo json_encode([
        "status" => "ok",
        "pesan" => "Data berhasil dihapus, tetapi notifikasi WhatsApp gagal dikirim.",
        "whatsapp" => false,
        "error" => $hasilWhatsApp["message"]
    ]);

    exit;
}


// ===============================
// BERHASIL
// ===============================
echo json_encode([
    "status" => "ok",
    "pesan" => "Data berhasil dihapus dan notifikasi WhatsApp berhasil dikirim.",
    "whatsapp" => true
]);

?>