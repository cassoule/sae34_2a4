function open_overlay() {
    let overlay = document.getElementById("profil-overlay");
    let exiter = document.getElementById("overlay-exit");
    let opacity = overlay.style.opacity;
    if(opacity == 0){
        overlay.style.visibility = "visible";
        overlay.style.opacity = 1;
        overlay.style.transform = "translateX(0em)";
        exiter.style.visibility = "visible";
        exiter.style.opacity = 1;
    }else{
        overlay.style.visibility = "hidden";
        overlay.style.opacity = 0;
        overlay.style.transform = "translateX(17em)";
        exiter.style.visibility = "hidden";
        exiter.style.opacity = 0;
    }
}

function exit_overlay() {
    let overlay = dcoument.getElementById("profil-overlay");
    let exiter = document.getElementById("overlay-exit");
    let opacity = overlay.style.opacity;    
    overlay.style.visibility = "hidden";
    overlay.style.opacity = 0;
    overlay.style.transform = "translateX(17em)";
    exiter.style.visibility = "hidden";
    exiter.style.opacity = 0;
}

function scroll_down() {
    scrollTo(0, 737);
}


function moveLeft() {
    document.querySelector('.carousel-container').scrollBy(-350, 0);
}

function moveRight() {
    document.querySelector('.carousel-container').scrollBy(350, 0);
}

var overlayBack = document.querySelector(".overlay-back");
overlayBack.addEventListener('click', closeOverlayImages);

var img1 = document.getElementById("img-1");
var img2 = document.getElementById("img-2");
var img3 = document.getElementById("img-3");
var img4 = document.getElementById("img-4");
var img5 = document.getElementById("img-5");
var img6 = document.getElementById("img-6");
var img7 = document.getElementById("img-7");

img1.addEventListener('click', overlayImagesLogement);
img2.addEventListener('click', overlayImagesLogement);
img3.addEventListener('click', overlayImagesLogement);
img4.addEventListener('click', overlayImagesLogement);
img5.addEventListener('click', overlayImagesLogement);
img6.addEventListener('click', overlayImagesLogement);
img7.addEventListener('click', overlayImagesLogement);

var ovrImg1 = document.getElementById("ovr-img-1");
var ovrImg2 = document.getElementById("ovr-img-2");
var ovrImg3 = document.getElementById("ovr-img-3");
var ovrImg4 = document.getElementById("ovr-img-4");
var ovrImg5 = document.getElementById("ovr-img-5");
var ovrImg6 = document.getElementById("ovr-img-6");
var ovrImg7 = document.getElementById("ovr-img-7");

var currentPt
var currentImg;
var nextImg;
var currentId;
var imgNb;
var img;

function overlayImagesLogement(event) {
    imgNb = document.querySelectorAll(".ovr-img").length;
    currentImg = document.getElementById("ovr-img-" + event.target.id.slice(-1));
    currentId = event.target.id.slice(-1);
    currentPt = document.getElementById("pt-" + currentId);
    currentPt.role = "active";
    currentImg.alt = "active";
    var overlay = document.querySelector(".images-overlay");
    overlay.style.visibility = "visible";
    overlay.style.opacity = 1;
    if(currentId == 1){
        var leftId = imgNb;
        var rightId = parseInt(currentId) + 1;
    }else if(currentId == imgNb){
        var leftId = parseInt(currentId) - 1;
        var rightId = 1;
    }else{
        var leftId = parseInt(currentId) - 1;
        var rightId = parseInt(currentId) + 1;
    }
    document.getElementById(`ovr-img-${leftId}`).alt = "left";
    document.getElementById(`ovr-img-${rightId}`).alt = "right";
}

function overlayImage4() {
    imgNb = document.querySelectorAll(".ovr-img").length;
    currentImg = document.getElementById("ovr-img-4");
    currentId = "4";
    currentPt = document.getElementById("pt-" + currentId);
    currentPt.role = "active";
    currentImg.alt = "active";
    var overlay = document.querySelector(".images-overlay");
    overlay.style.visibility = "visible";
    overlay.style.opacity = 1;
}

function nextOverlayImage(){
    currentPt.role = "inactive";
    currentImg.alt = "left";
    if(currentId == imgNb){
        currentId = 1;
    }else{
        currentId++;
    }
    currentImg = document.getElementById("ovr-img-" + currentId);
    currentPt = document.getElementById("pt-" + currentId);
    currentPt.role = "active";
    currentImg.alt = "active";
    if(currentId == imgNb){
        var nextId = 1;
    }else{
        var nextId = parseInt(currentId) + 1;
    }
    var nextImg = document.getElementById(`ovr-img-${nextId}`);
    nextImg.alt = "right";
}

function previousOverlayImage(){
    currentPt.role = "inactive";
    currentImg.alt = "right";
    if(currentId == 1){
        currentId = imgNb;
    }else{
        currentId--;
    }
    currentImg = document.getElementById("ovr-img-" + currentId);
    currentPt = document.getElementById("pt-" + currentId);
    currentPt.role = "active";
    currentImg.alt = "active";
    if(currentId == 1){
        var nextId = imgNb;
    }else{
        var nextId = parseInt(currentId) - 1;
    }
    var nextImg = document.getElementById(`ovr-img-${nextId}`);
    nextImg.alt = "left";
}

function closeOverlayImages(){
    var overlay = document.querySelector(".images-overlay");
    overlay.style.opacity = 0;
    overlay.style.visibility = "hidden";
    currentImg.alt = "inactive";
    currentPt.role = "inactive";
}

function overlayGoTo(id){
    if(id != currentId){
        currentPt.role = "inactive";
        currentImg.alt = "inactive";
        
        currentId = id;
        currentImg = document.getElementById("ovr-img-" + currentId);
        currentPt = document.getElementById("pt-" + currentId);
        currentPt.role = "active";
        currentImg.alt = "active";
    }
}