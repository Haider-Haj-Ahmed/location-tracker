<!-- resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0f1117; color: #e2e8f0; height: 100vh; display: flex; flex-direction: column; }
        header { padding: 14px 24px; background: #1a1d27; border-bottom: 1px solid #2d3148; display: flex; align-items: center; gap: 12px; }
        header h1 { font-size: 1.1rem; font-weight: 600; letter-spacing: .5px; }
        .badge { background: #22c55e22; color: #22c55e; border: 1px solid #22c55e55; padding: 2px 10px; border-radius: 20px; font-size: .75rem; }
        #map { flex: 1; }
        #sidebar { position: absolute; top: 60px; right: 16px; z-index: 1000; width: 240px; display: flex; flex-direction: column; gap: 8px; }
        .device-card { background: #1a1d27ee; border: 1px solid #2d3148; border-radius: 10px; padding: 12px 14px; cursor: pointer; transition: border-color .2s; }
        .device-card:hover { border-color: #4f6ef7; }
        .device-card .dname { font-weight: 600; font-size: .9rem; margin-bottom: 4px; }
        .device-card .dmeta { font-size: .75rem; color: #94a3b8; }
        .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }
        .dot.online { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
        .dot.offline { background: #64748b; }
    </style>
</head>
<body>
<header>
    <span style="font-size:1.4rem">📡</span>
    <h1>Live Tracker Dashboard</h1>
    <span class="badge" id="online-count">0 online</span>
</header>

<div id="map"></div>
<div id="sidebar"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map', { zoomControl: false }).setView([20, 0], 2);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CARTO', maxZoom: 19
    }).addTo(map);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    const markers = {};
    const circles = {};

    function makeIcon(online) {
        return L.divIcon({
            className: '',
            html: `<div style="width:14px;height:14px;border-radius:50%;background:${online ? '#22c55e' : '#64748b'};border:2px solid #fff;box-shadow:0 0 ${online ? '8px #22c55e' : 'none'}"></div>`,
            iconSize: [14, 14], iconAnchor: [7, 7]
        });
    }

    async function refresh() {
        const res = await fetch('/api/devices/latest');
        const devices = await res.json();
        const sidebar = document.getElementById('sidebar');
        const onlineCount = devices.filter(d => d.online && d.location).length;
        document.getElementById('online-count').textContent = `${onlineCount} online`;

        sidebar.innerHTML = '';

        devices.forEach(device => {
            // Sidebar card
            const card = document.createElement('div');
            card.className = 'device-card';
            card.innerHTML = `
                <div class="dname">
                    <span class="dot ${device.online ? 'online' : 'offline'}"></span>
                    ${device.name}
                </div>
                <div class="dmeta">${device.last_seen ?? 'Never seen'}</div>
                ${device.location ? `<div class="dmeta">${device.location.lat.toFixed(5)}, ${device.location.lng.toFixed(5)}</div>` : '<div class="dmeta">No location yet</div>'}
            `;

            if (device.location) {
                card.onclick = () => map.flyTo([device.location.lat, device.location.lng], 15);
            }
            sidebar.appendChild(card);

            // Map marker
            if (!device.location) return;
            const latlng = [device.location.lat, device.location.lng];

            if (markers[device.id]) {
                markers[device.id].setLatLng(latlng).setIcon(makeIcon(device.online));
                if (device.location.accuracy && circles[device.id]) {
                    circles[device.id].setLatLng(latlng).setRadius(device.location.accuracy);
                }
            } else {
                markers[device.id] = L.marker(latlng, { icon: makeIcon(device.online) })
                    .addTo(map)
                    .bindPopup(`<b>${device.name}</b><br>${device.last_seen}`);
                if (device.location.accuracy) {
                    circles[device.id] = L.circle(latlng, {
                        radius: device.location.accuracy,
                        color: '#4f6ef7', fillOpacity: 0.08, weight: 1
                    }).addTo(map);
                }
            }
        });
    }

    refresh();
    setInterval(refresh, 5000); // poll every 5 seconds
</script>
</body>
</html>
