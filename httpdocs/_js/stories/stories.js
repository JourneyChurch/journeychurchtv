$(document).ready(function() {
  /*$("#fullpage").fullpage({
    onLeave: function(index, nextIndex, direction) {
        var leavingSection = $(this);

        setTimeout(function() {
            if (index == 2) {
                $('#stories-vid')[0].contentWindow.postMessage('{"event":"command","func":"' + 'pauseVideo' + '","args":""}', '*');
            }
        }, 500);
    },

    afterLoad: function(anchorLink, index) {
        var loadedSection = $(this);

        setTimeout(function() {
            if (index == 2) {
                $('#stories-vid')[0].contentWindow.postMessage('{"event":"command","func":"' + 'playVideo' + '","args":""}', '*');
            }
        }, 500);
    }
  });*/

  // Set section heights
  var windowHeight = $(window).height();

  $("section").css({
    "min-height" : windowHeight,
    "height" : windowHeight
  });

  // Validate entries
  var validator = $("#stories-form").validate();

  $("#stories-form").submit(function(event) {
    if (!$("#stories-form").valid()) {
      var invalids = validator.numberOfInvalids();

      $("#invalid").html("<h3>Oops... " + invalids + " of your fields are invalid.<br><br>Please review and resubmit your entry.</h3>");

      $("#invalid").fadeIn();

      setTimeout(
        function() {
          $("#invalid").fadeOut();
        }
      , 8000);

      return false;
    }
  });

  var sections = $("section");
  var sectionScrollTops = [];

  // Get the scroll tops of each section
  function setScrollTops() {
    sectionScrollTops = [];

    sections.each(function() {
      sectionScrollTops.push($(this).offset().top);
    });
  };

  // Find section index to determine where to scroll
  function getSectionIndex() {
    var currentScrollTop = $(document).scrollTop();

    for (var i = 0; i < (sectionScrollTops.length)-1; ++i) {
      if (currentScrollTop >= sectionScrollTops[i] && currentScrollTop < sectionScrollTops[i+1]) {
        return i;
      }
    }

    return i;
  };

  // Set the scroll tops
  setScrollTops();
  var sectionIndex;

  // When arrow up is clicked
  $(".arrow-up").click(function() {

    // Find what index it is in
    sectionIndex = getSectionIndex();

    // Scroll to correct section
    if ($(document).scrollTop() != sectionScrollTops[sectionIndex]) {
      $("html, body").animate({
        scrollTop: sectionScrollTops[sectionIndex]
      });
    }
    else {
      $("html, body").animate({
        scrollTop: sectionScrollTops[sectionIndex-1]
      });
    }
  });

  // When arrow down is clicked
  $(".arrow-down").click(function() {
    sectionIndex = getSectionIndex();

    $("html, body").animate({
      scrollTop: sectionScrollTops[sectionIndex+1]
    });
  });

  // Adjust heights of sections and scroll tops on resize
  $(window).resize(function() {
    windowHeight = $(window).height();

    $("section").css({
      "min-height" : windowHeight,
      "height" : windowHeight
    });

    setScrollTops();
  });
});
