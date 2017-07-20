$(document).ready(function() {

  // Sidebar navigation
  $(".feature-nav ul li:first").addClass("active");


  // Make first feature active and others disappear
  $(".tab-pane:first").addClass("active in");


  // First Step for each feature is active
  $("ol").each(function() {
    var firstItem = $(this).find("li:first");
    firstItem.addClass("active");
  });


  // Add active class to first item in each slider
  $(".carousel-inner").each(function() {
    var firstItem = $(this).find(".item:first");
    firstItem.addClass("active");
  });


  // Changes the current step when slider changes for each feature.
  // This has to be called every time the feature changes to register .active .carousel
  function syncStepsWithSlider() {
    $(".tab-content .active .carousel").on('slide.bs.carousel', function(event) {
      var items = $(this).find(".item");
      var itemsLength = items.length;
      var item = event.relatedTarget;
      var itemIndex = items.index(item);

      var steps = $(".tab-content .active ol li");

      var step = steps.get(itemIndex);
      $(step).addClass("active");

      var previousStep;
      if (itemIndex == 0) {
         previousStep = steps.get(itemsLength - 1);
      }
      else {
         previousStep = steps.get(itemIndex - 1);
      }
      $(previousStep).removeClass("active");
    });
  }


  // Initialize first slider
  $(".carousel").first().carousel();
  syncStepsWithSlider();


  // Controls for slider inside iphone
  // Move Left
  $(".controls .move-left").click(function() {
    $(".tab-content .active .carousel").carousel('prev');
  });

  // Play/Pause
  $(".controls .play-pause").click(function() {
    $(".controls .play-pause i").toggleClass('fa-pause fa-play');

    if ($(".controls .play-pause i").hasClass('fa-pause')) {
      $(".tab-content .active .carousel").carousel();
    }
    else {
      $(".tab-content .active .carousel").carousel('pause');
    }
  });

  // Move Right
  $(".controls .move-right").click(function() {
    $(".tab-content .active .carousel").carousel('next');
  });


  // Play the feature slider when selected. Pause all the other sliders
  $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
    $(".controls .play-pause i").toggleClass("fa-pause fa-play");

    var newTab = e.target; // newly activated tab
    var previousTab = e.relatedTarget; // previous active tab

    var newHashFrag = newTab.href.split("#")[1];
    var previousHashFrag = previousTab.href.split("#")[1];

    console.log("New: " + newHashFrag);
    console.log("Previous " + previousHashFrag);

    $("#" + previousHashFrag + " .carousel").carousel("pause");
    $("#" + newHashFrag + " .carousel").carousel();

    // reregister steps with slider for new feature
    syncStepsWithSlider();
  });
});
