$(document).ready(function () {
    $(".nav-icon").click(function () {
        $(".nav").fadeToggle(300);
        $("body").toggleClass("nav-visible");
    });
});
