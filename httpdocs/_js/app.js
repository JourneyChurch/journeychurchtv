$(document).ready(function() {
  $(".feature-nav ul li:first").addClass("active");
  $(".tab-pane:first").addClass("active in");
  $("ol li:first").addClass("active");

  $(".carousel-inner .item:first").addClass("active");
  $(".carousel").carousel();

  $(".controls .move-left").click(function() {
    $(".carousel").carousel('prev');
  });

  $(".controls .play-pause").click(function() {
    $(".controls .play-pause i").toggleClass('fa-pause fa-play');

    if ($(".controls .play-pause i").hasClass('fa-pause')) {
      $(".carousel").carousel();
    }
    else {
      $(".carousel").carousel('pause');
    }
  });

  $(".controls .move-right").click(function() {
    $(".carousel").carousel('next');
  });

  var carousels = $(".tab-content .carousel");
  carousels.each(function(index) {
    $(this).on('slid.bs.carousel', function(event) {
      var item = event.relatedTarget;
      var itemIndex = $(".item").index(item);
      var itemsSize = $(".item").length;
      var featureNumber = index + 1;

      var steps = $("#ol-" + featureNumber + " li");

      var step = steps.get(itemIndex);
      $(step).addClass("active");

      var nextStep;
      if (itemIndex == 0) {
         previousStep = steps.get(itemsSize - 1);
      }
      else {
         previousStep = steps.get(itemIndex - 1);
      }
      $(previousStep).removeClass("active");
    });
  });
});
