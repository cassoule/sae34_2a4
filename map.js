// New York
var startlat = 0;
var startlon = 0;

var options = {
 center: [startlat, startlon],
 zoom: 2
}

// document.getElementById('lat').value = startlat;
// document.getElementById('lon').value = startlon;

var map = L.map('map', options);
var nzoom = 11;

L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {attribution: 'OSM'}).addTo(map);

var myMarker = L.circle([startlat, startlon], {title: "Coordinates", alt: "Coordinates", draggable: true, radius: 3000}).addTo(map).on('dragend', function() {
    var lat = myMarker.getLatLng().lat.toFixed(8);
    var lon = myMarker.getLatLng().lng.toFixed(8);
    var czoom = map.getZoom();
    if(czoom != 18) { map.setView([lat,lon], nzoom); } else { map.setView([lat,lon]); }
    document.getElementById('lat').value = lat;
    document.getElementById('lon').value = lon;
    myMarker.bindPopup("Lat " + lat + "<br />Lon " + lon).openPopup();
});

function chooseAddr(lat1, lng1){
    myMarker.closePopup();
    map.setView([lat1, lng1], nzoom);
    myMarker.setLatLng([lat1, lng1]);
    // lat = lat1.toFixed(8);
    // lon = lng1.toFixed(8);
    // document.getElementById('lat').value = lat;
    // document.getElementById('lon').value = lon;
    var popUp = L.popup()
                .setLatLng([(lat1-(-.027)), lng1])
                .setContent(document.querySelector(".container > .adresse-row > h4").textContent)
                .openOn(map);
}

function myFunction(arr){
    if(arr.length > 0){
        for(var i = 0; i < 1; i++){
            chooseAddr(arr[i].lat, arr[i].lon)
        }
    }
}

function addr_search(){
    var inp = document.querySelector(".container > .adresse-row > h4");
    console.log(inp.textContent);
    var xmlhttp = new XMLHttpRequest();
    var url = "https://nominatim.openstreetmap.org/search.php?q=" + inp.textContent+ "&format=jsonv2";
    xmlhttp.onreadystatechange = function(){
        if (this.readyState == 4 && this.status == 200){
            var myArr = JSON.parse(this.responseText);
            myFunction(myArr);
        }
    };
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}

addr_search();