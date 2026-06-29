<?php
require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_konversi.php';

$narrative = "";
$outputPathWordPatroli = $outputPathWordGeneral = $outputPathPdf = "";
$outputPathLandy = $outputPathPdfLandy = $outputPathPagi = $outputPathPdfPagi = "";
$narasiPatroliLandy = $narasiPatroliPagi = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reportType'])) {
    try {
        echo "<script>document.getElementById('progress').value = 10;</script>";
        ob_flush();
        flush();

        $hasilFolder = __DIR__ . '/hasil';
        cleanFolder($hasilFolder);

        // --- Proses Patroli ---
        $rawReport = $_POST['patrolReport'] ?? '';
        $hasilPatroli = prosesPatrolReport($rawReport);
        $groupedReports = $hasilPatroli['groupedReports'];
        $processedReports = $hasilPatroli['processedReports'];

        // Narasi Patroli
        $narasiPatroli = "";
        $totalPatroli = 0;
        $platformCounts = [];
        foreach ($groupedReports as $platform => $reports) {
            if (!empty($reports)) {
                $platformFormatted = ucwords(strtolower($platform));
                $narasiPatroli .= "*{$platformFormatted}*\n\n";
                if (count($reports) === 1) {
                    $narasiPatroli .= "{$reports[0]}\n\n";
                } else {
                    foreach ($reports as $index => $report) {
                        $narasiPatroli .= ($index + 1) . ". {$report}\n\n";
                    }
                }
                $platformCounts[$platform] = count($reports);
                $totalPatroli += count($reports);
            }
        }

        $sheetsToRead = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
        $tanggalInput = $_POST['tanggal'] ?? date('Y-m-d');
        $tanggalFormatted = strtoupper(formatTanggalIndonesia($tanggalInput));
        $tanggalNamaFile = date('dmY', strtotime($tanggalInput));
        $tanggalFormattedFirst = ucfirst(formatTanggalIndonesia($tanggalInput));
        $hariFormatted = getHariIndonesia($tanggalInput);
        $bulan_romawi = bulanKeRomawi($tanggalInput);

        // Proses file gambar patroli
        $screenshotPaths = [];
        $patroliScreenshotType = $_POST['patroliScreenshotType'] ?? 'upload';
        if ($patroliScreenshotType === 'screenshot') {
            // Ambil link dari processedReports
            $patroliLinks = [];
            foreach ($processedReports as $platform => $reports) {
                foreach ($reports as $report) {
                    if (!empty($report['link'])) {
                        $patroliLinks[] = $report['link'];
                    }
                }
            }
            if (count($patroliLinks) < 1) {
                throw new Exception('Tidak ada link pada hasil patrol report untuk tangkapan layar patroli.');
            }
            // Jalankan node ambil_ss.js patroli {link1} {link2} ...
            $escapedLinks = array_map('escapeshellarg', $patroliLinks);
            $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' patroli ' . implode(' ', $escapedLinks);
            exec($cmd, $output, $ret);
            // Ambil file hasil di folder ss/ dengan prefix patroli_
            $ssDir = __DIR__ . '/ss';
            $files = [];
            foreach (glob($ssDir . '/patroli_*.jpg') as $f) {
                $files[$f] = filemtime($f);
            }
            arsort($files);
            $selectedFiles = array_slice(array_keys($files), 0, count($patroliLinks));
            foreach ($selectedFiles as $src) {
                $dst = __DIR__ . '/template_word/' . basename($src);
                copy($src, $dst);
                $screenshotPaths[] = $dst;
            }
            // Hapus file screenshot patroli dari folder ss setelah diproses
            foreach ($selectedFiles as $src) {
                @unlink($src);
            }
        } else {
            if (isset($_FILES['screenshotPatroli'])) {
                for ($i = 0; $i < count($_FILES['screenshotPatroli']['name']); $i++) {
                    if (isset($_FILES['screenshotPatroli']['tmp_name'][$i]) && $_FILES['screenshotPatroli']['error'][$i] === UPLOAD_ERR_OK) {
                        $originalPath = $_FILES['screenshotPatroli']['tmp_name'][$i];
                        $destinationPath = __DIR__ . '/template_word/' . basename($_FILES['screenshotPatroli']['name'][$i]);
                        if (move_uploaded_file($originalPath, $destinationPath)) {
                            $screenshotPaths[] = $destinationPath;
                        } else {
                            throw new Exception('Gagal menyimpan screenshot patroli: ' . $_FILES['screenshotPatroli']['name'][$i]);
                        }
                    }
                }
            }
        }

        // --- Laporan KBD ---
        if (in_array('Laporan KBD', $_POST['reportType'])) {
            $totalPatroliNarrative = [];
            foreach ($platformCounts as $platform => $count) {
                $platformFormatted = ucwords(strtolower($platform));
                $totalPatroliNarrative[] = "{$platformFormatted} ({$count} konten)";
            }
            $totalPatroliNarrativeString = (count($totalPatroliNarrative) > 1)
                ? implode(', ', array_slice($totalPatroliNarrative, 0, -1)) . ' dan ' . end($totalPatroliNarrative)
                : implode('', $totalPatroliNarrative);

            $fileName = "{$tanggalNamaFile} - PELAKSANAAN CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA KONTER OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WILAYAH MERPATI – 14";

            // --- Tambahan: Proses gambar cipop dari link jika dipilih ---
            $cipopImageType = $_POST['cipopImageType'] ?? 'upload';
            $imagePaths = [];

            if ($cipopImageType === 'screenshot') {
                $cipopLinksRaw = $_POST['cipopScreenshotLinks'] ?? '';
                $cipopLinks = array_filter(array_map('trim', preg_split('/\r?\n/', $cipopLinksRaw)));
                if (count($cipopLinks) < 1 || count($cipopLinks) > 8) {
                    throw new Exception('Masukkan minimal 1 dan maksimal 8 link untuk tangkapan layar cipop.');
                }
                // Jalankan node ambil_ss.js cipop {link1} {link2} ...
                $escapedLinks = array_map('escapeshellarg', $cipopLinks);
                $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' cipop ' . implode(' ', $escapedLinks);
                // Jalankan command dan tunggu selesai
                exec($cmd, $output, $ret);
                // Ambil file hasil di folder ss/ dengan prefix cipop_
                $ssDir = __DIR__ . '/ss';
                $files = [];
                foreach (glob($ssDir . '/cipop_*.jpg') as $f) {
                    $files[$f] = filemtime($f);
                }
                // Ambil file terbaru sebanyak jumlah link
                arsort($files);
                $selectedFiles = array_slice(array_keys($files), 0, count($cipopLinks));
                // Salin ke hasil folder
                foreach ($selectedFiles as $src) {
                    $dst = $hasilFolder . '/' . basename($src);
                    copy($src, $dst);
                    $imagePaths[] = $dst;
                }
                // Hapus file screenshot cipop dari folder ss setelah diproses
                foreach ($selectedFiles as $src) {
                    @unlink($src);
                }
            } else {
                // Default: upload file
                if (!isset($_FILES['imageFiles']) || count($_FILES['imageFiles']['name']) < 1 || count($_FILES['imageFiles']['name']) > 8) {
                    throw new Exception('Harap unggah minimal 1 gambar dan maksimal 8 gambar.');
                }
                for ($i = 0; $i < count($_FILES['imageFiles']['name']); $i++) {
                    if (isset($_FILES['imageFiles']['tmp_name'][$i]) && $_FILES['imageFiles']['error'][$i] === UPLOAD_ERR_OK) {
                        $originalPath = $_FILES['imageFiles']['tmp_name'][$i];
                        $destinationPath = __DIR__ . '/template_pdf/' . basename($_FILES['imageFiles']['name'][$i]);
                        if (compressImage($originalPath, $destinationPath, 15)) {
                            $imagePaths[] = $destinationPath;
                        } else {
                            throw new Exception('Gagal mengompresi gambar: ' . $_FILES['imageFiles']['name'][$i]);
                        }
                    } else {
                        $imagePaths[] = null;
                    }
                }
            }

            $result = prosesExcelFiles($_FILES['excelFiles'], $sheetsToRead);
            echo "<script>document.getElementById('progress').value = 50;</script>";
            ob_flush();
            flush();

            $dataAkun = $result['dataAkun'];
            $dataLink = $result['dataLink'];
            $jumlahAkunperSheet = $jumlahLinkperSheet = $jumlahDataPerSheet = [];
            foreach ($dataAkun as $sheetName => $groupedData) {
                $jumlahDataPerSheet[$sheetName]['totalAkun'] = count($groupedData);
                $jumlahAkunperSheet[$sheetName] = count($groupedData);
            }
            foreach ($dataLink as $sheetName => $dataRows) {
                $jumlahDataPerSheet[$sheetName]['totalLink'] = count($dataRows);
                $jumlahLinkperSheet[$sheetName] = count($dataRows);
            }

            $narrative = <<<EOD
*Kepada: Yth.*
*1. Rajawali*
*2. Elang*

*Dari : Merpati - 14*

*Tembusan : Yth.*
*1. Kasuari – 2*
*2. Kasuari – 6*
*3. Kasuari – 9*

*Perihal : PELAKSANAAN CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA KONTER OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WIL. MERPATI – 14*

*A. EXECUTIVE SUMMARY*

Pada {$tanggalFormattedFirst}, di wilayah Merpati-14 termonitor sebanyak {$totalPatroli} konten provokasi terhadap Presiden Prabowo Subianto beserta keluarganya di media sosial yaitu {$totalPatroliNarrativeString}. Berdasarkan temuan tersebut Tim Delta Merpati-14 telah melakukan upaya RAS dan kontra propaganda dalam rangka mengeliminir propaganda negatif.

*B. HASIL PATROLI CYBER*

{$narasiPatroli}

*C. LANGKAH TINDAK*

Tim Merpati 14 memasifkan Kontra opini dengan tema konten *Program Pemerintahan Presiden Prabowo Subianto* dengan total Facebook {$jumlahLinkperSheet['FACEBOOK']} link, X / Twitter {$jumlahLinkperSheet['TWITTER']} link, Instagram {$jumlahLinkperSheet['INSTAGRAM']} link, Tiktok {$jumlahLinkperSheet['TIKTOK']} link, Snackvideo {$jumlahLinkperSheet['SNACKVIDEO']} link dan Youtube {$jumlahLinkperSheet['YOUTUBE']} link.

Nilai : Ambon-1

*DUMP Merpati-14*
EOD;

            // Generate Word & PDF
            $templatePathWordGeneral = __DIR__ . '/template_word/template_viral.docx';
            $outputPathWordGeneral = $hasilFolder . "/{$fileName}.docx";
            createWordFile($templatePathWordGeneral, $outputPathWordGeneral, $tanggalFormatted, $jumlahDataPerSheet, $dataLink);

            $templatePathHtml = __DIR__ . '/template_pdf/template_kbd.html';
            $outputPathPdf = $hasilFolder . "/{$fileName}.pdf";
            createPdfFile($templatePathHtml, $outputPathPdf, $tanggalFormatted, $hariFormatted, $tanggalFormattedFirst, $jumlahLinkperSheet, $imagePaths);

            $templatePathWordPatroli = __DIR__ . '/template_word/template_Patroli_kbd.docx';
            $outputPathWordPatroli = $hasilFolder . "/HASIL PATROLI SIBER TERKAIT OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WIL. MERPATI – 14 TANGGAL {$tanggalFormatted}.docx";
            createWordFilePatroli($templatePathWordPatroli, $outputPathWordPatroli, $tanggalFormatted, $processedReports, $screenshotPaths);

            echo "<script>document.getElementById('progress').value = 70;</script>";
            ob_flush();
            flush();
        }

        // --- Patroli Landy ---
        if (in_array('Patroli Landy', $_POST['reportType'])) {
            $isiPatroliLandy = "";
            $tanggal_formatted_first = $tanggalFormattedFirst ?? '';
            foreach ($processedReports as $platform => $reports) {
                if (!empty($reports)) {
                    $platformFormatted = ucwords(strtolower($platform));
                    $isiPatroliLandy .= "*{$platformFormatted}*\n\n";
                    $no = 1;
                    foreach ($reports as $report) {
                        $isiPatroliLandy .= "{$no}. Pada {$tanggal_formatted_first}, hasil Patroli Siber mendeteksi akun \"{$report['name']}\" ({$report['link']}) di media sosial _*{$platformFormatted}*_ yang berisikan postingan {$report['category']} dengan {$report['narrative']}\n\n";
                        $no++;
                    }
                }
            }
            $narasiPatroliLandy = <<<EOD
*Kepada Yth : Kasuari-23*

*Dari : Merpati - 14*

*Perihal : Patroli Siber Terkait Akun Yang Mendiskreditkan Presiden Prabowo Subianto di Media Sosial Update {$tanggalFormattedFirst}*

*A. KEGIATAN PATROLI SIBER*

{$isiPatroliLandy}
*C.UPAYA*

1.Melakukan pemantauan terhadap akun yang menyebarkan berita atau isu yang menyudutkan pemerintahan.

2.Melakukan pemetaan terhadap postingan ataupun berita tendensius dan hoax serta penyebarnya yang tersebar di dunia maya.

3.Melakukan kontra dan report terhadap isu sensitif yang efeknya diperkirakan cukup besar dan nyata baik dengan tulisan maupun dengan meme yang bersifat menarik.

*DUMP. TTD: Merpati - 14*
EOD;

            // Upaya screenshot logic
            $upayaScreenshotType = $_POST['upayaScreenshotType'] ?? 'upload';
            $foto_upaya = [];
            if ($upayaScreenshotType === 'screenshot') {
                $rawUpaya = $_POST['input_upaya'] ?? '';
                $hasilUpaya = prosesPatrolReport($rawUpaya, 'upaya', 3);
                $processedUpaya = $hasilUpaya['processedReports'];
                // Ambil link dari processedReports upaya
                $upayaLinks = [];
                if (isset($processedUpaya)) {
                    foreach ($processedUpaya as $platform => $reports) {
                        foreach ($reports as $report) {
                            if (!empty($report['link'])) {
                                $upayaLinks[] = $report['link'];
                            }
                        }
                    }
                }
                if (count($upayaLinks) < 1) {
                    throw new Exception('Tidak ada link pada hasil upaya untuk tangkapan layar upaya.');
                }
                $escapedLinks = array_map('escapeshellarg', $upayaLinks);
                $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' upaya ' . implode(' ', $escapedLinks);
                exec($cmd, $output, $ret);
                $ssDir = __DIR__ . '/ss';
                $files = [];
                foreach (glob($ssDir . '/upaya_*.jpg') as $f) {
                    $files[$f] = filemtime($f);
                }
                arsort($files);
                $selectedFiles = array_slice(array_keys($files), 0, count($upayaLinks));
                foreach ($selectedFiles as $src) {
                    $dst = __DIR__ . '/template_word/' . basename($src);
                    copy($src, $dst);
                    $foto_upaya[] = $dst;
                }
                foreach ($selectedFiles as $src) {
                    @unlink($src);
                }
            } else {
                $foto_upaya = isset($_FILES['upayaFiles']) ? $_FILES['upayaFiles']['tmp_name'] : [];
            }

            // Hapus juga file hasil copy screenshot link patroli setelah proses selesai
            register_shutdown_function(function () use (&$foto_upaya) {
                foreach ($foto_upaya as $file) {
                    @unlink($file);
                }
            });

            // Data untuk template
            $nama_akun = $kategori = $narasi = $link = [];
            foreach ($processedReports as $platform => $reports) {
                foreach ($reports as $report) {
                    $nama_akun[] = $report['name'];
                    $kategori[] = $report['category'];
                    $narasi[] = $report['narrative'];
                    $link[] = $report['link'];
                }
            }
            $tanggal_judul = $tanggalFormatted;
            $tanggal = $tanggalFormattedFirst;
            $foto_patroli = $screenshotPaths;

            $totalReports = count($nama_akun);
            if (count($foto_patroli) !== $totalReports) throw new Exception('Jumlah screenshot patroli harus sama dengan jumlah laporan yang diproses.');
            if (count($foto_upaya) !== $totalReports) throw new Exception('Jumlah foto upaya harus sama dengan jumlah laporan yang diproses.');

            $templatePathLandy = __DIR__ . '/template_word/template_patroli_landy.docx';
            $outputPathLandy = $hasilFolder . "/PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP AKUN YANG MENDISKREDITKAN PRESIDEN PRABOWO SUBIANTO UPDATE TANGGAL {$tanggalFormatted}.docx";
            createWordFileLandy($templatePathLandy, $outputPathLandy, [
                'nama_akun' => $nama_akun,
                'tanggal_judul' => $tanggal_judul,
                'tanggal' => $tanggal,
                'kategori' => $kategori,
                'narasi' => $narasi,
                'link' => $link,
                'foto_patroli' => $foto_patroli,
                'foto_upaya' => $foto_upaya
            ]);

            $templatePathHtmlLandy = __DIR__ . '/template_pdf/template_patroli.html';
            $outputPathPdfLandy = $hasilFolder . "/LAMPIRAN PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP AKUN YANG MENDISKREDITKAN PRESIDEN PRABOWO SUBIANTO DI WILAYAH MERPATI - 14 PADA {$tanggalFormatted}.pdf";
            createPdfFileLandy($templatePathHtmlLandy, $outputPathPdfLandy, $foto_patroli, $foto_upaya);

            deleteUploadedImages($foto_upaya ?? []);
        }

        // --- Patroli Pagi ---
        if (in_array('Patroli Pagi', $_POST['reportType'])) {
            $rawUpaya = $_POST['input_upaya'] ?? '';
            $hasilUpaya = prosesPatrolReport($rawUpaya, 'upaya', 3);
            $processedUpaya = $hasilUpaya['processedReports'];

            $akunPatroli = [];
            foreach ($processedReports as $platform => $reports) {
                foreach ($reports as $report) {
                    $akunPatroli[] = $report['name'];
                }
            }

            $narasiUpayaPagi = '';
            $idxPatroli = 0;
            foreach ($processedUpaya as $platform => $upayaList) {
                foreach ($upayaList as $upaya) {
                    $nama_akun_patroli = $akunPatroli[$idxPatroli] ?? '-';
                    $nama_akun_upaya = $upaya['name'] ?? '-';
                    $link = $upaya['link'] ?? '-';
                    $narasi = $upaya['narrative'] ?? '-';
                    $point = chr(97 + $idxPatroli) . '.';
                    $narasiUpayaPagi .= "{$point} Upaya Kontra & Takedown terhadap Akun ({$nama_akun_patroli}) dengan membuat postingan melalui akun ({$nama_akun_upaya}) ({$link}) dengan {$narasi}\n\n";
                    $idxPatroli++;
                }
            }

            $isiPatroliPagi = "";
            $tanggal_formatted_first = $tanggalFormattedFirst ?? '';
            foreach ($processedReports as $platform => $reports) {
                if (!empty($reports)) {
                    $platformFormatted = ucwords(strtolower($platform));
                    $no = 0;
                    foreach ($reports as $report) {
                        $point = chr(97 + $no) . '.';
                        $isiPatroliPagi .= "{$point} Termonitor Akun ({$report['name']}) ({$report['link']}) membagikan postingan {$report['category']} dengan {$report['narrative']}\n\n";
                        $no++;
                    }
                }
            }

            $nama_akun = $kategori = $narasi = $link = [];
            foreach ($processedReports as $platform => $reports) {
                foreach ($reports as $report) {
                    $nama_akun[] = $report['name'];
                    $kategori[] = $report['category'];
                    $narasi[] = $report['narrative'];
                    $link[] = $report['link'];
                }
            }
            $nama_akun_upaya = $narasi_upaya = $link_upaya = [];
            foreach ($processedUpaya as $platform => $reports) {
                foreach ($reports as $report) {
                    $nama_akun_upaya[] = $report['name'] ?? '-';
                    $narasi_upaya[] = $report['narrative'] ?? '-';
                    $link_upaya[] = $report['link'] ?? '-';
                }
            }

            $upayaScreenshotType = $_POST['upayaScreenshotType'] ?? 'upload';

            $foto_upaya = [];
            if ($upayaScreenshotType === 'screenshot') {
                $upayaLinks = [];
                if (isset($processedUpaya)) {
                    foreach ($processedUpaya as $platform => $reports) {
                        foreach ($reports as $report) {
                            if (!empty($report['link'])) {
                                $upayaLinks[] = $report['link'];
                            }
                        }
                    }
                }
                if (count($upayaLinks) < 1) {
                    throw new Exception('Tidak ada link pada hasil upaya untuk tangkapan layar upaya.');
                }
                $escapedLinks = array_map('escapeshellarg', $upayaLinks);
                $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' upaya ' . implode(' ', $escapedLinks);
                exec($cmd, $output, $ret);
                $ssDir = __DIR__ . '/ss';
                $files = [];
                foreach (glob($ssDir . '/upaya_*.jpg') as $f) {
                    $files[$f] = filemtime($f);
                }
                arsort($files);
                $selectedFiles = array_slice(array_keys($files), 0, count($upayaLinks));
                foreach ($selectedFiles as $src) {
                    $dst = __DIR__ . '/template_word/' . basename($src);
                    copy($src, $dst);
                    $foto_upaya[] = $dst;
                }
                foreach ($selectedFiles as $src) {
                    @unlink($src);
                }
            } else {
                $foto_upaya = isset($_FILES['upayaFiles']) ? $_FILES['upayaFiles']['tmp_name'] : [];
            }

            // Hapus juga file hasil copy screenshot link patroli setelah proses selesai
            register_shutdown_function(function () use (&$foto_upaya) {
                foreach ($foto_upaya as $file) {
                    @unlink($file);
                }
            });

            $tanggal_judul = $tanggalFormatted;
            $foto_patroli = $screenshotPaths;
            $tahun_input = date('Y', strtotime($tanggalInput));
            $totalReports = count($nama_akun);
            $totalUpaya = count($nama_akun_upaya);

            if (count($foto_patroli) !== $totalReports) throw new Exception('Jumlah screenshot patroli harus sama dengan jumlah laporan yang diproses.');
            if (count($foto_upaya) !== $totalReports) throw new Exception('Jumlah foto upaya harus sama dengan jumlah laporan yang diproses.');

            $narasiPatroliPagi = <<<EOD
*Kepada Yth:*

*1. Rajawali*
*2. Elang*

*Dari: Merpati-14*

*Tembusan : Yth.*
*1. Kasuari-2*
*2. Kasuari-9*
*3. Kasuari-21*
*4. Kasuari-23*

*Perihal : Patroli Siber di Wilayah Merpati-14 ({$tanggalFormattedFirst})*

*1. KEGIATAN PATROLI SIBER*

*A. EXECUTIVE SUMMARY*

Pada {$tanggalFormattedFirst} di Jambi, Provinsi Jambi, telah dilakukan giat Patroli Siber terkait keberadaan konten/akun/postingan negatif yang bersifat hoax, provokatif, ujaran kebencian, dukungan terhadap Khilafah, memecah belah NKRI, isu kebangkitan PKI maupun tanggapan negatif terhadap kebijakan Pemerintah dengan hasil {$totalReports} temuan konten dan telah dilakukan {$totalUpaya} upaya kontra kicau, serta upaya Takedown.

*B. HAL MENONJOL*

 Hasil monitoring di media sosial terhadap konten negatif telah ditemukan sebanyak {$totalReports} konten. Selanjutnya hasil monitoring konten-konten menonjol di media sosial, antara lain:

 *1. Provokasi dan Ujaran Kebencian*

{$isiPatroliPagi}
*C. LANGKAH TIM SIBER WILAYAH MERPATI-14:* Dalam menyikapi penyebaran konten negatif di media sosial, Tim Siber Wilayah Merpati-14 melakukan Kontra Kicau, al:

*1. Kontra Kicau dan Upaya Takedown postingan negatif di Medsos :*

{$narasiUpayaPagi}
*D. CATATAN*

 Hingga saat ini konten/akun/situs/postingan negatif, menghina, dan provokatif media sosial di wilayah Merpati-14 rata-rata berada pada akun palsu dan disebarkan melalui Facebook. 

*E. LANGKAH TINDAK*

1. Melakukan profiling akun, grup, dan website, serta counter terhadap topik atau isu menonjol dengan melakukan diseminasi gambar atau konten grafis di media sosial.
2. Melakukan pemantauan terhadap akun yang menyebarkan berita atau isu yang menyudutkan pemerintahan.
3. Melakukan pemetaan terhadap postingan ataupun berita tendensius dan hoax serta penyebarnya yang tersebar di dunia maya.
4. Melakukan kontra terhadap isu sensitif yang efeknya diperkirakan cukup besar dan nyata baik dengan tulisan maupun dengan meme yang bersifat menarik.
5. Membangun jaringan di dunia maya dengan pemangku kepentingan lain guna menangkal penyebaran konten negatif.

*F. SARAN TINDAK*
 Merpati-14 menyarankan kepada Jajaran Kasuari-VI untuk membantu memblokir/ merusak Akun Provokatif yang menjadi temuan Merpati-14.

*DUMP. TTD: Merpati - 14*
EOD;

            $templatePathPagi = __DIR__ . '/template_word/template_Patroli_pagi.docx';
            $outputPathPagi = $hasilFolder . "/Laporan Patroli Siber Konten Negatif di Wilayah Merpati-14 Update  {$tanggalFormatted}.docx";
            $outputPatroliPdfPagi = $hasilFolder . "/Laporan Patroli Siber Konten Negatif di Wilayah Merpati-14 Update  {$tanggalFormatted}.pdf";
            createWordFilePagi($templatePathPagi, $outputPathPagi, [
                'nama_akun' => $nama_akun,
                'kategori' => $kategori,
                'narasi' => $narasi,
                'link' => $link,
                'foto' => $foto_patroli,
            ], [
                'nama_akun' => $nama_akun_upaya,
                'narasi' => $narasi_upaya,
                'link' => $link_upaya,
                'foto' => $foto_upaya
            ], [
                'tanggal_lampiran' => $tanggal_judul,
                'tanggal' => $tanggalFormattedFirst,
                'bulan_romawi' => $bulan_romawi,
                'isi_patroli'    => $isiPatroliPagi,
                'tahun' => $tahun_input
            ]);

            $templatePathHtmlPagi = __DIR__ . '/template_pdf/template_patroli.html';
            $outputPathPdfPagi = $hasilFolder . "/Lampiran Patroli Siber Merpati 14 ({$tanggalFormatted}).pdf";
            createPdfFileLandy($templatePathHtmlPagi, $outputPathPdfPagi, $foto_patroli, $foto_upaya);

            deleteUploadedImages($foto_upaya ?? []);
        }

        echo "<script>document.getElementById('progress').value = 100;</script>";

        // Hapus semua gambar yang sudah dipindahkan ke folder foto
        deleteUploadedImages($imagePaths ?? []);
        deleteUploadedImages($screenshotPaths ?? []);
        deleteUploadedImages($upayaPaths ?? []);
    } catch (\Exception $e) {
        // // Tangani kesalahan di sini
        // $errorMessage = $e->getMessage();
        // echo "<script>alert('Terjadi kesalahan: $errorMessage');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel File</title>
    <!-- Use local Bootstrap CSS and Bootstrap Icons from node_modules -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="node_modules/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }

        .card {
            background-color: #1f1f1f;
            color: #ffffff;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
        }

        textarea.form-control {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-hash"></i> Rekap Hastag</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#"><i class="bi bi-house-door"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-upload"></i> Upload Data</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-bar-chart"></i> Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-gear"></i> Settings</a>
                    </li>
                </ul>
                <!-- Petunjuk Penggunaan di pojok kanan navbar -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-info fw-bold" href="#" id="petunjukDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-info-circle"></i> Petunjuk Penggunaan
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="petunjukDropdown" style="min-width:350px;max-width:400px;">
                            <div style="font-size:0.97rem;">
                                <ol class="mb-3 ps-3" style="font-size: 1rem;">
                                    <li>
                                        <b>Pilih Jenis Laporan</b> (Laporan KBD, Patroli Landy, atau Patroli Pagi) pada langkah pertama.
                                        <ul>
                                            <li><b>Laporan KBD</b>: Rekap laporan Cipop (Excel) dan hasil patroli (Word & PDF).</li>
                                            <li><b>Patroli Landy</b>: Laporan patroli dan upaya kontra opini (Word & PDF).</li>
                                            <li><b>Patroli Pagi</b>: Laporan patroli pagi dan upaya takedown (Word & PDF).</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <b>Pilih Tanggal Laporan</b> pada langkah kedua.
                                        <ul>
                                            <li>Tanggal digunakan sebagai penanda laporan dan nama file hasil.</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <b>Input Data Teks</b> pada langkah ketiga:
                                        <ul>
                                            <li>
                                                <b>Input Patrol Report</b> wajib diisi.<br>
                                                <span class="text-warning">Format:</span>
                                                <pre style="background:#222;color:#fff;padding:6px 10px;border-radius:5px;font-size:0.97em;">
nama akun
link
kategori
narasi
                                                </pre>
                                                <span class="text-info">Pisahkan setiap laporan dengan baris kosong.</span>
                                            </li>
                                            <li>
                                                <b>Input Upaya Patroli Pagi</b> (khusus jika memilih Patroli Pagi):<br>
                                                Format:
                                                <pre style="background:#222;color:#fff;padding:6px 10px;border-radius:5px;font-size:0.97em;">
nama akun
link
narasi
                                                </pre>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <b>Upload File Pendukung</b> pada langkah keempat:
                                        <ul>
                                            <li>
                                                <b>Laporan KBD</b>: Upload file Excel Cipop (.xlsx/.xls) dan 1-8 gambar (jpg/png) atau masukkan link untuk screenshot otomatis.
                                            </li>
                                            <li>
                                                <b>Patroli Landy/Pagi</b>: Upload file gambar upaya (jumlah sesuai laporan) atau gunakan screenshot otomatis dari link.
                                            </li>
                                            <li>
                                                <b>Semua Jenis</b>: Upload screenshot patroli (jumlah sesuai laporan) atau gunakan screenshot otomatis dari link pada Patrol Report.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        Klik <b>Upload dan Proses</b> untuk memulai pembuatan laporan. Tunggu hingga proses selesai dan progress mencapai 100%.
                                    </li>
                                    <li>
                                        Setelah proses selesai, <b>download file hasil laporan</b> pada kolom yang tersedia di bawah form.
                                    </li>
                                </ol>
                                <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:0.95rem;">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <b>Tips:</b> Pastikan format input dan jumlah file sesuai instruksi agar proses berjalan lancar. Jika ada error, periksa kembali inputan dan file yang diupload. Gunakan link publik untuk screenshot otomatis.
                                </div>
                                <div class="alert alert-secondary py-2 px-3 mb-0" style="font-size:0.95rem;">
                                    <i class="bi bi-bug-fill"></i>
                                    Jika terjadi error, cek kembali format input, jumlah file, dan pastikan tidak ada file yang rusak. Untuk screenshot otomatis, pastikan link dapat diakses publik dan bukan private.
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <!-- Left Column: Input Form -->
            <div class="col-md-3 mb-4">
                <!-- Step-by-step Wizard Form -->
                <form id="wizardForm" action="index.php" target="_blank" method="post" enctype="multipart/form-data" onsubmit="showProgress()" class="bg-dark p-4 rounded shadow-sm">
                    <!-- Step 1: Pilih Jenis Laporan -->
                    <div class="wizard-step" id="step-1">
                        <h5 class="fw-bold mb-2">Langkah 1: Pilih Jenis Laporan</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Pilih satu atau lebih jenis laporan yang ingin Anda proses.</p>
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-ui-checks-grid"></i> Pilih Jenis Laporan:</label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reportType[]" id="laporanKbd" value="Laporan KBD">
                                    <label class="form-check-label" for="laporanKbd"><i class="bi bi-file-earmark-text"></i> Laporan KBD</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reportType[]" id="patroliLandy" value="Patroli Landy">
                                    <label class="form-check-label" for="patroliLandy"><i class="bi bi-shield-check"></i> Patroli Landy</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reportType[]" id="patroliPagi" value="Patroli Pagi">
                                    <label class="form-check-label" for="patroliPagi"><i class="bi bi-sunrise"></i> Patroli Pagi</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" onclick="nextStep(1)">Next</button>
                        </div>
                    </div>
                    <!-- Step 2: Pilih Tanggal -->
                    <div class="wizard-step d-none" id="step-2">
                        <h5 class="fw-bold mb-2">Langkah 2: Pilih Tanggal Laporan</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Tentukan tanggal laporan yang akan diproses.</p>
                        <div class="mb-3">
                            <label for="tanggal" class="form-label"><i class="bi bi-calendar-event"></i> Pilih Tanggal:</label>
                            <input type="date" name="tanggal" id="tanggal" required class="form-control">
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
                        </div>
                    </div>
                    <!-- Step 3: Input Teks -->
                    <div class="wizard-step d-none" id="step-3">
                        <h5 class="fw-bold mb-2">Langkah 3: Input Data Teks</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Masukkan data teks sesuai kebutuhan laporan.</p>
                        <!-- Semua Jenis: Patrol Report -->
                        <div class="card bg-secondary mb-2">
                            <div class="card-header text-white">
                                <strong>Input Patrol Report</strong>
                            </div>
                            <div class="card-body p-2">
                                <label for="patrolReport" class="form-label"><i class="bi bi-pencil-square"></i> Input Patrol Report:</label>
                                <textarea name="patrolReport" id="patrolReport" rows="8" required class="form-control"
                                    placeholder="Format teks patroli

nama akun
link
kategori
narasi
"></textarea>
                            </div>
                        </div>
                        <!-- Patroli Pagi: Input Upaya -->
                        <div id="step3-inputUpaya" class="d-none">
                            <div class="card bg-secondary mb-2">
                                <div class="card-header text-white">
                                    <strong>Input Upaya Patroli Pagi</strong>
                                </div>
                                <div class="card-body p-2">
                                    <label for="inputUpaya" class="form-label"><i class="bi bi-pencil"></i> Input Upaya Patroli Pagi:</label>
                                    <textarea name="input_upaya" id="inputUpaya" rows="8" class="form-control"
                                        placeholder="Format teks upaya

nama akun
link
narasi
"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Previous</button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next</button>
                        </div>
                    </div>
                    <!-- Step 4: Upload File -->
                    <div class="wizard-step d-none" id="step-4">
                        <h5 class="fw-bold mb-2">Langkah 4: Upload File Pendukung</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Upload file sesuai kebutuhan laporan yang dipilih.</p>
                        <!-- Laporan KBD -->
                        <div id="step4-laporanKbd" class="d-none">
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color:#0dcaf0;"><i class="bi bi-file-earmark-excel"></i> Laporan KBD</label>
                                <label for="excelFiles" class="form-label"><i class="bi bi-file-earmark-excel"></i> Upload Excel Cipop:</label>
                                <input type="file" name="excelFiles[]" id="excelFiles" accept=".xlsx, .xls" multiple class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color:#0dcaf0;"><i class="bi bi-images"></i> Laporan KBD</label>
                                <label class="form-label"><i class="bi bi-image"></i> Pilih Jenis Gambar Cipop:</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="cipopImageType" id="cipopUploadFile" value="upload" checked>
                                        <label class="form-check-label" for="cipopUploadFile">Upload File</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="cipopImageType" id="cipopScreenshotLink" value="screenshot">
                                        <label class="form-check-label" for="cipopScreenshotLink">Tangkapan Layar Link</label>
                                    </div>
                                </div>
                                <div id="cipopUploadFileGroup">
                                    <label for="imageFiles" class="form-label"><i class="bi bi-image"></i> Upload Gambar (1-8):</label>
                                    <input type="file" name="imageFiles[]" id="imageFiles" accept="image/*" multiple class="form-control">
                                </div>
                                <div id="cipopScreenshotLinkGroup" class="d-none">
                                    <label for="cipopScreenshotLinks" class="form-label"><i class="bi bi-link"></i> Masukkan Link (satu per baris):</label>
                                    <textarea name="cipopScreenshotLinks" id="cipopScreenshotLinks" rows="5" class="form-control" placeholder="https://facebook.com/...
https://instagram.com/..."></textarea>
                                    <div class="alert alert-warning mt-2 py-2 px-3" style="font-size:0.95rem;">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <b>Catatan penting untuk tangkapan layar link:</b><br>
                                        <ul class="mb-0 ps-3" style="font-size:0.97em;">
                                            <li>Pastikan link yang dimasukkan valid dan dapat diakses publik (bukan link bodong).</li>
                                            <li>Khusus Facebook: pastikan postingan/grup bersifat publik (terbuka), bukan private/tertutup.</li>
                                            <li>Platform TikTok: <b>belum didukung</b> otomatis screenshot, karena halaman TikTok sering gagal diambil otomatis.</li>
                                            <li>Hasil screenshot tergantung akses publik dan status login browser server.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Patroli Landy/Pagi -->
                        <div id="step4-patroliLandyPagi" class="d-none">
                            <div class="card bg-secondary mb-2">
                                <div class="card-header text-white">
                                    <strong>Upload Upaya Patroli Pagi / Landy</strong>
                                </div>
                                <div class="card-body p-2">
                                    <label class="form-label fw-bold" style="color:#ffc107;"><i class="bi bi-shield-check"></i> Laporan Landy/Pagi</label>
                                    <div class="mb-2" id="upayaScreenshotTypeGroup">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="upayaScreenshotType" id="upayaScreenshotUploadFile" value="upload" checked>
                                            <label class="form-check-label" for="upayaScreenshotUploadFile">Upload File</label>
                                        </div>
                                        <div class="form-check form-check-inline" id="upayaScreenshotLinkRadio">
                                            <input class="form-check-input" type="radio" name="upayaScreenshotType" id="upayaScreenshotLink" value="screenshot">
                                            <label class="form-check-label" for="upayaScreenshotLink">Tangkapan Layar Link (otomatis dari link pada Upaya)</label>
                                        </div>
                                    </div>
                                    <div id="upayaScreenshotUploadFileGroup">
                                        <label for="upayaFiles" class="form-label"><i class="bi bi-upload"></i> Upload Upaya:</label>
                                        <input type="file" name="upayaFiles[]" id="upayaFiles" accept="image/*" multiple class="form-control">
                                    </div>
                                    <div id="upayaScreenshotLinkWarning" class="d-none">
                                        <div class="alert alert-warning mt-2 py-2 px-3" style="font-size:0.95rem;">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            <b>Catatan penting untuk tangkapan layar link:</b><br>
                                            <ul class="mb-0 ps-3" style="font-size:0.97em;">
                                                <li>Pastikan link pada data Upaya valid dan dapat diakses publik (bukan link bodong).</li>
                                                <li>Khusus Facebook: pastikan postingan/grup bersifat publik (terbuka), bukan private/tertutup.</li>
                                                <li>Platform TikTok: <b>belum didukung</b> otomatis screenshot, karena halaman TikTok sering gagal diambil otomatis.</li>
                                                <li>Hasil screenshot tergantung akses publik dan status login browser server.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Semua Jenis: Screenshot Patroli -->
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-image"></i> Pilih Jenis Screenshot Patroli:</label>
                            <div class="mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="patroliScreenshotType" id="patroliScreenshotUploadFile" value="upload" checked>
                                    <label class="form-check-label" for="patroliScreenshotUploadFile">Upload File</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="patroliScreenshotType" id="patroliScreenshotLink" value="screenshot">
                                    <label class="form-check-label" for="patroliScreenshotLink">Tangkapan Layar Link (otomatis dari link pada Patrol Report)</label>
                                </div>
                            </div>
                            <div id="patroliScreenshotUploadFileGroup">
                                <label for="screenshotPatroli" class="form-label"><i class="bi bi-image"></i> Upload Screenshot Patroli:</label>
                                <input type="file" name="screenshotPatroli[]" id="screenshotPatroli" accept="image/*" multiple class="form-control">
                            </div>
                            <div id="patroliScreenshotLinkWarning" class="d-none">
                                <div class="alert alert-warning mt-2 py-2 px-3" style="font-size:0.95rem;">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <b>Catatan penting untuk tangkapan layar link:</b><br>
                                    <ul class="mb-0 ps-3" style="font-size:0.97em;">
                                        <li>Pastikan link pada Patrol Report valid dan dapat diakses publik (bukan link bodong).</li>
                                        <li>Khusus Facebook: pastikan postingan/grup bersifat publik (terbuka), bukan private/tertutup.</li>
                                        <li>Platform TikTok: <b>belum didukung</b> otomatis screenshot, karena halaman TikTok sering gagal diambil otomatis.</li>
                                        <li>Hasil screenshot tergantung akses publik dan status login browser server.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="prevStep(4)">Previous</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload dan Proses</button>
                        </div>
                    </div>
                </form>

            </div>
            <!-- Middle Column: Laporan KBD -->
            <div class="col-md-3">
                <?php if (isset($outputPathWordGeneral) && file_exists($outputPathWordGeneral)): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Narasi Laporan KBD</h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" rows="20" readonly><?= htmlspecialchars($narrative) ?></textarea>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Download Lampiran Laporan KBD</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="btn-group" role="group" aria-label="Download Laporan KBD">
                                <?php if (file_exists($outputPathWordPatroli)): ?>
                                    <a href="hasil/<?= basename($outputPathWordPatroli) ?>" class="btn btn-success" target="_blank"><i class="bi bi-file-earmark-word"></i> Download Patroli(Word)</a>
                                <?php endif; ?>
                                <?php if (file_exists($outputPathWordGeneral)): ?>
                                    <a href="hasil/<?= basename($outputPathWordGeneral) ?>" class="btn btn-success" target="_blank">Download Cipop (Word)</a>
                                <?php endif; ?>
                                <?php if (file_exists($outputPathPdf)): ?>
                                    <a href="hasil/<?= basename($outputPathPdf) ?>" class="btn btn-danger" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Download Lampiran (PDF)</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Right Column: Patroli Landy -->
            <div class="col-md-3">
                <?php if (isset($outputPathLandy) && file_exists($outputPathLandy)): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Narasi Laporan Patroli Landy</h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" rows="20" readonly><?= htmlspecialchars($narasiPatroliLandy) ?></textarea>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Download Lampiran Patroli Landy</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="btn-group" role="group" aria-label="Download Patroli Landy">
                                <a href="hasil/<?= basename($outputPathLandy) ?>" class="btn btn-success" target="_blank">
                                    <i class="bi bi-file-earmark-word"></i> Word
                                </a>
                                <a href="hasil/<?= basename($outputPathPdfLandy) ?>" class="btn btn-danger" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Far Right Column: Patroli Pagi -->
            <div class="col-md-3">
                <?php if (isset($outputPathPagi) && file_exists($outputPathPagi)): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Narasi Laporan Patroli Pagi</h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" rows="20" readonly><?= htmlspecialchars($narasiPatroliPagi) ?></textarea>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Download Lampiran Patroli Pagi</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="btn-group" role="group" aria-label="Download Patroli Pagi">
                                <a href="hasil/<?= basename($outputPathPagi) ?>" class="btn btn-success" target="_blank">
                                    <i class="bi bi-file-earmark-word"></i> Patroli Word
                                </a>
                                <a href="hasil/<?= basename($outputPathPdfPagi) ?>" class="btn btn-danger" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i> Lampiran PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <!-- Kosong atau bisa diisi info tambahan -->
            </div>
        </div>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/custom/custom.js"></script>
</body>

</html>