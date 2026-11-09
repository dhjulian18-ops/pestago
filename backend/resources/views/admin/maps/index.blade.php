@extends('layouts.app')
@section('title', 'Peta Sebaran Vendor & Layanan — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'maps'])

    <div class="dashboard-content" style="padding-bottom:12px;">
        {{-- Header --}}
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
            <div>
                <h1 class="dashboard-title" style="font-size:20px;font-weight:800;color:#111111;margin:0 0 4px;letter-spacing:-0.02em;">
                    <i class="bi bi-geo-alt" style="color:#d81a24;"></i> Peta Sebaran Vendor &amp; Layanan
                </h1>
                <p class="dashboard-subtitle" style="font-size:13px;color:#666666;margin:0;">
                    Visualisasi titik lokasi seluruh vendor mitra dan katalog layanan PestaGo secara real-time.
                </p>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" onclick="resetMapBounds()" class="uq-map-btn-dark">
                    <i class="bi bi-arrows-fullscreen"></i> Tampilkan Semua Titik
                </button>
            </div>
        </div>

        {{-- Uniqlo Clean Stats Bar (Compact, Sharp, Zero-Radius) --}}
        <div class="uq-map-stats-strip">
            <div class="uq-map-stat-col">
                <span class="uq-map-stat-label">TOTAL LOKASI TERPETAKAN</span>
                <span class="uq-map-stat-val" id="statTotalPoints">0</span>
            </div>
            <div class="uq-map-stat-col">
                <span class="uq-map-stat-label">TITIK VENDOR MITRA</span>
                <span class="uq-map-stat-val" style="color:#d81a24;" id="statVendorPoints">0</span>
            </div>
            <div class="uq-map-stat-col">
                <span class="uq-map-stat-label">TITIK LAYANAN / VENUE</span>
                <span class="uq-map-stat-val" style="color:#2563eb;" id="statServicePoints">0</span>
            </div>
            <div class="uq-map-stat-col">
                <span class="uq-map-stat-label">CAKUPAN KOTA / WILAYAH</span>
                <span class="uq-map-stat-val">{{ $cities->count() }}</span>
            </div>
        </div>

        {{-- Main Map Card Container (Uniqlo Clean Style) --}}
        <div class="uq-map-main-card">
            {{-- Filter & Search Toolbar --}}
            <div class="uq-map-toolbar">
                {{-- Layer Type Filters --}}
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    <span style="font-size:11px;font-weight:800;letter-spacing:0.06em;color:#888888;margin-right:2px;">FILTER:</span>
                    <button type="button" class="type-filter-btn active" data-type="all" onclick="setTypeFilter('all')">
                        Semua (<span id="countAll">0</span>)
                    </button>
                    <button type="button" class="type-filter-btn" data-type="vendor" onclick="setTypeFilter('vendor')">
                        Vendor (<span id="countVendors">0</span>)
                    </button>
                    <button type="button" class="type-filter-btn" data-type="service" onclick="setTypeFilter('service')">
                        Layanan (<span id="countServices">0</span>)
                    </button>
                </div>

                {{-- Dropdowns & Live Search --}}
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    {{-- Filter Kategori --}}
                    <select id="categoryFilter" class="uq-map-select" onchange="applyFilters()">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Kota --}}
                    <select id="cityFilter" class="uq-map-select" onchange="applyFilters()">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>

                    {{-- Search Input --}}
                    <div style="position:relative;min-width:210px;">
                        <input type="text" id="mapSearchInput" class="uq-map-input" placeholder="Cari vendor / layanan..." oninput="applyFilters()">
                        <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#888888;font-size:12px;"></i>
                    </div>

                    <button type="button" onclick="resetFilters()" class="uq-map-btn-ghost" title="Reset Semua Filter">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>

            {{-- Split Layout: Sidebar List + Leaflet Map (Enlarged Height & Independent Scroll) --}}
            <div id="mapSplitWrapper">
                {{-- Left Item List Drawer --}}
                <div class="uq-map-list-sidebar">
                    <div class="uq-map-list-header">
                        <div>
                            <div style="font-size:12px;font-weight:800;color:#111111;letter-spacing:0.04em;text-transform:uppercase;">
                                Lokasi Terdaftar (<span id="visibleItemsCount">0</span>)
                            </div>
                            <div style="font-size:11px;color:#888888;margin-top:2px;">
                                Klik kartu untuk pusatkan peta
                            </div>
                        </div>
                        <span class="uq-map-badge-clean">
                            AKTIF
                        </span>
                    </div>

                    {{-- Scrollable List Container with guaranteed scroll fix --}}
                    <div id="locationsListContainer">
                        {{-- Items dynamically rendered here --}}
                    </div>
                </div>

                {{-- Right Leaflet Map (Enlarged) --}}
                <div class="uq-map-canvas-container">
                    <div id="adminMasterMap"></div>

                    {{-- Map Legend Overlay --}}
                    <div class="uq-map-legend">
                        <div style="font-weight:800;color:#111111;margin-bottom:2px;font-size:10px;letter-spacing:0.06em;text-transform:uppercase;">LEGENDA PETA</div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="width:12px;height:12px;background:#d81a24;display:inline-block;border:1px solid #ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                            <span style="font-weight:600;color:#333333;">Titik Vendor Mitra</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="width:12px;height:12px;background:#2563eb;display:inline-block;border:1px solid #ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                            <span style="font-weight:600;color:#333333;">Titik Layanan / Venue</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Uniqlo Clean Styles for Admin Map */
