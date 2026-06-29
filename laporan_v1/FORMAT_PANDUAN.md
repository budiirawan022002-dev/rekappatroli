# PANDUAN FORMAT INPUT PATROLI LANDY

## ✅ KEDUA FORMAT INI DIDUKUNG

### Format A: Dengan Label (Recommended - Lebih Mudah Dibaca)
```
nama akun: Akun Facebook Jokotole
link: https://www.facebook.com/joko.tolebhr
kategori: Kritik Program MBG
narasi: Komentar ini membangun narasi penolakan...
profiling: Akun ini menunjukkan profil sebagai seorang...
tanggal_postingan: 27/09/2025
wilayah: Jambi
korelasi: Bewinters langsung dengan postingan...
afiliasi: (Tidak ditemukan)

```
*Note: Baris kosong untuk pemisah antar akun*

### Format B: Tanpa Label (Format Lama)
```
Akun Facebook Jokotole
https://www.facebook.com/joko.tolebhr
Kritik Program MBG
Komentar ini membangun narasi penolakan...
Akun ini menunjukkan profil sebagai seorang...
27/09/2025
Jambi
Bewinters langsung dengan postingan...
(Tidak ditemukan)

```
*Note: Baris kosong untuk pemisah antar akun*

## 📋 VARIASI LABEL YANG DIDUKUNG

Label berikut akan otomatis dihapus:
- `nama akun:` / `nama_akun:` / `namaakun:`
- `link:`
- `kategori:`
- `narasi:`
- `profiling:`
- `tanggal postingan:` / `tanggal_postingan:` / `tanggalpostingan:`
- `wilayah:`
- `korelasi:`
- `afiliasi:`

*Case insensitive: `Nama Akun:`, `LINK:`, dll juga valid*

## 🔴 REQUIREMENT UNTUK PATROLI LANDY

Untuk generate laporan Patroli Landy, Anda HARUS mengupload:

### 1. **Patrol Report** (9 baris per akun)
   - Format dengan atau tanpa label
   - Minimal 1 akun
   - Pisahkan dengan baris kosong

### 2. **Screenshot Patroli** 
   - 1 foto per akun
   - Format: JPG, PNG
   - Contoh: Jika ada 3 akun, upload 3 foto

### 3. **Foto RAS/Upaya** 
   - 1 foto per akun
   - Format: JPG, PNG
   - Jumlah harus sama dengan jumlah akun

### 4. **Foto Profiling**
   - 1 foto per akun
   - Format: JPG, PNG
   - Jumlah harus sama dengan jumlah akun

### 5. **Tanggal Laporan**
   - Pilih tanggal

## ⚠️ COMMON ERRORS

### Error: "Format Patrol Report tidak valid"
**Penyebab**: Jumlah baris bukan kelipatan 9
**Solusi**: Pastikan setiap akun punya 9 field lengkap

### Error: "Upload minimal 1 screenshot patroli"
**Penyebab**: File screenshot belum diupload
**Solusi**: Upload file di section "Screenshot Patroli"

### Error: "Upload minimal 1 gambar RAS"
**Penyebab**: File RAS belum diupload
**Solusi**: Upload file di section "Upload Foto RAS/Upaya Landy"

### Error: "Upload minimal 1 foto profiling"
**Penyebab**: File profiling belum diupload
**Solusi**: Upload file di section "Upload Foto Profiling Landy"

### Error: "Jumlah foto tidak sama dengan jumlah data"
**Penyebab**: Jumlah foto tidak match dengan jumlah akun
**Solusi**: Upload foto sesuai jumlah akun (contoh: 3 akun = 3 foto)

## 🎯 TIPS

1. **Gunakan format dengan label** untuk mengurangi kesalahan
2. **Copy template** di atas dan isi sesuai data
3. **Pastikan URL valid** (dimulai dengan http:// atau https://)
4. **Cek jumlah foto** sebelum submit
5. **Test dengan 1 akun dulu** sebelum input banyak

## 📞 DEBUG

Jika masih error setelah mengikuti panduan ini:

1. Buka **Developer Console** (F12)
2. Submit form
3. Lihat error di tab **Console**
4. Screenshot dan laporkan error tersebut




