const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());
const fs = require('fs').promises;
const fsSync = require('fs'); // Tambahkan untuk cek/membuat folder secara sinkron
const path = require('path');
const sharp = require('sharp'); // Tambahkan di bagian atas, pastikan sudah install: npm install sharp

// Tambahkan konstanta untuk folder penyimpanan sesi
const sessionDir = path.join(__dirname, 'browser_sessions');
if (!fsSync.existsSync(sessionDir)) {
    fsSync.mkdirSync(sessionDir);
}

// Mapping domain ke tipe platform dan cookies
const platformMap = [
    {
        name: 'facebook',
        match: /facebook\.com/,
        cookies: path.join(__dirname, 'login_session', 'fb1.json')
    },
    {
        name: 'instagram',
        match: /instagram\.com/,
        cookies: path.join(__dirname, 'login_session', 'ig1.json')
    },
    {
        name: 'xcom',
        match: /(?:x\.com|twitter\.com)/,
        cookies: path.join(__dirname, 'login_session', 'x1.json')
    },
    {
        name: 'tiktok',
        match: /tiktok\.com/,
        cookies: path.join(__dirname, 'login_session', 'tk1.json')
    },
    {
        name: 'snackvideo',
        match: /snackvideo\.com/,
        cookies: null // tanpa cookies
    },
    {
        name: 'youtube',
        match: /youtube\.com/,
        cookies: null // tanpa cookies
    }
];

// Pastikan folder 'ss' ada, jika belum maka buat
const ssDir = path.join(__dirname, 'ss');
if (!fsSync.existsSync(ssDir)) {
    fsSync.mkdirSync(ssDir);
}

async function loadCustomCookies(page, cookieFile) {
    try {
        const cookiesString = await fs.readFile(cookieFile);
        const cookies = JSON.parse(cookiesString);
        await page.setCookie(...cookies);
        return true;
    } catch (err) {
        console.error('Gagal memuat cookies:', err.message);
        return false;
    }
}

async function waitForFullLoad(page, timeout = 60000) {
    await page.goto(page.url(), { waitUntil: ['networkidle2', 'load'], timeout });
    await new Promise(resolve => setTimeout(resolve, 2000));
}

async function saveCompressedJpeg(inputBuffer, outputPath, maxSizeKB = 200) {
    let quality = 80;
    let buffer = await sharp(inputBuffer).jpeg({ quality }).toBuffer();

    // Kompres hingga ukuran < maxSizeKB atau quality minimum 30
    while (buffer.length > maxSizeKB * 1024 && quality > 30) {
        quality -= 10;
        buffer = await sharp(inputBuffer).jpeg({ quality }).toBuffer();
    }
    await sharp(buffer).toFile(outputPath);
}

async function loadBrowserSession(platformName) {
    const sessionPath = path.join(sessionDir, `${platformName}_session.json`);
    try {
        const sessionData = await fs.readFile(sessionPath, 'utf-8');
        return JSON.parse(sessionData);
    } catch (err) {
        return null;
    }
}

async function saveBrowserSession(platformName, page) {
    const sessionPath = path.join(sessionDir, `${platformName}_session.json`);
    try {
        const client = await page.target().createCDPSession();
        const { cookies } = await client.send('Network.getAllCookies');
        const localStorage = await page.evaluate(() => {
            let items = {};
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                items[key] = localStorage.getItem(key);
            }
            return items;
        });

        const sessionData = {
            cookies,
            localStorage,
            platformName,
            timestamp: Date.now()
        };

        await fs.writeFile(sessionPath, JSON.stringify(sessionData, null, 2));
        console.log(`Sesi browser untuk ${platformName} berhasil disimpan`);
    } catch (err) {
        console.error(`Gagal menyimpan sesi browser untuk ${platformName}:`, err.message);
    }
}