.uq-map-stats-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background: #ffffff;
    border: 1px solid #e5e5e5;
    margin-bottom: 14px;
    border-radius: 0 !important;
}
.uq-map-stat-col {
    padding: 12px 18px;
    border-right: 1px solid #e5e5e5;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.uq-map-stat-col:last-child {
    border-right: none;
}
.uq-map-stat-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: #888888;
    text-transform: uppercase;
}
.uq-map-stat-val {
    font-size: 20px;
    font-weight: 800;
    color: #111111;
    line-height: 1.1;
}

.uq-map-main-card {
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    background: #ffffff;
    box-shadow: none !important;
    overflow: hidden;
}

.uq-map-toolbar {
    background: #fafafa;
    padding: 12px 18px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.type-filter-btn {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    color: #111111;
    font-size: 12px;
    font-weight: 700;
    padding: 0 14px;
    height: 36px;
    border-radius: 0 !important;
    transition: all 0.15s ease;
    cursor: pointer;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.type-filter-btn:hover {
    background: #f5f5f5;
    border-color: #111111;
}
.type-filter-btn.active {
    background: #111111;
    color: #ffffff;
    border-color: #111111;
}

.uq-map-select {
    height: 36px;
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    font-size: 12.5px;
    padding: 0 12px;
    background: #ffffff;
    color: #111111;
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s ease;
}
.uq-map-select:focus {
    border-color: #111111;
}

.uq-map-input {
    height: 36px;
    width: 100%;
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    font-size: 12.5px;
    padding: 0 12px 0 32px;
    background: #ffffff;
    color: #111111;
    font-family: inherit;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.15s ease;
}
.uq-map-input:focus {
    border-color: #111111;
}

.uq-map-btn-ghost {
    height: 36px;
    padding: 0 14px;
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    background: #ffffff;
    color: #111111;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}
.uq-map-btn-ghost:hover {
    background: #f5f5f5;
    border-color: #111111;
}

.uq-map-btn-dark {
    height: 36px;
    padding: 0 14px;
    border: 1px solid #111111;
    border-radius: 0 !important;
    background: #111111;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.uq-map-btn-dark:hover {
    background: #333333;
    border-color: #333333;
}

/* Map Split Layout: Much larger height and independent scrolling */
#mapSplitWrapper {
    display: grid;
    grid-template-columns: 380px 1fr;
    height: calc(100vh - 195px);
    min-height: 720px;
    min-width: 0;
    position: relative;
    background: #ffffff;
}

.uq-map-list-sidebar {
    border-right: 1px solid #e5e5e5;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    min-height: 0;
    height: 100%;
    max-height: 100%;
    overflow: hidden;
    box-sizing: border-box;
}

.uq-map-list-header {
    flex-shrink: 0;
    padding: 12px 16px;
    background: #fafafa;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.uq-map-badge-clean {
    background: #ffffff;
    color: #111111;
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    font-size: 10px;
    font-weight: 800;
    padding: 3px 8px;
    letter-spacing: 0.05em;
}

/* Scrollable Container with Guaranteed Scrollbar */
#locationsListContainer {
    flex: 1 1 0%;
    min-height: 0;
    height: 100%;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-sizing: border-box;
}

#locationsListContainer::-webkit-scrollbar {
    width: 6px;
}
#locationsListContainer::-webkit-scrollbar-track {
    background: #fafafa;
}
#locationsListContainer::-webkit-scrollbar-thumb {
    background: #cccccc;
}
#locationsListContainer::-webkit-scrollbar-thumb:hover {
    background: #999999;
}

