var pickerMap = null;
var pickerMarker = null;
var pickedLat = null;
var pickedLng = null;
var currentTarget = null;

function openMapModal(target){
    currentTarget = target;
    var modal = new bootstrap.Modal(document.getElementById("mapModal"));
    modal.show();

    setTimeout(function(){
        if(!pickerMap){
            pickerMap = L.map("mapPicker").setView([11.6076, 125.4303], 13);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap"
            }).addTo(pickerMap);

            pickerMap.on("click", function(e){
                pickedLat = e.latlng.lat.toFixed(6);
                pickedLng = e.latlng.lng.toFixed(6);

                if(pickerMarker) pickerMarker.remove();

                pickerMarker = L.marker([pickedLat, pickedLng]).addTo(pickerMap)
                    .bindPopup(`<strong>Selected Location</strong><br>Lat: ${pickedLat}<br>Lng: ${pickedLng}`)
                    .openPopup();
            });
        }
        pickerMap.invalidateSize();
    }, 400);

    document.getElementById("mapModal").addEventListener("hidden.bs.modal", function(){
        if(pickedLat && currentTarget === "business"){
            document.getElementById("lat_business").value = pickedLat;
            document.getElementById("lng_business").value = pickedLng;
        }
    }, { once: true });
}