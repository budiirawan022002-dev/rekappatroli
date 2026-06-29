<?php
/**
 * Fungsi untuk memformat tanggal ke dalam format bahasa Indonesia.
 *
 * @param string $tanggal Tanggal dalam format Y-m-d.
 * @return string Tanggal dalam format bahasa Indonesia.
 */
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$tanggalObj) {
        return "Tanggal tidak valid";
    }
    $hari = $tanggalObj->format('d');
    $bulanIndo = $bulan[(int)$tanggalObj->format('m')];
    $tahun = $tanggalObj->format('Y');
    return "$hari $bulanIndo $tahun";
}

/**
 * Fungsi untuk mendapatkan nama hari dalam bahasa Indonesia.
 *
 * @param string $tanggal Tanggal dalam format Y-m-d.
 * @return string Nama hari dalam bahasa Indonesia.
 */
function getHariIndonesia($tanggal) {
    $hari = [
        'Sunday' => 'MINGGU',
        'Monday' => 'SENIN',
        'Tuesday' => 'SELASA',
        'Wednesday' => 'RABU',
        'Thursday' => 'KAMIS',
        'Friday' => 'JUMAT',
        'Saturday' => 'SABTU'
    ];
    $tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$tanggalObj) {
        return "HARI TIDAK VALID";
    }
    return $hari[$tanggalObj->format('l')];
}

// Tambahkan fungsi bulan romawi sebelum penggunaan $bulan_romawi
function bulanKeRomawi($tanggal)
{
    $bulan = (int)date('m', strtotime($tanggal));
    $romawi = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'V',
        6 => 'VI',
        7 => 'VII',
        8 => 'VIII',
        9 => 'IX',
        10 => 'X',
        11 => 'XI',
        12 => 'XII'
    ];
    return $romawi[$bulan] ?? '';
}
/**
 * Fungsi untuk mengompresi ukuran file gambar tanpa mengubah resolusinya.
 *
 * @param string $sourcePath Path file gambar asli.
 * @param string $destinationPath Path file gambar hasil kompresi.
 * @param int $quality Kualitas kompresi (0-100, semakin rendah semakin kecil ukuran file).
 * @return bool True jika berhasil, false jika gagal.
 */
function compressImage($sourcePath, $destinationPath, $quality = 85) {
    list(, , $imageType) = getimagesize($sourcePath);

    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($sourcePath);
            imagejpeg($image, $destinationPath, $quality);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($sourcePath);
            imagepng($image, $destinationPath, 1); // PNG quality: 0-9
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($sourcePath);
            imagegif($image, $destinationPath);
            break;
        default:
            return false;
    }

    imagedestroy($image);
    return true;
}
?>