.loc-item-card {
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    padding: 12px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
    border-left: 3px solid transparent;
}
.loc-item-card:hover {
    border-color: #111111;
    background: #fafafa;
}
.loc-item-card.active-item {
    border-color: #d81a24;
    border-left: 3px solid #d81a24;
    background: #fff8f8;
}

.uq-map-canvas-container {
    position: relative;
    height: 100%;
    width: 100%;
    min-height: 0;
    overflow: hidden;
}

#adminMasterMap {
    height: 100%;
    width: 100%;
    min-height: 100%;
    z-index: 1;
}

.uq-map-legend {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #e5e5e5;
    padding: 10px 14px;
    border-radius: 0 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    z-index: 999;
    font-size: 11.5px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* Custom Marker Styling */
.custom-map-pin {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 !important;
    color: #ffffff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    border: 2px solid #ffffff;
    transition: transform 0.2s ease;
}
.custom-map-pin:hover {
    transform: scale(1.15);
}
.custom-map-pin.vendor-pin {
    background: #d81a24;
}
.custom-map-pin.service-pin {
    background: #2563eb;
}

@media (max-width: 992px) {
    .uq-map-stats-strip {
        grid-template-columns: 1fr 1fr;
    }
    .uq-map-stat-col:nth-child(2) {
        border-right: none;
    }
    #mapSplitWrapper {
        grid-template-columns: 1fr !important;
        height: auto !important;
    }
    #adminMasterMap {
        height: 520px !important;
    }
    .uq-map-list-sidebar {
        height: 340px !important;
        max-height: 340px !important;
        border-right: none;
        border-bottom: 1px solid #e5e5e5;
    }
}
</style>

<script>
const vendorsData = @json($vendors);
const servicesData = @json($services);

let map;
let markersLayer;
let currentTypeFilter = 'all';
let allMarkers = [];

function initAdminMap() {
    map = L.map('adminMasterMap', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView([-6.2287, 106.8360], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    // Update Initial Stats
    document.getElementById('statVendorPoints').innerText = vendorsData.length;
    document.getElementById('statServicePoints').innerText = servicesData.length;
    document.getElementById('statTotalPoints').innerText = vendorsData.length + servicesData.length;
    document.getElementById('countVendors').innerText = vendorsData.length;
    document.getElementById('countServices').innerText = servicesData.length;
    document.getElementById('countAll').innerText = vendorsData.length + servicesData.length;

    applyFilters();

    setTimeout(() => {
        if (map) {
            map.invalidateSize();
        }
    }, 300);
}

function setTypeFilter(type) {
    currentTypeFilter = type;
    document.querySelectorAll('.type-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-type') === type) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyFilters();
}

function resetFilters() {
    currentTypeFilter = 'all';
    document.querySelectorAll('.type-filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-type') === 'all');
    });
    document.getElementById('categoryFilter').value = '';
    document.getElementById('cityFilter').value = '';
    document.getElementById('mapSearchInput').value = '';
    applyFilters();
    resetMapBounds();
}

