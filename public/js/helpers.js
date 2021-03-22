// ** FADE OUT FUNCTION **
const fadeOut = (el) => {
    el.style.opacity = 1;
    (function fade() {
        var val = parseFloat(el.style.opacity);
        if (!((val -= .1) < 0)) {
            el.style.opacity = val;
            requestAnimationFrame(fade);
        } else {
            el.style.display = "none";
        }
    })();
};

// ** FADE IN FUNCTION **
const fadeIn = (el, display) => {
    el.style.opacity = 0;
    el.style.display = display || "block";
    (function fade() {
        var val = parseFloat(el.style.opacity);
        if (!((val += .1) >= 1)) {
            el.style.opacity = val;
            requestAnimationFrame(fade);
        }
    })();
};