<?php
header('Content-Type: application/json');

// PANGGIL KONEKSI DB
require 'koneksi.php';

// AMBIL VOUCHER DARI PARAMETER
$voucherCode = isset($_GET['voucherCode']) ? trim($_GET['voucherCode']) : '';

// Validasi input
if (empty($voucherCode)) {
    echo json_encode([
        'error' => true,
        'message' => 'Kode voucher wajib diisi.'
    ]);
    exit;
}

try {
    // CEK APAKAH VOUCHER DAPAT DIGUNAKAN
    $sql = "SELECT * FROM vouchers WHERE code = :voucherCode AND status = 'active' LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':voucherCode', $voucherCode);
    $stmt->execute();
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voucher) {
        echo json_encode([
            'error' => true,
            'message' => 'Voucher tidak ditemukan atau sudah digunakan.'
        ]);
        exit;
    }

    // Jika ditemukan dan aktif
    echo json_encode([
        'error' => false,
        'data'  => [
            'reward_id' => $voucher['reward_id']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Terjadi kesalahan pada server.',
        'detail' => $e->getMessage() // Hapus baris ini di production
    ]);
}
?>
