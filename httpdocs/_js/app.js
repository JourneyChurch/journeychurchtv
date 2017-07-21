$(document).ready(function() {


  // Initialize ui with active elements
  function addActiveElements() {

    // Set first sidebar navigation item to active
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
  }


  // Register steps to change when slider changes. This has to be called everytime the feature changes
  function registerStepsWithSlider() {
    var activeTab = ".tab-content .active";

    // Called everytime the active slider changes slides
    $(activeTab + " .carousel").on('slide.bs.carousel', function(event) {

      // Find active slide credentials
      var slides = $(this).find(".item");
      var slidesLength = slides.length;
      var slide = event.relatedTarget;
      var slideIndex = slides.index(slide);

      // Get steps
      var steps = $(activeTab + " ol li");

      // Match step and slide by index
      var step = steps.get(slideIndex);

      // Add corresponding step with active
      $(step).addClass("active");

      // Determine step before and remove active from it
      var stepBefore;
      if (slideIndex == 0) {
         stepBefore = steps.get(slidesLength - 1);
      }
      else {
         stepBefore = steps.get(slideIndex - 1);
      }

      $(stepBefore).removeClass("active");


      // Determine step after and remove active from it
      var stepAfter;
      if (slideIndex == slidesLength - 1) {
         stepAfter = steps.get(0);
      }
      else {
         stepAfter = steps.get(slideIndex + 1);
      }

      $(stepAfter).removeClass("active");
    });
  }


  // Register controls for a feature slider. This has to be called everytime the feature changes
  function registerControls() {
    var activeTabControls = ".tab-content .active .controls";
    var activeTabCarousel = ".tab-content .active .carousel";

    // Move Left
    $(activeTabControls + " .move-left").unbind('click').click(function() {
      $(activeTabCarousel).carousel('prev');
    });

    // Play/Pause
    $(activeTabControls + " .play-pause").unbind('click').click(function() {
      $(activeTabControls + " .play-pause i").toggleClass('fa-pause fa-play');

      if ($(activeTabControls + " .play-pause i").hasClass('fa-pause')) {
        $(activeTabCarousel).carousel();
      }
      else {
        $(activeTabCarousel).carousel('pause');
      }
    });

    // Move Right
    $(activeTabControls + " .move-right").unbind('click').click(function() {
      $(activeTabCarousel).carousel('next');
    });
  }


  function init() {
    // Initialize active elements
    addActiveElements();

    // Start first slider
    $(".carousel").first().carousel();

    // Register Steps and Contols with slider
    registerStepsWithSlider();
    registerControls();
  }


  // Start app
  init();


  // Listen for tab changes. Play active slider. Pause all other sliders
  $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {

    // previous active tab
    var previousTab = e.relatedTarget;

    // new active tab
    var newTab = e.target;

    // Div ids obtained from hash fragments
    var previousHashFrag = previousTab.href.split("#")[1];
    var newHashFrag = newTab.href.split("#")[1];

    // Pause previous slider
    $("#" + previousHashFrag + " .carousel").carousel("pause");

    // Start new slider
    $("#" + newHashFrag + " .carousel").carousel();

    // Make sure pause is shown
    $("#" + newHashFrag + " .controls .play-pause i").removeClass("fa-play");
    $("#" + newHashFrag + " .controls .play-pause i").addClass("fa-pause");

    // reregister steps with slider for new feature
    registerStepsWithSlider();

    // reregister controls with new feature
    registerControls();
  });

});
