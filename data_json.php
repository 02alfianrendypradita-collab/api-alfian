<?php

include 'koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// ==========================================
// PARAMETER PAGINATION
// ==========================================

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

if ($page < 1) {
    $page = 1;
}

if ($limit < 1) {
    $limit = 10;
}

$offset = ($page - 1) * $limit;


// ==========================================
// HITUNG TOTAL DATA
// ==========================================

$totalResult = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
);

$total = 0;

if ($totalResult) {
    $rowTotal = $totalResult->fetch_assoc();
    $total = (int) $rowTotal['total'];
}

$totalPages = $total > 0
    ? ceil($total / $limit)
    : 1;


// ==========================================
// AMBIL DATA USERS
// ==========================================

$sql = "
    SELECT
        id,
        name,
        nisn,
        ttl,
        gender,
        email,
        whatsapp,
        address
    FROM users
    ORDER BY id ASC
    LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

$data = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $data[] = [
            "id"       => $row["id"],
            "name"     => $row["name"],
            "nisn"     => $row["nisn"],
            "ttl"      => $row["ttl"],
            "gender"   => $row["gender"],
            "email"    => $row["email"],
            "whatsapp" => $row["whatsapp"],
            "address"  => $row["address"]
        ];
    }
}


// ==========================================
// RESPONSE JSON
// ==========================================

$response = [
    "status"     => "success",
    "page"       => $page,
    "limit"      => $limit,
    "total"      => $total,
    "totalPages" => $totalPages,
    "data"       => $data
];


// ==========================================
// OUTPUT JSON
// ==========================================

echo json_encode(
    $response,
    JSON_PRETTY_PRINT |
    JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE
);

?>