$(document).ready(function() {
  $(".nav-icon").click(function() {
    $(".nav").fadeToggle(300);
    $(".nav-icon").toggleClass("nav-visible");
  });
});
