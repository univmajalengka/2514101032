
<?php

session_start();
require_once "koneksi.php";


// 1. Membuat fungsi untuk menghitung diskon
function hitungDiskon($totalBelanja) {

    if ($totalBelanja >= 100000) {
        return 10;

    } elseif ($totalBelanja >= 50000) {
        return 5;

    } else {
        return 0;
    }
}


// 2. Menyiapkan hasil perhitungan
$hasil = null;
$pesan = "";


// 3. Memproses form input
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $totalBelanja = filter_input(
        INPUT_POST,
        "total_belanja",
        FILTER_VALIDATE_INT
    );

    if ($totalBelanja === false ||
        $totalBelanja === null ||
        $totalBelanja <= 0) {

        $_SESSION["pesan"] = "Masukkan nominal belanja yang valid.";

    } else {

        // 4. Menghitung persentase diskon
        $diskon = hitungDiskon($totalBelanja);

        // 5. Menghitung nominal diskon
        $nominalDiskon = ($diskon / 100) * $totalBelanja;

        // 6. Menghitung total pembayaran
        $totalBayar = $totalBelanja - $nominalDiskon;


        // 7. Menyimpan transaksi ke database
        $sql = "INSERT INTO transaksi
                (total_belanja, diskon, nominal_diskon, total_bayar)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iiii",
            $totalBelanja,
            $diskon,
            $nominalDiskon,
            $totalBayar
        );

        if ($stmt->execute()) {

            // Menyimpan hasil sementara untuk ditampilkan
            $_SESSION["hasil"] = [
                "total_belanja" => $totalBelanja,
                "diskon" => $diskon,
                "nominal_diskon" => $nominalDiskon,
                "total_bayar" => $totalBayar
            ];

            $_SESSION["pesan"] = "Transaksi berhasil disimpan.";

        } else {

            $_SESSION["pesan"] = "Gagal menyimpan transaksi.";

        }

        $stmt->close();
    }

    // 8. Mengarahkan kembali ke halaman utama
    header("Location: index.php");
    exit();
}


// 9. Mengambil hasil perhitungan dan pesan
if (isset($_SESSION["hasil"])) {
    $hasil = $_SESSION["hasil"];
    unset($_SESSION["hasil"]);
}

if (isset($_SESSION["pesan"])) {
    $pesan = $_SESSION["pesan"];
    unset($_SESSION["pesan"]);
}


// 10. Mengambil riwayat transaksi
$sqlRiwayat = "SELECT * FROM transaksi ORDER BY id DESC LIMIT 5";
$riwayat = $conn->query($sqlRiwayat);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistem Perhitungan Diskon</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <header>
        <h1>DISCOUNT CALCULATOR</h1>
        <p>Sistem Perhitungan Diskon Belanja</p>
    </header>


    <!-- FORM INPUT -->
    <section class="card">

        <h2>Hitung Diskon</h2>

        <p class="description">
            Masukkan total belanja untuk menghitung diskon.
        </p>

        <?php if ($pesan != "") { ?>
            <div class="message">
                <?php echo htmlspecialchars($pesan); ?>
            </div>
        <?php } ?>

        <form method="POST" action="index.php">

            <label for="total_belanja">
                Total Belanja (Rp)
            </label>

            <input
                type="number"
                name="total_belanja"
                id="total_belanja"
                placeholder="Contoh: 120000"
                min="1"
                step="1"
                required
            >

            <button type="submit">
                Hitung Diskon
            </button>

        </form>

        <div class="info-diskon">

            <h3>Ketentuan Diskon</h3>

            <ul>
                <li>Belanja ≥ Rp100.000 mendapatkan diskon 10%</li>
                <li>Belanja ≥ Rp50.000 mendapatkan diskon 5%</li>
                <li>Belanja kurang dari Rp50.000 tidak mendapatkan diskon</li>
            </ul>

        </div>

    </section>


    <!-- HASIL PERHITUNGAN -->
    <?php if ($hasil !== null) { ?>

    <section class="card result-section">

        <h2>Hasil Perhitungan</h2>

        <table class="result-table">

            <tr>
                <td>Total Belanja</td>
                <td>
                    Rp<?php echo number_format($hasil["total_belanja"], 0, ',', '.'); ?>
                </td>
            </tr>

            <tr>
                <td>Persentase Diskon</td>
                <td>
                    <?php echo $hasil["diskon"]; ?>%
                </td>
            </tr>

            <tr>
                <td>Nominal Diskon</td>
                <td>
                    Rp<?php echo number_format($hasil["nominal_diskon"], 0, ',', '.'); ?>
                </td>
            </tr>

        </table>

        <div class="total-box">

            <p>Total yang Harus Dibayar</p>

            <h3>
                Rp<?php echo number_format($hasil["total_bayar"], 0, ',', '.'); ?>
            </h3>

        </div>

    </section>

    <?php } ?>


    <!-- RIWAYAT TRANSAKSI -->
    <section class="card history-section">

        <h2>Riwayat Perhitungan</h2>

        <p class="description">
            Daftar seluruh transaksi yang telah disimpan.
        </p>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Total Belanja</th>
                        <th>Diskon</th>
                        <th>Nominal Diskon</th>
                        <th>Total Bayar</th>
                        <th>Tanggal dan Waktu</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    if ($riwayat && $riwayat->num_rows > 0) {

                        $no = 1;

                        while ($row = $riwayat->fetch_assoc()) {
                    ?>

                    <tr>

                        <td><?php echo $no++; ?></td>

                        <td>
                            Rp<?php echo number_format($row["total_belanja"], 0, ',', '.'); ?>
                        </td>

                        <td>
                            <?php echo $row["diskon"]; ?>%
                        </td>

                        <td>
                            Rp<?php echo number_format($row["nominal_diskon"], 0, ',', '.'); ?>
                        </td>

                        <td>
                            Rp<?php echo number_format($row["total_bayar"], 0, ',', '.'); ?>
                        </td>

                        <td>
                            <?php echo date(
                                "d-m-Y H:i:s",
                                strtotime($row["tanggal"])
                            ); ?>
                        </td>

                    </tr>

                    <?php
                        }

                    } else {
                    ?>

                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada riwayat transaksi.
                        </td>
                    </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </section>


    <!-- FOOTER -->
    <footer>
        <p>Sistem Perhitungan Diskon &copy; 2026</p>
    </footer>

</div>

</body>
</html>

<?php

// 11. Menutup koneksi database
$conn->close();

?>