async function restoreBrowserSession(page, platformName) {
    const sessionData = await loadBrowserSession(platformName);
    if (!sessionData) return false;

    try {
        // Restore cookies
        if (sessionData.cookies && sessionData.cookies.length > 0) {
            const client = await page.target().createCDPSession();
            await client.send('Network.clearBrowserCookies');
            await client.send('Network.setCookies', { cookies: sessionData.cookies });
        }

        // Restore localStorage
        if (sessionData.localStorage) {
            await page.evaluate((data) => {
                localStorage.clear();
                for (const [key, value] of Object.entries(data)) {
                    localStorage.setItem(key, value);
                }
            }, sessionData.localStorage);
        }

        console.log(`Sesi browser untuk ${platformName} berhasil dipulihkan`);
        return true;
    } catch (err) {
        console.error(`Gagal memulihkan sesi browser untuk ${platformName}:`, err.message);
        return false;
    }
}

async function takeScreenshotWithCookies(url, cookieFile, outputPath, platformName = '', browser = null) {
    if (!browser) {
        throw new Error('Browser instance is required');
    }

    let page = null;
    try {
        page = await browser.newPage();
        await page.setViewport({ width: 375, height: 800, isMobile: true, deviceScaleFactor: 3 });
        await page.setUserAgent(
            'Mozilla/5.0 (iPhone; CPU iPhone OS 13_0 like Mac OS X) ' +
            'AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0 Mobile/15E148 Safari/604.1'
        );

        // Coba pulihkan sesi yang tersimpan
        const sessionRestored = await restoreBrowserSession(page, platformName);
        if (!sessionRestored) {
            // Jika tidak ada sesi tersimpan, gunakan cookies dari file
            await loadCustomCookies(page, cookieFile);
        }

        // Untuk platform X (Twitter), pastikan konten utama sudah tampil
        if (platformName === 'xcom') {
            await page.goto(url, { waitUntil: ['networkidle2', 'domcontentloaded'], timeout: 60000 });
            try {
                await page.waitForSelector('div[data-testid="tweet"], article', { timeout: 15000 });
                await page.waitForTimeout(3000);
            } catch (e) {
                console.log('Konten utama X tidak ditemukan, lanjut screenshot.');
            }    } else if (platformName === 'instagram') {
            console.log('periksa apakah ada popup login Instagram...');
            await page.goto(url, { waitUntil: ['networkidle2', 'load'], timeout: 60000 });
            try {
                // Tunggu beberapa saat untuk popup muncul
                await new Promise(resolve => setTimeout(resolve, 2000));

                // Klik di area atas halaman untuk menutup popup jika ada
                await page.mouse.click(187, 12); // Klik di tengah atas halaman
                console.log('Klik di area atas untuk menutup popup');
                await new Promise(resolve => setTimeout(resolve, 1000)); // Tunggu popup hilang

                // Tunggu konten utama Instagram muncul
                await page.waitForSelector('article', { timeout: 15000 });
                await new Promise(resolve => setTimeout(resolve, 2000));
            } catch (e) {
                console.log('Gagal menangani popup atau memuat konten Instagram:', e.message);
            }
        } else {
            await page.goto(url, { waitUntil: ['networkidle2', 'load'], timeout: 60000 });
            await new Promise(resolve => setTimeout(resolve, 2000));
        }

        // Khusus untuk tiktok: klik tombol "Not now" jika ada
        if (platformName === 'tiktok') {
            try {
                const [notNowBtn] = await page.$x("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'not now') or contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'nanti saja')]");
                if (notNowBtn) {
                    await notNowBtn.click();
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
            } catch (e) {
                console.log('Tombol "Not now" tidak ditemukan atau gagal diklik.');
            }
        }

        // Simpan sesi browser setelah login berhasil
        await saveBrowserSession(platformName, page);

        // Screenshot ke buffer PNG, lalu kompres ke JPEG < 200KB
        const buffer = await page.screenshot({ type: 'png', fullPage: false });
        const jpegPath = path.join(ssDir, outputPath.replace(/\.\w+$/, '.jpg'));
        await saveCompressedJpeg(buffer, jpegPath, 200);

        console.log(`Screenshot saved to ss/${path.basename(jpegPath)}`);
        return browser;
    } finally {
        if (page) {
            await page.close();
        }
    }
}

async function takeScreenshotNoCookies(url, outputPath, browser = null) {
    if (!browser) {
        throw new Error('Browser instance is required');
    }

    let page = null;
    try {
        page = await browser.newPage();
        await page.setViewport({ width: 375, height: 800, isMobile: true, deviceScaleFactor: 3 });
        await page.setUserAgent(
            'Mozilla/5.0 (iPhone; CPU iPhone OS 13_0 like Mac OS X) ' +
            'AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0 Mobile/15E148 Safari/604.1'
        );
        await page.goto(url, { waitUntil: ['networkidle2', 'load'], timeout: 60000 });
        await new Promise(resolve => setTimeout(resolve, 2000));

        // Screenshot ke buffer PNG, lalu kompres ke JPEG < 200KB
        const buffer = await page.screenshot({ type: 'png', fullPage: false });
        const jpegPath = path.join(ssDir, outputPath.replace(/\.\w+$/, '.jpg'));
        await saveCompressedJpeg(buffer, jpegPath, 200);

        console.log(`Screenshot saved to ss/${path.basename(jpegPath)}`);
    } finally {
        if (page) {
            await page.close();
        }
    }
    return browser;
}

// Ambil parameter dari command line (mulai dari index 2)
// Format: node ambil_ss.js jenis_tangkapan url1 url2 ...
// jenis_tangkapan: cipop | patroli | upaya
const args = process.argv.slice(2);
if (args.length < 2) {
    console.log('Masukkan jenis tangkapan layar dan link sebagai parameter, contoh:');
    console.log('node ambil_ss.js cipop "https://facebook.com/..." "https://instagram.com/..."');
    process.exit(1);
}
const jenisTangkapan = args[0].toLowerCase();
const links = args.slice(1);

const jenisPrefix = (() => {
    if (jenisTangkapan === 'cipop') return 'cipop';
    if (jenisTangkapan === 'patroli') return 'patroli';
    if (jenisTangkapan === 'upaya') return 'upaya';
    return 'screenshot';
})();

(async () => {
    let browser = null;
    try {
        // Inisialisasi browser di awal
        browser = await puppeteer.launch({
            headless: true,
            executablePath: "C:\\Users\\Administrator\\.cache\\puppeteer\\chrome\\win64-137.0.7151.119\\chrome-win64\\chrome.exe",
            args: [`--no-sandbox`, `--headless`, `--disable-gpu`, `--disable-dev-shm-usage`],
            defaultViewport: {
                width: 375,
                height: 800,
                isMobile: true,
                deviceScaleFactor: 3
            }
        });

        // Proses semua URL secara berurutan
        for (const url of links) {
            const platform = platformMap.find(p => p.match.test(url));
            let outputName = `${jenisPrefix}_unknown_${Date.now()}.png`;
            
            if (platform) {
                outputName = `${jenisPrefix}_${platform.name}_${Date.now()}.png`;
                try {
                    if (platform.cookies) {
                        await takeScreenshotWithCookies(url, platform.cookies, outputName, platform.name, browser);
                    } else {
                        await takeScreenshotNoCookies(url, outputName, browser);
                    }
                    console.log(`Berhasil mengambil screenshot untuk ${url}`);
                } catch (err) {
                    console.error(`Gagal screenshot ${platform.name}:`, err.message);
                }
            } else {
                try {
                    await takeScreenshotNoCookies(url, outputName, browser);
                    console.log(`Berhasil mengambil screenshot untuk ${url}`);
                } catch (err) {
                    console.error(`Gagal screenshot (unknown platform):`, err.message);
                }
            }
        }

        // Setelah semua screenshot selesai
        console.log('\nSemua screenshot telah berhasil diambil. Browser akan ditutup.');
    } finally {
        if (browser) {
                await browser.close();
        }
    }
})();