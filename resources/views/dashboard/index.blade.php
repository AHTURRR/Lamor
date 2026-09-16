<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — LacakLokasi</title>
    <meta name="description" content="Dashboard admin untuk pelacakan lokasi realtime.">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])

    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-sidebar: rgba(13, 17, 28, 0.95);
            --bg-card: rgba(19, 25, 39, 0.9);
            --bg-input: rgba(0, 0, 0, 0.35);
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --accent-glow: rgba(59, 130, 246, 0.25);
            --accent-secondary: #06d6a0;
            --purple: #8b5cf6;
            --text-primary: #e8ecf4;
            --text-secondary: #8892a8;
            --text-muted: #5a6478;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --border: rgba(255, 255, 255, 0.06);
            --sidebar-width: 380px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
        }

        /* Layout */
        .app-layout {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .logo-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo svg { width: 26px; height: 26px; fill: var(--accent); }
        .logo span { font-size: 1.15rem; font-weight: 700; letter-spacing: -0.02em; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-logout:hover { border-color: var(--danger); color: var(--danger); }

        /* New Session Form */
        .new-session {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .new-session h3 {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
        }

        .form-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .input {
            flex: 1;
            padding: 10px 14px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.85rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        .input:focus { border-color: var(--accent); }
        .input::placeholder { color: var(--text-muted); }
        .input.small { max-width: 120px; }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            font-size: 0.825rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: #fff;
        }
        .btn-primary:hover { box-shadow: 0 4px 15px var(--accent-glow); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-danger {
            background: rgba(239,68,68,0.1);
            color: var(--danger);
            border: 1px solid rgba(239,68,68,0.2);
        }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.7rem;
            border-radius: 8px;
        }

        /* Session list */
        .session-list-header {
            padding: 16px 20px 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .session-list-header h3 {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .session-count {
            font-size: 0.7rem;
            color: var(--text-muted);
            background: rgba(255,255,255,0.05);
            padding: 3px 8px;
            border-radius: 6px;
        }

        .session-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
        }

        .session-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .session-card:hover { background: rgba(255, 255, 255, 0.04); border-color: rgba(255,255,255,0.1); }
        .session-card.active { border-color: var(--accent); background: rgba(59,130,246,0.05); }

        .session-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .session-phone {
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
        }

        .status-badge {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .status-badge.active { background: rgba(16,185,129,0.15); color: var(--success); }
        .status-badge.pending { background: rgba(245,158,11,0.15); color: var(--warning); }
        .status-badge.expired { background: rgba(239,68,68,0.1); color: var(--danger); }
        .status-badge.revoked { background: rgba(90,100,120,0.2); color: var(--text-muted); }

        .session-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .session-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .session-coords {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
        }

        .session-actions {
            display: flex;
            gap: 6px;
            margin-top: 8px;
        }

        /* Map container */
        .map-container {
            flex: 1;
            position: relative;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Map overlay stats */
        .map-stats {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 999;
            display: flex;
            gap: 8px;
        }

        .stat-chip {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
        }
        .stat-chip .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
        }
        .stat-chip .dot.green { background: var(--success); }
        .stat-chip .dot.yellow { background: var(--warning); }
        .stat-chip .dot.red { background: var(--danger); }
        .stat-chip .dot.blue { background: var(--accent); animation: pulse 2s ease-in-out infinite; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* Polling indicator */
        .poll-indicator {
            position: absolute;
            bottom: 24px;
            right: 16px;
            z-index: 999;
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 0.7rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Toast notifications */
        .toast-container {
            position: absolute;
            top: 60px;
            right: 16px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .toast {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.8rem;
            animation: slideIn 0.3s ease, fadeOut 0.3s ease 3.7s forwards;
            max-width: 300px;
        }
        .toast.success { border-left: 3px solid var(--success); }
        .toast.error { border-left: 3px solid var(--danger); }
        .toast.info { border-left: 3px solid var(--accent); }

        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }

        /* Mobile sidebar toggle */
        .sidebar-toggle {
            display: none;
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 1001;
            width: 40px; height: 40px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { position: absolute; height: 100%; transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-toggle { display: flex; }
        }

        /* Scrollbar */
        .session-list::-webkit-scrollbar { width: 4px; }
        .session-list::-webkit-scrollbar-track { background: transparent; }
        .session-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* Leaflet custom marker styling */
        .custom-marker {
            position: relative;
        }
        .marker-dot {
            width: 16px; height: 16px;
            background: var(--accent);
            border: 3px solid #fff;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .marker-dot.stale { background: var(--warning); }
        .marker-dot.expired { background: var(--danger); }

        .leaflet-popup-content-wrapper {
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border-radius: 12px !important;
            border: 1px solid var(--border) !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4) !important;
        }
        .leaflet-popup-tip { background: var(--bg-card) !important; }
        .leaflet-popup-content { font-family: 'Inter', sans-serif !important; font-size: 0.8rem !important; }
        .popup-title { font-weight: 700; font-size: 0.9rem; margin-bottom: 6px; }
        .popup-row { color: var(--text-secondary); margin: 3px 0; }
        .popup-row span { color: var(--text-primary); font-weight: 500; }

        /* Tracking URL modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.visible { display: flex; }
        .modal {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            max-width: 480px;
            width: calc(100% - 48px);
        }
        .modal h2 { font-size: 1.1rem; margin-bottom: 16px; }
        .modal .url-box {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--accent);
            word-break: break-all;
            margin-bottom: 16px;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .modal .url-box:hover { border-color: var(--accent); }
        .modal .modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Mobile toggle -->
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-row">
                    <div class="logo">
                        <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        <span>LacakLokasi</span>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        <form method="POST" action="{{ route('logout') }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn-logout">Logout</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- New Session Form -->
            <div class="new-session">
                <h3>Sesi Baru</h3>
                <div class="form-row">
                    <input class="input" type="tel" id="phoneInput" placeholder="+62812345678">
                    <input class="input small" type="text" id="labelInput" placeholder="Label">
                </div>
                <button class="btn btn-primary" style="width:100%" id="createBtn" onclick="createSession()">
                    Kirim Tautan Pelacakan
                </button>
            </div>

            <!-- Session List -->
            <div class="session-list-header">
                <h3>Sesi Aktif</h3>
                <span class="session-count" id="sessionCount">0</span>
            </div>
            <div class="session-list" id="sessionList">
                <!-- Populated by JS -->
            </div>
        </aside>

        <!-- Map -->
        <main class="map-container">
            <div id="map"></div>

            <div class="map-stats">
                <div class="stat-chip">
                    <div class="dot blue"></div>
                    <span>Polling aktif</span>
                </div>
                <div class="stat-chip">
                    <div class="dot green" id="activeCountDot"></div>
                    <span id="activeCountText">0 aktif</span>
                </div>
            </div>

            <div class="poll-indicator" id="pollIndicator">
                <div class="dot blue" style="width:6px;height:6px;border-radius:50%;background:var(--accent)"></div>
                Update terakhir: <span id="lastPollTime">—</span>
            </div>

            <div class="toast-container" id="toastContainer"></div>
        </main>
    </div>

    <!-- URL Modal -->
    <div class="modal-overlay" id="urlModal">
        <div class="modal">
            <h2>🔗 Tautan Pelacakan</h2>
            <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:16px;">
                Salin tautan ini dan kirimkan ke target. SMS juga telah dikirim secara otomatis.
            </p>
            <div class="url-box" id="trackingUrl" onclick="copyUrl()"></div>
            <div class="modal-actions">
                <button class="btn btn-primary btn-sm" onclick="copyUrl()">Salin URL</button>
                <button class="btn btn-danger btn-sm" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <script>
        // ============================================================
        // Configuration
        // ============================================================
        const POLL_INTERVAL = 3000;
        const API_BASE = '{{ url("/api") }}';
        let apiToken = null;

        // ============================================================
        // State
        // ============================================================
        let map = null;
        let markers = {};          // sessionId -> L.marker
        let accuracyCircles = {};  // sessionId -> L.circle
        let trails = {};           // sessionId -> L.polyline
        let sessions = [];
        let selectedSessionId = null;

        // ============================================================
        // Initialize logic is now at the bottom with startRealtime()
        // ============================================================
        // ============================================================
        // Map Initialization
        // ============================================================
        function initMap() {
            map = L.map('map', {
                center: [-2.5, 118],  // Center of Indonesia
                zoom: 5,
                zoomControl: false,
            });

            // Dark tile layer — OpenStreetMap via jawg.io free dark tiles
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(map);

            L.control.zoom({ position: 'bottomleft' }).addTo(map);
        }

        // ============================================================
        // API Token
        // ============================================================
        async function fetchApiToken() {
            try {
                const res = await fetch('{{ route("api.token") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await res.json();
                apiToken = data.token;
            } catch (err) {
                console.error('Failed to get API token:', err);
                showToast('Gagal mendapatkan token API', 'error');
            }
        }

        async function apiRequest(endpoint, options = {}) {
            const defaults = {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${apiToken}`,
                },
            };
            const config = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };
            return fetch(`${API_BASE}${endpoint}`, config);
        }

        // ============================================================
        // Session Management
        // ============================================================
        async function createSession() {
            const phone = document.getElementById('phoneInput').value.trim();
            const label = document.getElementById('labelInput').value.trim();

            if (!phone) {
                showToast('Masukkan nomor telepon', 'error');
                return;
            }

            const btn = document.getElementById('createBtn');
            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            try {
                const res = await apiRequest('/sessions', {
                    method: 'POST',
                    body: JSON.stringify({ phone_number: phone, label: label || null }),
                });

                const data = await res.json();

                if (!res.ok) {
                    const errorMsg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Gagal membuat sesi');
                    throw new Error(errorMsg);
                }

                showToast(`Sesi dibuat untuk ${phone}`, 'success');
                document.getElementById('phoneInput').value = '';
                document.getElementById('labelInput').value = '';

                // Show tracking URL
                document.getElementById('trackingUrl').textContent = data.data.tracking_url;
                document.getElementById('urlModal').classList.add('visible');

                await loadSessions();

            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Kirim Tautan Pelacakan';
            }
        }

        async function loadSessions() {
            try {
                const res = await apiRequest('/sessions');
                const data = await res.json();
                sessions = data.data || [];
                renderSessionList();
                updateMapMarkers();
            } catch (err) {
                console.error('Load sessions failed:', err);
            }
        }

        async function revokeSession(id, event) {
            event.stopPropagation();
            if (!confirm('Hentikan sesi pelacakan ini?')) { return; }

            try {
                await apiRequest(`/sessions/${id}`, { method: 'DELETE' });
                showToast('Sesi dihentikan', 'info');
                await loadSessions();
            } catch (err) {
                showToast('Gagal menghentikan sesi', 'error');
            }
        }

        // ============================================================
        // Render Session List
        // ============================================================
        function renderSessionList() {
            const container = document.getElementById('sessionList');
            const activeSessions = sessions.filter(s => !s.is_expired && s.status !== 'revoked');
            document.getElementById('sessionCount').textContent = activeSessions.length;
            document.getElementById('activeCountText').textContent = `${activeSessions.filter(s => s.status === 'active').length} aktif`;

            container.innerHTML = sessions.map(s => {
                const loc = s.latest_location;
                const isSelected = s.id === selectedSessionId;

                return `
                    <div class="session-card ${isSelected ? 'active' : ''}" onclick="focusSession(${s.id})">
                        <div class="session-top">
                            <span class="session-phone">${s.phone_number}</span>
                            <span class="status-badge ${s.is_expired ? 'expired' : s.status}">${s.is_expired ? 'expired' : s.status}</span>
                        </div>
                        ${s.label ? `<div class="session-label">${escapeHtml(s.label)}</div>` : ''}
                        <div class="session-meta">
                            <span class="session-coords">${loc ? `${loc.latitude.toFixed(6)}, ${loc.longitude.toFixed(6)}` : 'Belum ada lokasi'}</span>
                            <span>${timeAgo(s.created_at)}</span>
                        </div>
                        ${!s.is_expired && s.status !== 'revoked' ? `
                        <div class="session-actions">
                            <button class="btn btn-danger btn-sm" onclick="revokeSession(${s.id}, event)">Hentikan</button>
                        </div>` : ''}
                    </div>
                `;
            }).join('');
        }

        // ============================================================
        // Map Markers
        // ============================================================
        function updateMapMarkers() {
            sessions.forEach(session => {
                const loc = session.latest_location;
                if (!loc) { return; }

                const { latitude: lat, longitude: lng, accuracy } = loc;
                const isStale = session.is_expired || session.status === 'expired';
                const isActive = session.status === 'active' && !session.is_expired;

                // Create or update marker
                if (markers[session.id]) {
                    markers[session.id].setLatLng([lat, lng]);
                } else {
                    const markerColor = isStale ? '#ef4444' : (isActive ? '#10b981' : '#f59e0b');

                    const icon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div class="marker-dot" style="background:${markerColor}"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8],
                    });

                    markers[session.id] = L.marker([lat, lng], { icon })
                        .addTo(map)
                        .bindPopup(createPopupContent(session, loc));
                }

                // Update popup
                markers[session.id].setPopupContent(createPopupContent(session, loc));

                // Accuracy circle
                if (accuracy && accuracy < 5000) {
                    if (accuracyCircles[session.id]) {
                        accuracyCircles[session.id].setLatLng([lat, lng]).setRadius(accuracy);
                    } else {
                        accuracyCircles[session.id] = L.circle([lat, lng], {
                            radius: accuracy,
                            color: isActive ? '#3b82f6' : '#ef4444',
                            fillColor: isActive ? '#3b82f6' : '#ef4444',
                            fillOpacity: 0.08,
                            weight: 1,
                            opacity: 0.3,
                        }).addTo(map);
                    }
                }
            });
        }

        function createPopupContent(session, loc) {
            return `
                <div class="popup-title">${escapeHtml(session.label || session.phone_number)}</div>
                <div class="popup-row">📱 <span>${session.phone_number}</span></div>
                <div class="popup-row">📍 <span>${loc.latitude.toFixed(8)}, ${loc.longitude.toFixed(8)}</span></div>
                ${loc.accuracy ? `<div class="popup-row">🎯 Akurasi: <span>${loc.accuracy.toFixed(1)}m</span></div>` : ''}
                ${loc.recorded_at ? `<div class="popup-row">🕐 <span>${new Date(loc.recorded_at).toLocaleString('id-ID')}</span></div>` : ''}
                <div class="popup-row">Status: <span class="status-badge ${session.status}" style="font-size:0.65rem">${session.status}</span></div>
            `;
        }

        function focusSession(id) {
            selectedSessionId = id;
            renderSessionList();

            const session = sessions.find(s => s.id === id);
            if (session?.latest_location) {
                const { latitude, longitude } = session.latest_location;
                map.flyTo([latitude, longitude], 16, { duration: 1.5 });

                if (markers[id]) {
                    markers[id].openPopup();
                }
            }

            // Load trail
            loadTrail(id);
        }

        async function loadTrail(sessionId) {
            try {
                const res = await apiRequest(`/locations/${sessionId}/history`);
                const data = await res.json();
                const locs = data.data || [];

                // Remove existing trail
                if (trails[sessionId]) {
                    map.removeLayer(trails[sessionId]);
                }

                if (locs.length > 1) {
                    const latlngs = locs.map(l => [l.latitude, l.longitude]);
                    trails[sessionId] = L.polyline(latlngs, {
                        color: '#3b82f6',
                        weight: 3,
                        opacity: 0.6,
                        dashArray: '8, 8',
                    }).addTo(map);
                }
            } catch (err) {
                console.error('Load trail failed:', err);
            }
        }

        // ============================================================
        // WebSockets (Laravel Echo)
        // ============================================================
        function startRealtime() {
            // Listen to private 'tracking' channel
            // LocationUpdated event will push the new LocationLog and session_id
            if (window.Echo) {
                window.Echo.private('tracking')
                    .listen('LocationUpdated', (e) => {
                        handleRealtimeUpdate(e);
                    });
                
                document.getElementById('lastPollTime').textContent = 'Live (Reverb)';
                document.getElementById('pollIndicator').querySelector('.dot').style.animation = 'pulse 2s ease-in-out infinite';
            } else {
                console.warn('Laravel Echo is not available. Ensure Vite is running and compiled.');
                document.getElementById('lastPollTime').textContent = 'Echo error';
            }
        }

        function handleRealtimeUpdate(update) {
            // The event payload matches the LocationUpdated broadcastWith array
            const newLoc = update.location;
            const sessionId = update.session_id;
            const status = update.status;
            
            const existing = sessions.find(s => s.session_id === sessionId || s.id === sessionId);
            if (existing) {
                existing.latest_location = {
                    latitude: newLoc.latitude,
                    longitude: newLoc.longitude,
                    accuracy: newLoc.accuracy,
                    recorded_at: newLoc.recorded_at,
                };
                existing.status = status;
                
                renderSessionList();
                updateMapMarkers();

                if (selectedSessionId === sessionId) {
                    loadTrail(selectedSessionId);
                }
            }
        }

        // Initialize: replace startPolling with startRealtime
        document.addEventListener('DOMContentLoaded', async () => {
            initMap();
            await fetchApiToken();
            await loadSessions();
            
            // Wait slightly for Vite module to load window.Echo
            setTimeout(startRealtime, 1000);
        });

        // ============================================================
        // UI Helpers
        // ============================================================
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        function timeAgo(dateStr) {
            const diff = Date.now() - new Date(dateStr).getTime();
            const mins = Math.floor(diff / 60000);
            if (mins < 1) { return 'baru saja'; }
            if (mins < 60) { return `${mins}m lalu`; }
            const hours = Math.floor(mins / 60);
            if (hours < 24) { return `${hours}j lalu`; }
            return `${Math.floor(hours / 24)}h lalu`;
        }

        function escapeHtml(str) {
            if (!str) { return ''; }
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function copyUrl() {
            const url = document.getElementById('trackingUrl').textContent;
            navigator.clipboard.writeText(url).then(() => {
                showToast('URL disalin ke clipboard', 'success');
            }).catch(() => {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = url;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast('URL disalin', 'success');
            });
        }

        function closeModal() {
            document.getElementById('urlModal').classList.remove('visible');
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
    </script>
</body>
</html>
