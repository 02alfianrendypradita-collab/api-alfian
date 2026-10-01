<?php

/*
|--------------------------------------------------------------------------
| KONFIGURASI FONNTE
|--------------------------------------------------------------------------
*/

// Masukkan TOKEN Fonnte kamu di sini
$FONNTE_TOKEN = "MASUKKAN_TOKEN_FONNTE_DISINI";

// Nomor WhatsApp ADMIN yang menerima notifikasi
$FONNTE_ADMIN = "628xxxxxxxxxx";


/*
|--------------------------------------------------------------------------
| FUNGSI KIRIM WHATSAPP
|--------------------------------------------------------------------------
*/

function kirimFonnte($target, $message)
{
    global $FONNTE_TOKEN;

    if (empty($target)) {
        return false;
    }

    if (empty($FONNTE_TOKEN)) {
        return false;
    }

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
            "Authorization: " . $FONNTE_TOKEN
        ],
    ]);

    $response = curl_exec($curl);

    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return false;
    }

    return $response;
}


/*
|--------------------------------------------------------------------------
| FORMAT NOMOR WHATSAPP
|--------------------------------------------------------------------------
*/

function formatWhatsApp($nomor)
{
    $nomor = preg_replace('/[^0-9]/', '', $nomor);

    if (substr($nomor, 0, 1) === "0") {
        $nomor = "62" . substr($nomor, 1);
    }

    return $nomor;
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI REGISTER
|--------------------------------------------------------------------------
*/

function notifikasiRegister($nama, $nisn, $email, $whatsapp)
{
    global $FONNTE_ADMIN;

    $pesan = "🔔 *REGISTRASI AKUN BARU*

"
        . "Nama : " . $nama . "
"
        . "NISN : " . $nisn . "
"
        . "Email : " . $email . "
"
        . "WhatsApp : " . $whatsapp . "

"
        . "Status : ✅ Berhasil terdaftar

"
        . "Sistem Ujian ASTS";

    kirimFonnte(
        formatWhatsApp($FONNTE_ADMIN),
        $pesan
    );
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI EDIT
|--------------------------------------------------------------------------
*/

function notifikasiEdit($nama, $nisn, $email, $whatsapp)
{
    global $FONNTE_ADMIN;

    $pesan = "✏️ *DATA AKUN DIUBAH*

"
        . "Nama : " . $nama . "
"
        . "NISN : " . $nisn . "
"
        . "Email : " . $email . "
"
        . "WhatsApp : " . $whatsapp . "

"
        . "Status : 🔄 Data berhasil diperbarui

"
        . "Sistem Ujian ASTS";

    kirimFonnte(
        formatWhatsApp($FONNTE_ADMIN),
        $pesan
    );
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI DELETE
|--------------------------------------------------------------------------
*/

function notifikasiDelete($nama, $nisn, $email)
{
    global $FONNTE_ADMIN;

    $pesan = "🗑️ *DATA AKUN DIHAPUS*

"
        . "Nama : " . $nama . "
"
        . "NISN : " . $nisn . "
"
        . "Email : " . $email . "

"
        . "Status : ❌ Data telah dihapus

"
        . "Sistem Ujian ASTS";

    kirimFonnte(
        formatWhatsApp($FONNTE_ADMIN),
        $pesan
    );
}