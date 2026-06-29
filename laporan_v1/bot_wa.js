const { default: makeWASocket, useMultiFileAuthState, fetchLatestBaileysVersion, DisconnectReason } = require('@whiskeysockets/baileys');
const { Boom, isBoom } = require('@hapi/boom'); // perbaikan di sini
const fs = require('fs');
const axios = require('axios'); // Tambahkan di bagian atas jika belum ada

async function startBot() {
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');
    const { version } = await fetchLatestBaileysVersion();

    const sock = makeWASocket({
        version,
        printQRInTerminal: true,
        auth: state
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        if (type !== 'notify') return;
        for (const msg of messages) {
            if (!msg.message) continue;
            const from = msg.key.remoteJid;
            const sender = msg.key.participant || from;

            // Tampilkan chat id (remoteJid) dan sender di terminal
            console.log(`Chat ID (remoteJid): ${from}, Sender: ${sender}`);

            // Hanya balas pesan dari grup dengan ID berikut
            if (from !== '6282278417204-1479263031@g.us') continue;

            const text = msg.message.conversation || msg.message.extendedTextMessage?.text || '';

            if (text.toLowerCase().includes('rekap laporan patroli')) {
                let laporan = 'Rekap Laporan Patroli:\n- Patroli 1: OK\n- Patroli 2: OK\n- Patroli 3: Tidak hadir';
                await sock.sendMessage(from, { text: laporan });
            } else if (text.toLowerCase() === 'menu') {
                // Ubah menu menjadi tombol
                const buttonMessage = {
                    text: 'Silakan pilih menu rekap:',
                    footer: 'Menu Rekap',
                    buttons: [
                        { buttonId: 'rekap_pagi', buttonText: { displayText: 'Rekap Patroli Pagi' }, type: 1 },
                        { buttonId: 'rekap_landy', buttonText: { displayText: 'Rekap Patroli Landy' }, type: 1 },
                        { buttonId: 'rekap_kabinda', buttonText: { displayText: 'Rekap Laporan Kabinda' }, type: 1 }
                    ],
                    headerType: 1
                };
                await sock.sendMessage(from, buttonMessage);
            } else if (msg.message.buttonsResponseMessage) {
                // Tangani respon tombol
                const selected = msg.message.buttonsResponseMessage.selectedButtonId;
                if (selected === 'rekap_pagi') {
                    await sock.sendMessage(from, { text: 'Berikut Rekap Patroli Pagi:\n- Data 1\n- Data 2' });
                } else if (selected === 'rekap_landy') {
                    await sock.sendMessage(from, { text: 'Berikut Rekap Patroli Landy:\n- Data 1\n- Data 2' });
                } else if (selected === 'rekap_kabinda') {
                    await sock.sendMessage(from, { text: 'Berikut Rekap Laporan Kabinda:\n- Data 1\n- Data 2' });
                }
            }
        }
    });

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect } = update;
        if (connection === 'close') {
            const shouldReconnect =
                lastDisconnect &&
                lastDisconnect.error &&
                isBoom(lastDisconnect.error) && // perbaikan di sini
                lastDisconnect.error.output?.statusCode !== DisconnectReason.loggedOut;
            if (shouldReconnect) {
                startBot();
            } else {
                console.log('You are logged out.');
            }
        }
        console.log('connection update', update);
    });
}

startBot();