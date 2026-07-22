<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (shouldShowPopup()) {
            setTimeout(function() {
                document.getElementById("popup").classList.add("active");
                document.getElementsByTagName("body")[0].style.overflow = "hidden";
            }, 30000);
        }
    });

    document.getElementById("close-popup").addEventListener("click", function() {

        document.getElementById("popup").classList.remove("active");
        document.getElementsByTagName("body")[0].style.overflow = "auto";

        setPopupClosedFlag();
    });

    document.getElementsByClassName("form-btn")[0].addEventListener("click", function() {
        setFormSubmittedFlag();
    });

    function shouldShowPopup() {
        const lastClosed = localStorage.getItem("popupLastClosed");
        const formSubmitted = localStorage.getItem("formSubmitted");

        if (!formSubmitted && (!lastClosed || Date.now() - lastClosed > 3600000)) {
            return true;
        }
        return false;
    }

    function setPopupClosedFlag() {
        localStorage.setItem("popupLastClosed", Date.now());
    }

    function setFormSubmittedFlag() {
        localStorage.setItem("formSubmitted", "true");
    }
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadInfusionRecaptchaCallback&render=explicit"
    async="async" defer="defer"></script>
