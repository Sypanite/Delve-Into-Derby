/**
 * Handles the progression of the bar. Based on example code by W3 found at
 * http://www.w3schools.com/w3css/w3css_progressbar.asp
 **/
function progress() {
    var e = document.getElementById("myBar"); 
    var width = 1;
    var id = setInterval(frame, 10);

    function frame() {
        if (width >= 100) {
            clearInterval(id);
        }
		else {
            width++; 
            elem.style.width = width + '%'; 
        }
    }
}
