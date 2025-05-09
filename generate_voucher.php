<?php
// Koneksi database
$pdo = new PDO("mysql:host=localhost;dbname=rolet", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fungsi untuk generate kode unik tanpa duplikat
function generateVoucherCode($length = 8, $pdo) {
    do {
        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, $length));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vouchers WHERE code = ?");
        $stmt->execute([$code]);
        $count = $stmt->fetchColumn();
    } while ($count > 0); // Ulangi jika ada duplikat
    return $code;
}

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $reward_id = isset($_POST['reward_id']) ? (int)$_POST['reward_id'] : 0;
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;

    if ($reward_id <= 0) {
        echo "Reward ID tidak valid!";
        exit;
    }

    if ($jumlah <= 0 || $jumlah > 100) {
        echo "Jumlah voucher tidak valid!";
        exit;
    }

    try {
        // Insert voucher baru ke database
        for ($i = 0; $i < $jumlah; $i++) {
            $code = generateVoucherCode(8, $pdo);
            $stmt = $pdo->prepare("INSERT INTO vouchers (code, reward_id, status) VALUES (:code, :reward_id, 'active')");
            $stmt->execute([
                ':code' => $code,
                ':reward_id' => $reward_id
            ]);
        }

        // Redirect ke halaman dengan parameter sukses
        header("Location: generate_voucher.php?success=1");
        exit;
    } catch (PDOException $e) {
        // Menangani error database
        echo "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Generate Voucher | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">

<div class="container py-5">
    <h2 class="mb-4">🎁 Generate Voucher Baru</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Voucher berhasil dibuat!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-5">
        <div class="col-md-4">
            <label class="form-label">Reward ID</label>
            <input type="number" name="reward_id" class="form-control" required min="1">
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah Voucher</label>
            <input type="number" name="jumlah" class="form-control" required min="1" max="100">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Generate</button>
        </div>
    </form>

    <h4>📄 Daftar Voucher Aktif</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-dark table-striped align-middle text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Reward ID</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM vouchers ORDER BY id DESC LIMIT 100");
                foreach ($stmt as $row):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <code class="text-success"><?= $row['code'] ?></code>
                    </td>
                    <td><?= $row['reward_id'] ?></td>
                    <td>
                        <span class="badge <?= $row['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= strtoupper($row['status']) ?>
                        </span>
                    </td>
                    <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
