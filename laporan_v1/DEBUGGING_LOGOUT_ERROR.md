# Debugging: Error "Ke Logout" saat Proses

## Masalah
User melaporkan: "ketika proses malahan ke log out"

Dari screenshot Network tab terlihat:
- Request ke `api_rekap.php` mengembalikan **status error (merah)**
- Kemungkinan ada PHP error yang menyebabkan halaman error/redirect

## Perbaikan yang Sudah Dilakukan

### 1. ✅ Enhanced Error Logging di Backend (api_rekap.php)
```php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
```

**Fitur:**
- Semua error PHP akan dicatat ke `error.log`
- Error tidak ditampilkan di output (agar tidak break JSON response)
- Detail logging untuk setiap step proses

### 2. ✅ FormData Validation di Frontend
```javascript
// CRITICAL: Verify reportType exists
const reportTypes = formData.getAll('reportType[]');
if (reportTypes.length === 0) {
    alert('ERROR KRITIS: Jenis laporan tidak terdeteksi!');
    return false;
}
```

**Fitur:**
- Validasi FormData sebelum dikirim
- console.table() untuk debug FormData contents
- Alert jika reportType[] kosong

### 3. ✅ Enhanced Response Validation
```javascript
// Check if response is OK
if (!response.ok) {
    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
}

// Check if response is JSON
if (!contentType.includes('application/json')) {
    // Log response body for debugging
    throw new Error('Server tidak mengembalikan JSON');
}
```

**Fitur:**
- Cek HTTP status code
- Cek Content-Type header
- Log response body jika bukan JSON
- Error message yang jelas

### 4. ✅ Test API Endpoint
File baru: `test_api.php`
```
http://localhost:8080/3.Rekap_Laporan_2025_v2/test_api.php
```

**Fungsi:**
- Test apakah server bisa diakses
- Test apakah POST data diterima
- Tidak butuh data lengkap, hanya test koneksi

## Cara Debugging

### Step 1: Clear Cache Browser
**WAJIB!** Browser cache bisa menyimpan JavaScript lama

**Chrome/Edge:**
```
Ctrl + Shift + Delete → Clear Cache → Ctrl + F5
```

**Firefox:**
```
Ctrl + Shift + Delete → Clear Cache → Ctrl + F5
```

### Step 2: Buka Browser Console (F12)
Sebelum klik "Upload dan Proses", buka DevTools:
1. Tekan **F12**
2. Pilih tab **Console**
3. Clear console: Klik icon 🚫 atau Ctrl+L

### Step 3: Monitor Console Saat Proses
Klik "Upload dan Proses", perhatikan console:

**✅ Normal Flow:**
```
✅ Attaching submit event listener to wizard form
🚀 Form submit event triggered in AJAX handler!
=== FORMDATA CONTENTS BEFORE SEND ===
reportType[] count: 1
reportType[] values: ["Laporan KBD"]
🚀 Sending fetch request to api_rekap.php...
📥 Response received: {status: 200, statusText: "OK"}
```

**❌ Error Flow (contoh):**
```
❌ FATAL: reportType[] is empty!
// atau
❌ Response is not JSON! Content-Type: text/html
Response body: <br /><b>Fatal error</b>: ...
```

### Step 4: Check Network Tab
1. Pilih tab **Network** di DevTools
2. Klik "Upload dan Proses"
3. Cari request ke `api_rekap.php`
4. Klik request tersebut

**Cek:**
- **Status Code**: Harus 200 OK (hijau), bukan 500/404 (merah)
- **Response Tab**: Harus JSON, bukan HTML error
- **Headers Tab → Content-Type**: Harus `application/json`

### Step 5: Check Error Log
File: `error.log` di root folder

**Buka dengan:**
```bash
notepad error.log
# atau
tail -f error.log
```

**Cari:**
- `PHP Fatal error`
- `PHP Warning`
- `API_REKAP: Processing request`
- Stack trace

## Kemungkinan Penyebab Error

### 1. ❌ FormData Kosong
**Gejala:** Alert "ERROR KRITIS: Jenis laporan tidak terdeteksi!"
**Penyebab:** Checkbox reportType[] tidak tercentang
**Solusi:** Refresh halaman, centang jenis laporan lagi

### 2. ❌ PHP Error
**Gejala:** Response HTML instead of JSON
**Penyebab:** Fatal error di api_rekap.php atau file yang di-require
**Solusi:** Cek `error.log`, fix PHP error

### 3. ❌ File Upload Terlalu Besar
**Gejala:** Request timeout atau 413 error
**Penyebab:** php.ini limits: `upload_max_filesize`, `post_max_size`
**Solusi:** Increase limits atau upload file lebih kecil

### 4. ❌ Session Expired
**Gejala:** Redirect ke login page
**Penyebab:** Kalau ada authentication di index.php
**Solusi:** Cek apakah ada session check di api_rekap.php

### 5. ❌ Memory Limit
**Gejala:** "Allowed memory size exhausted"
**Penyebab:** Processing file terlalu besar
**Solusi:** Increase `memory_limit` di php.ini

## Test Langkah-langkah

### Test 1: API Accessibility
```
http://localhost:8080/3.Rekap_Laporan_2025_v2/test_api.php
```
**Expected:** JSON response dengan "success": true

### Test 2: Simple Laporan KBD
1. Pilih **Laporan KBD** saja
2. Input minimal data:
   - Tanggal: Hari ini
   - Patrol Report: 4 baris (1 akun)
   - Upload 1 Excel
   - Upload 1 Gambar Cipop
   - Upload 1 Screenshot Patrol
3. Klik "Upload dan Proses"
4. Monitor console & network

### Test 3: Check Generated Files
Kalau proses sukses, cek folder `/hasil`:
```
hasil/
  - PATROLI_ddmmyyyy.docx
  - CIPOP_ddmmyyyy.docx
  - LAMPIRAN_PATROLI_ddmmyyyy.pdf
```

## Troubleshooting Quick Guide

| Gejala | Cek | Solusi |
|--------|-----|--------|
| Page refresh/reload | Console for errors | Clear cache, try again |
| Alert "ERROR KRITIS" | reportType checkbox | Centang jenis laporan |
| Network status merah | Response tab | Cek error.log |
| Response HTML bukan JSON | Response preview | Fix PHP error |
| Timeout | File size | Reduce file size or increase limits |

## Files Modified

1. ✅ `api_rekap.php` - Enhanced error logging
2. ✅ `js/ajax-handler.js` - FormData validation & response validation
3. ✅ `test_api.php` - New test endpoint
4. ✅ `index.php` - Removed target="_blank"

## Status
✅ Error logging enabled
✅ FormData validation added
✅ Response validation enhanced
✅ Test endpoint created
🔄 Waiting for user test results


