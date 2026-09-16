<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow">
    <title>Berbagi Lokasi</title>
    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-card: #131927;
            --bg-glass: rgba(19, 25, 39, 0.85);
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.3);
            --accent-secondary: #06d6a0;
            --text-primary: #e8ecf4;
            --text-secondary: #8892a8;
            --text-muted: #5a6478;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --border: rgba(255,255,255,0.06);
            --radius: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animated background */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }
        .bg-orb:nth-child(1) { width: 400px; height: 400px; background: var(--accent); top: -100px; right: -100px; }
        .bg-orb:nth-child(2) { width: 300px; height: 300px; background: #8b5cf6; bottom: -80px; left: -80px; animation-delay: -7s; }
        .bg-orb:nth-child(3) { width: 200px; height: 200px; background: var(--accent-secondary); top: 50%; left: 50%; animation-delay: -14s; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(2.5); opacity: 0; }
        }

        @keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        .card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px 24px;
            text-align: center;
        }

        /* Location icon */
        .location-icon {
            width: 80px; height: 80px;
            margin: 0 auto 24px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .location-icon .ring {
            position: absolute; inset: 0;
            border-radius: 50%;
            border: 2px solid var(--accent);
            opacity: 0;
        }
        .location-icon.active .ring {
            animation: pulse-ring 2s ease-out infinite;
        }
        .location-icon .ring:nth-child(2) { animation-delay: 0.6s; }
        .location-icon .ring:nth-child(3) { animation-delay: 1.2s; }

        .location-icon svg {
            width: 40px; height: 40px;
            fill: var(--accent);
            z-index: 1;
        }

        h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 28px;
        }

        /* Status indicators */
        .status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        .status-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 12px;
            text-align: center;
        }
        .status-item .label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }
        .status-item .value {
            font-size: 1rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }
        .status-item .value.success { color: var(--success); }
        .status-item .value.warning { color: var(--warning); }
        .status-item .value.danger { color: var(--danger); }
        .status-item .value.accent { color: var(--accent); }

        /* Connection indicator */
        .connection-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .connection-bar.connected { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
        .connection-bar.sending { background: rgba(59, 130, 246, 0.1); color: var(--accent); border: 1px solid rgba(59, 130, 246, 0.2); }
        .connection-bar.error { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }
        .connection-bar.waiting { background: rgba(245, 158, 11, 0.1); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.2); }

        .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: currentColor;
        }
        .dot.pulse { animation: pulse-ring 1.5s ease-out infinite; }

        /* Button */
        .btn-primary {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #2563eb);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px var(--accent-glow); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-primary .spinner {
            display: none;
            width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin-slow 0.8s linear infinite;
            margin: 0 auto;
        }
        .btn-primary.loading .btn-text { display: none; }
        .btn-primary.loading .spinner { display: block; }

        /* Coordinate display */
        .coords {
            margin-top: 20px;
            padding: 16px;
            background: rgba(0,0,0,0.3);
            border-radius: 12px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.8;
            text-align: left;
            display: none;
        }
        .coords.visible { display: block; }
        .coords span { color: var(--accent); }

        /* Error state */
        .error-card {
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: none;
        }
        .error-card.visible { display: block; }
        .error-card h3 { color: var(--danger); font-size: 0.95rem; margin-bottom: 8px; }
        .error-card p { color: var(--text-secondary); font-size: 0.8rem; line-height: 1.6; }

        /* Footer */
        .footer {
            margin-top: 20px;
            font-size: 0.7rem;
            color: var(--text-muted);
            line-height: 1.5;
        }
        .footer a { color: var(--text-secondary); text-decoration: none; }

        /* Loading animation overlay */
        #loadingOverlay {
            position: fixed;
            inset: 0;
            background: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            transition: opacity 0.5s ease;
        }
        #loadingOverlay.hidden { opacity: 0; pointer-events: none; }
        .loader { width: 40px; height: 40px; border: 3px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin-slow 1s linear infinite; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="bg-orb"></div>
    <div class="bg-orb"></div>
    <div class="bg-orb"></div>

    <div id="loadingOverlay"><div class="loader"></div></div>

    <div class="container">
        <div class="card">
            <div class="location-icon" id="locationIcon">
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="ring"></div>
                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </div>

            <h1 id="mainTitle">Berbagi Lokasi</h1>
            <p class="subtitle" id="mainSubtitle">
                Anda diminta untuk berbagi lokasi secara realtime. Tekan tombol di bawah untuk memulai.
            </p>

            <!-- Error display -->
            <div class="error-card" id="errorCard">
                <h3 id="errorTitle">Izin Ditolak</h3>
                <p id="errorMessage"></p>
            </div>

            <!-- Connection status -->
            <div class="connection-bar waiting" id="connectionBar">
                <div class="dot"></div>
                <span id="connectionText">Menunggu izin lokasi...</span>
            </div>

            <!-- Status grid -->
            <div class="status-grid">
                <div class="status-item">
                    <div class="label">Status</div>
                    <div class="value accent" id="statusValue">Siap</div>
                </div>
                <div class="status-item">
                    <div class="label">Akurasi</div>
                    <div class="value" id="accuracyValue">—</div>
                </div>
                <div class="status-item">
                    <div class="label">Update Terkirim</div>
                    <div class="value accent" id="updateCount">0</div>
                </div>
                <div class="status-item">
                    <div class="label">Sisa Waktu</div>
                    <div class="value warning" id="timeRemaining">—</div>
                </div>
            </div>

            <!-- Start button -->
            <button class="btn-primary" id="startBtn" onclick="startTracking()">
                <span class="btn-text">Mulai Berbagi Lokasi</span>
                <div class="spinner"></div>
            </button>

            <!-- Coordinate display -->
            <div class="coords" id="coordsDisplay">
                <div>lat: <span id="displayLat">—</span></div>
                <div>lng: <span id="displayLng">—</span></div>
                <div>acc: <span id="displayAcc">—</span> meter</div>
                <div>upd: <span id="displayTime">—</span></div>
            </div>

            <div class="footer">
                <p>Lokasi Anda dikirim secara aman via HTTPS.<br>
                Anda bisa menutup halaman ini kapan saja untuk berhenti.</p>
            </div>
        </div>
    </div>

    <script>
        // ============================================================
        // Configuration — injected from Laravel Blade
        // ============================================================
        const CONFIG = {
            token: '{{ $token }}',
            sessionId: {{ $sessionId }},
            expiresAt: new Date('{{ $expiresAt }}'),
            apiUrl: '{{ url("/api/locations") }}',
            sendInterval: 5000,      // Send every 5 seconds
            maxRetries: 3,
            retryDelay: 2000,
        };

        // ============================================================
        // State
        // ============================================================
        let watchId = null;
        let sendTimer = null;
        let updatesSent = 0;
        let lastPosition = null;
        let pendingQueue = [];
        let isOnline = navigator.onLine;
        let countdownTimer = null;

        // ============================================================
        // DOM References
        // ============================================================
        const $ = (id) => document.getElementById(id);

        // ============================================================
        // Initialization
        // ============================================================
        window.addEventListener('load', () => {
            setTimeout(() => {
                $('loadingOverlay').classList.add('hidden');
            }, 500);

            startCountdown();
            checkHttps();
            checkExpiry();
        });

        // Online/offline detection
        window.addEventListener('online', () => {
            isOnline = true;
            flushQueue();
        });
        window.addEventListener('offline', () => {
            isOnline = false;
            updateConnection('error', 'Koneksi terputus — data antri lokal');
        });

        // ============================================================
        // HTTPS Check
        // ============================================================
        function checkHttps() {
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                showError('HTTPS Diperlukan',
                    'Geolocation API membutuhkan koneksi HTTPS. Pastikan Anda mengakses halaman ini melalui tautan yang aman.');
                $('startBtn').disabled = true;
            }
        }

        // ============================================================
        // Expiry Check & Countdown
        // ============================================================
        function checkExpiry() {
            if (new Date() >= CONFIG.expiresAt) {
                showError('Sesi Kedaluwarsa', 'Tautan pelacakan ini telah kedaluwarsa. Hubungi operator untuk tautan baru.');
                $('startBtn').disabled = true;
                stopTracking();
                return true;
            }
            return false;
        }

        function startCountdown() {
            countdownTimer = setInterval(() => {
                const now = new Date();
                const diff = CONFIG.expiresAt - now;

                if (diff <= 0) {
                    $('timeRemaining').textContent = 'Habis';
                    $('timeRemaining').className = 'value danger';
                    checkExpiry();
                    clearInterval(countdownTimer);
                    return;
                }

                const mins = Math.floor(diff / 60000);
                const secs = Math.floor((diff % 60000) / 1000);
                $('timeRemaining').textContent = `${mins}m ${secs}s`;

                if (mins < 5) {
                    $('timeRemaining').className = 'value danger';
                } else if (mins < 15) {
                    $('timeRemaining').className = 'value warning';
                }
            }, 1000);
        }

        // ============================================================
        // Start Tracking
        // ============================================================
        function startTracking() {
            if (!navigator.geolocation) {
                showError('Tidak Didukung', 'Browser Anda tidak mendukung Geolocation API. Gunakan browser modern seperti Chrome atau Firefox.');
                return;
            }

            if (checkExpiry()) {
                return;
            }

            const btn = $('startBtn');
            btn.classList.add('loading');
            btn.disabled = true;

            $('locationIcon').classList.add('active');
            updateConnection('waiting', 'Meminta izin lokasi...');

            navigator.geolocation.watchPosition(
                onPositionSuccess,
                onPositionError,
                {
                    enableHighAccuracy: true,
                    maximumAge: 10000,
                    timeout: 15000,
                }
            );
        }

        // ============================================================
        // Geolocation Callbacks
        // ============================================================
        function onPositionSuccess(position) {
            const { latitude, longitude, accuracy, altitude, speed, heading } = position.coords;

            lastPosition = {
                latitude,
                longitude,
                accuracy: accuracy || null,
                altitude: altitude || null,
                speed: speed || null,
                heading: heading || null,
                recorded_at: new Date(position.timestamp).toISOString(),
            };

            // Update UI
            $('displayLat').textContent = latitude.toFixed(8);
            $('displayLng').textContent = longitude.toFixed(8);
            $('displayAcc').textContent = accuracy ? accuracy.toFixed(1) : '—';
            $('displayTime').textContent = new Date().toLocaleTimeString('id-ID');
            $('coordsDisplay').classList.add('visible');
            $('statusValue').textContent = 'Aktif';
            $('statusValue').className = 'value success';

            // Accuracy indicator
            if (accuracy && accuracy > 500) {
                $('accuracyValue').textContent = `${accuracy.toFixed(0)}m`;
                $('accuracyValue').className = 'value danger';
            } else if (accuracy && accuracy > 100) {
                $('accuracyValue').textContent = `${accuracy.toFixed(0)}m`;
                $('accuracyValue').className = 'value warning';
            } else if (accuracy) {
                $('accuracyValue').textContent = `${accuracy.toFixed(0)}m`;
                $('accuracyValue').className = 'value success';
            }

            // Throttled sending: only send every CONFIG.sendInterval
            if (!sendTimer) {
                sendLocation(lastPosition);
                sendTimer = setInterval(() => {
                    if (lastPosition) {
                        sendLocation(lastPosition);
                    }
                }, CONFIG.sendInterval);
            }

            $('startBtn').style.display = 'none';
            $('mainTitle').textContent = 'Lokasi Sedang Dibagikan';
            $('mainSubtitle').textContent = 'Koordinat Anda dikirim secara otomatis. Tutup halaman ini untuk berhenti.';
        }

        function onPositionError(error) {
            const btn = $('startBtn');
            btn.classList.remove('loading');
            btn.disabled = false;
            $('locationIcon').classList.remove('active');

            switch (error.code) {
                case error.PERMISSION_DENIED:
                    showError('Izin Lokasi Ditolak',
                        'Anda menolak akses lokasi. Untuk mengaktifkan kembali:\n\n' +
                        '• Android Chrome: Ketuk ikon gembok di address bar → Izin situs → Lokasi → Izinkan\n' +
                        '• iPhone Safari: Pengaturan → Safari → Lokasi → Izinkan\n\n' +
                        'Setelah mengubah izin, muat ulang halaman ini.');
                    updateConnection('error', 'Izin lokasi ditolak');
                    $('statusValue').textContent = 'Ditolak';
                    $('statusValue').className = 'value danger';
                    break;

                case error.POSITION_UNAVAILABLE:
                    showError('Lokasi Tidak Tersedia',
                        'Perangkat Anda tidak bisa mendapatkan lokasi saat ini. Pastikan GPS aktif dan Anda berada di area dengan sinyal yang cukup.');
                    updateConnection('error', 'GPS tidak tersedia');
                    $('statusValue').textContent = 'Error';
                    $('statusValue').className = 'value danger';
                    break;

                case error.TIMEOUT:
                    showError('Waktu Habis',
                        'Permintaan lokasi memakan waktu terlalu lama. Pastikan GPS aktif dan coba lagi.');
                    updateConnection('warning', 'Timeout — coba lagi');
                    $('statusValue').textContent = 'Timeout';
                    $('statusValue').className = 'value warning';
                    break;
            }
        }

        // ============================================================
        // Send Location to API
        // ============================================================
        async function sendLocation(posData, retryCount = 0) {
            if (checkExpiry()) {
                return;
            }

            if (!isOnline) {
                pendingQueue.push({ ...posData });
                updateConnection('error', `Offline — ${pendingQueue.length} data antri`);
                return;
            }

            updateConnection('sending', 'Mengirim koordinat...');

            try {
                const response = await fetch(CONFIG.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${CONFIG.token}`,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(posData),
                });

                if (response.status === 401 || response.status === 410) {
                    const data = await response.json();
                    if (data.code === 'SESSION_EXPIRED' || data.code === 'TOKEN_INVALID') {
                        stopTracking();
                        showError('Sesi Berakhir', 'Sesi pelacakan Anda telah berakhir atau dibatalkan oleh operator.');
                        return;
                    }
                }

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                updatesSent++;
                $('updateCount').textContent = updatesSent;
                updateConnection('connected', `Terkirim — ${updatesSent} update`);

            } catch (err) {
                console.error('Send failed:', err);

                if (retryCount < CONFIG.maxRetries) {
                    setTimeout(() => sendLocation(posData, retryCount + 1), CONFIG.retryDelay);
                    updateConnection('warning', `Gagal — retry ${retryCount + 1}/${CONFIG.maxRetries}`);
                } else {
                    pendingQueue.push({ ...posData });
                    updateConnection('error', `Gagal — ${pendingQueue.length} data antri`);
                }
            }
        }

        // Flush queued data when back online
        async function flushQueue() {
            while (pendingQueue.length > 0 && isOnline) {
                const data = pendingQueue.shift();
                await sendLocation(data);
                await new Promise(r => setTimeout(r, 500));
            }
        }

        // ============================================================
        // Stop Tracking
        // ============================================================
        function stopTracking() {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (sendTimer) {
                clearInterval(sendTimer);
                sendTimer = null;
            }
            $('locationIcon').classList.remove('active');
        }

        // ============================================================
        // UI Helpers
        // ============================================================
        function updateConnection(type, text) {
            const bar = $('connectionBar');
            bar.className = `connection-bar ${type}`;
            $('connectionText').textContent = text;

            const dot = bar.querySelector('.dot');
            dot.className = type === 'sending' ? 'dot pulse' : 'dot';
        }

        function showError(title, message) {
            $('errorTitle').textContent = title;
            $('errorMessage').textContent = message;
            $('errorCard').classList.add('visible');
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', stopTracking);
    </script>
</body>
</html>
