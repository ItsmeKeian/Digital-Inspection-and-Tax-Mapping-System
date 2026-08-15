let map;
let markerLayer;

$(document).ready(function(){
    initMap();
    bindFilters();
    loadMarkers();
});

function initMap(){
    map = L.map("map").setView([11.6087, 125.4319], 15);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap"
    }).addTo(map);
    markerLayer = L.layerGroup().addTo(map);
}

function bindFilters(){
    $("#filterBarangay, #filterStatus").on("change", loadMarkers);
    let debounce;
    $("#filterSearch").on("keyup", function(){
        clearTimeout(debounce);
        debounce = setTimeout(loadMarkers, 300);
    });
}

function getGoogleStyleIcon(name, color){
    let label = name.length > 22 ? name.substring(0, 20) + "…" : name;

    let html = `
        <div style="display:flex;align-items:center;gap:5px;white-space:nowrap">
            <!-- Colored circle -->
            <div style="
                width: 18px;
                height: 18px;
                border-radius: 50%;
                background: ${color};
                border: 2.5px solid #fff;
                box-shadow: 0 2px 6px rgba(0,0,0,0.4);
                flex-shrink: 0;
            "></div>
            <!-- Business name -->
            <div style="
                font-size: 11px;
                font-weight: 600;
                font-family: 'Segoe UI', sans-serif;
                color: #1C1400;
                background: rgba(255,255,255,0.92);
                padding: 2px 6px;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.15);
                white-space: nowrap;
            ">${label}</div>
        </div>
    `;

    let approxWidth = label.length * 6.5 + 30;

    return L.divIcon({
        className:   "",
        html:        html,
        iconSize:    [approxWidth, 20],
        iconAnchor:  [6, 10],
        popupAnchor: [approxWidth / 2, -10]
    });
}

function loadMarkers(){
    let barangay = $("#filterBarangay").val();
    let status   = $("#filterStatus").val();
    let search   = $("#filterSearch").val();

    markerLayer.clearLayers();

    $.get("php/get/get_map_locations.php", { barangay, status, search }, function(data){
        let rows  = JSON.parse(data);
        let count = 0;

        rows.forEach(r => {
            if(!r.latitude || !r.longitude) return;

            let lat = parseFloat(r.latitude);
            let lng = parseFloat(r.longitude);
            if(isNaN(lat) || isNaN(lng)) return;

            // Color per status
            let colorMap = {
                "Existing":     "#22C55E",
                "Unregistered": "#EF4444",
                "New":          "#3B82F6",
                "Closed":       "#9CA3AF",
                "Transferred":  "#F97316"
            };
            let color = colorMap[r.operation_status] || "#C8960C";

            // Popup badge colors
            let badgeBg  = { "Existing":"#D1FAE5","Unregistered":"#FEE2E2","New":"#DBEAFE","Closed":"#F3F4F6","Transferred":"#FEF3C7" };
            let badgeTxt = { "Existing":"#065F46","Unregistered":"#991B1B","New":"#1E40AF","Closed":"#6B7280","Transferred":"#92400E" };

            let marker = L.marker([lat, lng], {
                icon: getGoogleStyleIcon(r.business_name, color)
            }).addTo(markerLayer);

            // Popup on click
            let statusLabel = r.operation_status || "No Inspection";
            let bg  = badgeBg[r.operation_status]  || "#FDF6E3";
            let txt = badgeTxt[r.operation_status] || "#92400E";

            marker.bindPopup(`
                <div class="popup-name">${r.business_name}</div>
                <div class="popup-row"><i class="fas fa-user"></i> ${r.owner_name || "—"}</div>
                <div class="popup-row"><i class="fas fa-map-pin"></i> ${r.barangay || "—"}</div>
                <div style="margin-top:6px">
                    <span class="popup-status" style="background:${bg};color:${txt}">${statusLabel}</span>
                </div>
            `, { maxWidth: 200 });

            count++;
        });

        $("#markerCount").text(count);
    });
}