function applyFilters() {
    markersLayer.clearLayers();
    allMarkers = [];

    const catVal = document.getElementById('categoryFilter').value;
    const cityVal = document.getElementById('cityFilter').value.toLowerCase();
    const searchVal = document.getElementById('mapSearchInput').value.toLowerCase().trim();

    const listContainer = document.getElementById('locationsListContainer');
    listContainer.innerHTML = '';

    let matchedItems = [];

    // Filter Vendors
    if (currentTypeFilter === 'all' || currentTypeFilter === 'vendor') {
        vendorsData.forEach(v => {
            const matchCat = !catVal || v.category_id == catVal;
            const matchCity = !cityVal || (v.city && v.city.toLowerCase() === cityVal);
            const matchSearch = !searchVal || 
                v.name.toLowerCase().includes(searchVal) || 
                (v.address && v.address.toLowerCase().includes(searchVal)) ||
                (v.city && v.city.toLowerCase().includes(searchVal));

            if (matchCat && matchCity && matchSearch) {
                matchedItems.push(v);
                createMarker(v);
            }
        });
    }

    // Filter Services
    if (currentTypeFilter === 'all' || currentTypeFilter === 'service') {
        servicesData.forEach(s => {
            const matchCat = !catVal || s.category_id == catVal;
            const matchCity = !cityVal || (s.city && s.city.toLowerCase() === cityVal);
            const matchSearch = !searchVal || 
                s.name.toLowerCase().includes(searchVal) || 
                s.vendor_name.toLowerCase().includes(searchVal) ||
                (s.address && s.address.toLowerCase().includes(searchVal)) ||
                (s.city && s.city.toLowerCase().includes(searchVal));

            if (matchCat && matchCity && matchSearch) {
                matchedItems.push(s);
                createMarker(s);
            }
        });
    }

    document.getElementById('visibleItemsCount').innerText = matchedItems.length;

    // Render List Items
    if (matchedItems.length === 0) {
        listContainer.innerHTML = `
            <div style="text-align:center;padding:48px 16px;color:#888888;">
                <i class="bi bi-geo-alt" style="font-size:28px;color:#cccccc;"></i>
                <div style="font-size:13px;font-weight:700;color:#111111;margin-top:8px;">Tidak Ada Titik Ditemukan</div>
                <div style="font-size:12px;color:#888888;margin-top:2px;">Silakan sesuaikan filter atau kata kunci pencarian.</div>
            </div>
        `;
    } else {
        matchedItems.forEach(item => {
            const card = document.createElement('div');
            card.className = 'loc-item-card';
            card.id = `card-${item.type}-${item.id}`;

            if (item.type === 'vendor') {
                const avatarImg = item.logo || item.avatar;
                const avatarHtml = avatarImg
                    ? `<img src="${avatarImg}" alt="${item.name}" style="width:36px;height:36px;border-radius:0;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">`
                    : `<div style="width:36px;height:36px;background:#111111;color:#ffffff;border-radius:0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0;">${item.name.charAt(0).toUpperCase()}</div>`;

                card.innerHTML = `
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        ${avatarHtml}
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:6px;">
                                <div style="font-weight:800;font-size:13px;color:#111111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.name}</div>
                                <span style="background:#fee2e2;color:#d81a24;font-size:9.5px;font-weight:800;padding:2px 6px;border-radius:0;flex-shrink:0;letter-spacing:0.04em;">VENDOR</span>
                            </div>
                            <div style="font-size:11.5px;color:#666666;margin-top:3px;display:flex;align-items:center;gap:6px;">
                                <span><i class="bi bi-tag"></i> ${item.category}</span>
                                <span>&bull;</span>
                                <span><i class="bi bi-geo-alt"></i> ${item.city}</span>
                            </div>
                            <div style="font-size:11px;color:#888888;margin-top:4px;display:flex;align-items:center;justify-content:space-between;">
                                <span>${item.services_count} Layanan Aktif</span>
                                <span style="color:#d97706;font-weight:700;"><i class="bi bi-star-fill"></i> ${item.rating}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                card.innerHTML = `
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                        <div style="font-weight:800;font-size:13px;color:#111111;line-height:1.3;">${item.name}</div>
                        <span style="background:#e0f2fe;color:#0369a1;font-size:9.5px;font-weight:800;padding:2px 6px;border-radius:0;flex-shrink:0;letter-spacing:0.04em;">LAYANAN</span>
                    </div>
                    <div style="font-size:11.5px;color:#666666;margin-top:3px;display:flex;align-items:center;gap:6px;">
                        <span><i class="bi bi-shop"></i> ${item.vendor_name}</span>
                        <span>&bull;</span>
                        <span><i class="bi bi-geo-alt"></i> ${item.city}</span>
                    </div>
                    <div style="font-size:12.5px;font-weight:800;color:#d81a24;margin-top:5px;display:flex;align-items:baseline;justify-content:space-between;">
                        <span>Rp ${item.price} <small style="font-size:10px;font-weight:500;color:#888888;">/${item.price_unit}</small></span>
                        <span style="font-size:10.5px;font-weight:600;color:#2563eb;">Lihat Peta &rarr;</span>
                    </div>
                `;
            }

            card.addEventListener('click', () => {
                focusOnPoint(item.latitude, item.longitude, item.type, item.id);
            });

            listContainer.appendChild(card);
        });
    }

    if (allMarkers.length > 0) {
        const group = new L.featureGroup(allMarkers.map(m => m.marker));
        map.fitBounds(group.getBounds().pad(0.12));
    }
}

function createMarker(item) {
    const isVendor = item.type === 'vendor';
    let iconHtml;
    if (isVendor) {
        const vImg = item.logo || item.avatar;
        iconHtml = vImg
            ? `<div class="custom-map-pin vendor-pin" style="width:34px;height:34px;padding:0;overflow:hidden;border:2px solid #ffffff;background:#ffffff;"><img src="${vImg}" style="width:100%;height:100%;object-fit:cover;"></div>`
            : `<div class="custom-map-pin vendor-pin" style="width:32px;height:32px;font-size:14px;"><i class="bi bi-shop"></i></div>`;
    } else {
        iconHtml = `<div class="custom-map-pin service-pin" style="width:28px;height:28px;font-size:13px;"><i class="bi bi-box-seam"></i></div>`;
    }

    const customIcon = L.divIcon({
        className: 'custom-leaflet-div-icon',
        html: iconHtml,
        iconSize: isVendor ? [34, 34] : [28, 28],
        iconAnchor: isVendor ? [17, 17] : [14, 14],
        popupAnchor: [0, -16]
    });

    const marker = L.marker([item.latitude, item.longitude], { icon: customIcon });

    let popupContent = '';
    if (isVendor) {
        const vImg = item.logo || item.avatar;
        const vAvatarHeader = vImg
            ? `<img src="${vImg}" alt="${item.name}" style="width:40px;height:40px;border-radius:0;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">`
            : `<div style="width:40px;height:40px;background:#111111;color:#ffffff;border-radius:0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;">${item.name.charAt(0).toUpperCase()}</div>`;

        popupContent = `
            <div style="font-family:inherit;min-width:240px;padding:4px 2px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:8px;">
                    <span style="background:#fee2e2;color:#d81a24;font-size:10px;font-weight:800;padding:2px 6px;letter-spacing:0.04em;">VENDOR MITRA</span>
                    <span style="background:#f0fdf4;color:#16a34a;font-size:10px;font-weight:800;padding:2px 6px;">${item.status.toUpperCase()}</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    ${vAvatarHeader}
                    <div style="overflow:hidden;">
                        <h4 style="font-size:14px;font-weight:800;margin:0 0 2px;color:#111111;line-height:1.2;">${item.name}</h4>
                        <div style="font-size:11.5px;color:#666666;"><i class="bi bi-tag"></i> ${item.category}</div>
                    </div>
                </div>
                <div style="font-size:12px;color:#444444;margin-bottom:6px;">
                    <i class="bi bi-geo-alt"></i> ${item.address ? item.address + ', ' : ''}${item.city}
                </div>
                ${item.phone ? `<div style="font-size:11.5px;color:#444444;margin-bottom:8px;"><i class="bi bi-telephone"></i> ${item.phone}</div>` : ''}
                <div style="border-top:1px solid #e5e5e5;padding-top:8px;margin-top:8px;display:flex;gap:6px;">
                    <a href="${item.url}" class="uq-map-btn-dark" style="font-size:11px;height:30px;padding:0 10px;flex:1;text-align:center;justify-content:center;">
                        Kelola Vendor
                    </a>
                    <a href="${item.public_url}" target="_blank" class="uq-map-btn-ghost" style="font-size:11px;height:30px;padding:0 8px;" title="Buka Profil Publik">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        `;
    } else {
        popupContent = `
            <div style="font-family:inherit;min-width:230px;padding:4px 2px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:6px;">
                    <span style="background:#e0f2fe;color:#0369a1;font-size:10px;font-weight:800;padding:2px 6px;letter-spacing:0.04em;">LAYANAN ACARA</span>
                    <span style="font-size:11px;color:#d97706;font-weight:800;"><i class="bi bi-star-fill"></i> ${item.rating}</span>
                </div>
                <h4 style="font-size:14px;font-weight:800;margin:0 0 3px;color:#111111;line-height:1.25;">${item.name}</h4>
                <div style="font-size:12px;color:#666666;margin-bottom:4px;">
                    <i class="bi bi-shop"></i> ${item.vendor_name} &bull; ${item.category}
                </div>
                <div style="font-size:12px;color:#444444;margin-bottom:6px;">
                    <i class="bi bi-geo-alt"></i> ${item.city}
                </div>
                <div style="font-size:13px;font-weight:800;color:#d81a24;margin-bottom:8px;">
                    Rp ${item.price} <span style="font-size:10.5px;font-weight:500;color:#888888;">/${item.price_unit}</span>
                </div>
                <a href="${item.public_url}" target="_blank" class="uq-map-btn-dark" style="font-size:11px;height:30px;padding:0 10px;display:flex;justify-content:center;width:100%;box-sizing:border-box;">
                    Buka Halaman Layanan &rarr;
                </a>
            </div>
        `;
    }

    marker.bindPopup(popupContent);
    marker.addTo(markersLayer);

    allMarkers.push({
        type: item.type,
        id: item.id,
        marker: marker
    });
}

function focusOnPoint(lat, lng, type, id) {
    map.flyTo([lat, lng], 16, { animate: true, duration: 1 });

    const target = allMarkers.find(m => m.type === type && m.id === id);
    if (target) {
        target.marker.openPopup();
    }

    document.querySelectorAll('.loc-item-card').forEach(c => c.classList.remove('active-item'));
    const activeCard = document.getElementById(`card-${type}-${id}`);
    if (activeCard) {
        activeCard.classList.add('active-item');
        activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function resetMapBounds() {
    if (allMarkers.length > 0) {
        const group = new L.featureGroup(allMarkers.map(m => m.marker));
        map.fitBounds(group.getBounds().pad(0.12));
    } else {
        map.setView([-6.2287, 106.8360], 11);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initAdminMap();
});
</script>
@endsection
