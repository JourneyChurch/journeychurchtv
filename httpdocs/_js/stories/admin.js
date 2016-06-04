$(document).ready(function () {

  // Set min height of admin
  $("#admin-main").css("min-height", $(document).height()-$("header").outerHeight());

  // Jquery Datepicker for filtering dates
  $( "#from" ).datepicker({
    defaultDate: "+1w",
    changeMonth: true,
    changeYear: true,
    onClose: function( selectedDate ) {
      $( "#to" ).datepicker( "option", "minDate", selectedDate );
    }
  });

  $( "#to" ).datepicker({
    defaultDate: "+1w",
    changeMonth: true,
    changeYear: true,
    onClose: function( selectedDate ) {
      $( "#from" ).datepicker( "option", "maxDate", selectedDate );
    }
  });

  // Validation for dates
  $("#filter-form").validate({
    rules: {
      from: {
        date: true,
        minlength: 10
      },
      to: {
        date: true,
        minlength: 10
      }
    }
  });

  //"<input type='checkbox' id='abuse' name='abuse'> <label for='marriage'>Marriage</label><br><input type='checkbox' id='addiction' name='addiction'> <label for='addiction'>Addiction</label><br><input type='checkbox' id='adoption' name='adoption'> <label for='adoption'>Adoption</label><br><input type='checkbox' id='anger' name='anger'> <label for='anger'>Anger</label><br><input type='checkbox' id='apathy' name='apathy'> <label for='apathy'>Apathy</label><br><input type='checkbox' id='bitterness' name='bitterness'> <label for='bitterness'>Bitterness</label><br><input type='checkbox' id='death-&-loss' name='death-&-loss'> <label for='death-&-loss'>Death & Loss</label><br><input type='checkbox' id='disappointment' name='disappointment'> <label for='disappointment'>Disappointment</label><br><input type='checkbox' id='doubt' name='doubt'> <label for='doubt'>Doubt</label><br><input type='checkbox' id='family' name='family'> <label for='family'>Family</label><br><input type='checkbox' id='financial' name='financial'> <label for='financial'>Financial</label><br><input type='checkbox' id='forgiveness' name='forgiveness'> <label for='forgiveness'>Forgiveness</label><br><input type='checkbox' id='gods-love' name='gods-love'> <label for='gods-love'>God's Love</label><br><input type='checkbox' id='grace' name='grace'> <label for='grace'>Grace</label><br><input type='checkbox' id='healing-recovery' name='healing-recovery'> <label for='healing-recovery'>Healing/Recovery</label><br><input type='checkbox' id='hope' name='hope'> <label for='hope'>Hope</label><br><input type='checkbox' id='journey-groups' name='journey-groups'> <label for='journey-groups'>Journey Groups</label><br><input type='checkbox' id='life-change' name='life-change'> <label for='life-change'>Life Change</label><br><input type='checkbox' id='love-relationships' name='love-relationships'> <label for='love-relationships'>Love/Relationships</label><br><input type='checkbox' id='marriage' name='marriage'> <label for='marriage'>Marriage</label><br><input type='checkbox' id='mercy' name='mercy'> <label for='mercy'>Mercy</label><br><input type='checkbox' id='miracle' name='miracle'> <label for='miracle'>Miracle</label><br><input type='checkbox' id='missions' name='missions'> <label for='missions'>Missions</label><br><input type='checkbox' id='natural-disasters' name='natural-disasters'> <label for='natural-disasters'>Natural Disasters</label><br><input type='checkbox' id='parenting' name='parenting'> <label for='parenting'>Parenting</label><br><input type='checkbox' id='patience' name='patience'> <label for='patience'>Patience</label><br><input type='checkbox' id='persecution' name='persecution'> <label for='persecution'>Persecution</label><br><input type='checkbox' id='prophecy' name='prophecy'> <label for='prophecy'>Prophecy</label><br><input type='checkbox' id='reconciliation' name='reconciliation'> <label for='reconciliation'>Reconciliation</label><br><input type='checkbox' id='religion' name='religion'> <label for='religion'>Religion</label><br><input type='checkbox' id='salvation' name='salvation'> <label for='salvation'>Salvation</label><br><input type='checkbox' id='school' name='school'> <label for='school'>School</label><br><input type='checkbox' id='serving' name='serving'> <label for='serving'>Serving</label><br><input type='checkbox' id='work' name='work'> <label for='work'>Work</label><br>"

  // function that prints stories from JSON values. Adds admin functionality to each story
  function printStories(data) {

    // empty stories feed to show new results
    $("#admin-main").empty();

    // parse as JSON
    var stories = JSON.parse(data);

    // arrays to update ids, classes, and attributes using js
    var ids = [];
    var statuses = [];
    var emails = [];
    var categories = [];

    // loop through each JSON story and print out story with values
    $(stories).each(function() {
      $("#admin-main").append("<div class='story'><!-- ** HEADER ** --><div class='header'><div class='date col-sm-3 col-xs-4'>"+this.date+"</div><div class='status col-sm-3 col-xs-4'>"+this.status.replace(/-/g, " ").toUpperCase()+"</div><div class='edit-category col-sm-1 col-xs-2 col-sm-offset-1 text-center'><i class='fa fa-pencil-square-o'></i></div><div class='trash col-sm-1 col-xs-2 text-center'><i class='fa fa-trash-o'></i></div><div class='col-sm-3'><button id='save-' class='save btn btn-default btn-block'>Save</button></div><div class='clearfix'></div></div><!-- ** CONTENT ** --><div class='content'><div class='col-sm-12'><div class='col-sm-12'><h3>"+this.name+"</h3></div><div class='clearfix'></div> <div class='more'><div class='entry col-sm-6'><p><strong>Where does your story begin? Describe that season…</strong><br>"+this.beginning+"</p></div><div class='entry col-sm-6'><p><strong>Can you further describe how you persevered during that season?</strong><br>"+this.persevered+"</p></div><div class='clearfix'></div> <div class='entry col-sm-6'><p><strong>How did that season change you and impact your story? Where were you before? Where are you now?</strong><br>"+this.growth+"</p></div><div class='entry email col-sm-6'><p><strong>Email</strong><br><a>"+this.email+"</a></p></div><div class='clearfix'></div></div></div><div class='clearfix'></div></div><!-- ** FOOTER ** --><div class='footer'><div class='col-sm-12'><div class='edit-categories'><div class='col-sm-6'><form><h4>Update Categories</h4><input type='checkbox' id='abuse' name='abuse'> <label for='abuse'>Abuse</label><br><input type='checkbox' id='addiction' name='addiction'> <label for='addiction'>Addiction</label><br><input type='checkbox' id='adoption' name='adoption'> <label for='adoption'>Adoption</label><br><input type='checkbox' id='anger' name='anger'> <label for='anger'>Anger</label><br><input type='checkbox' id='apathy' name='apathy'> <label for='apathy'>Apathy</label><br><input type='checkbox' id='bitterness' name='bitterness'> <label for='bitterness'>Bitterness</label><br><input type='checkbox' id='death-&-loss' name='death-&-loss'> <label for='death-&-loss'>Death & Loss</label><br><input type='checkbox' id='disappointment' name='disappointment'> <label for='disappointment'>Disappointment</label><br><input type='checkbox' id='doubt' name='doubt'> <label for='doubt'>Doubt</label><br><input type='checkbox' id='family' name='family'> <label for='family'>Family</label><br><input type='checkbox' id='financial' name='financial'> <label for='financial'>Financial</label><br><input type='checkbox' id='forgiveness' name='forgiveness'> <label for='forgiveness'>Forgiveness</label><br><input type='checkbox' id='gods-love' name='gods-love'> <label for='gods-love'>God's Love</label><br><input type='checkbox' id='grace' name='grace'> <label for='grace'>Grace</label><br><input type='checkbox' id='healing-recovery' name='healing-recovery'> <label for='healing-recovery'>Healing/Recovery</label><br><input type='checkbox' id='hope' name='hope'> <label for='hope'>Hope</label><br><input type='checkbox' id='journey-groups' name='journey-groups'> <label for='journey-groups'>Journey Groups</label><br><input type='checkbox' id='life-change' name='life-change'> <label for='life-change'>Life Change</label><br><input type='checkbox' id='love-relationships' name='love-relationships'> <label for='love-relationships'>Love/Relationships</label><br><input type='checkbox' id='marriage' name='marriage'> <label for='marriage'>Marriage</label><br><input type='checkbox' id='mercy' name='mercy'> <label for='mercy'>Mercy</label><br><input type='checkbox' id='miracle' name='miracle'> <label for='miracle'>Miracle</label><br><input type='checkbox' id='missions' name='missions'> <label for='missions'>Missions</label><br><input type='checkbox' id='natural-disasters' name='natural-disasters'> <label for='natural-disasters'>Natural Disasters</label><br><input type='checkbox' id='parenting' name='parenting'> <label for='parenting'>Parenting</label><br><input type='checkbox' id='patience' name='patience'> <label for='patience'>Patience</label><br><input type='checkbox' id='persecution' name='persecution'> <label for='persecution'>Persecution</label><br><input type='checkbox' id='prophecy' name='prophecy'> <label for='prophecy'>Prophecy</label><br><input type='checkbox' id='reconciliation' name='reconciliation'> <label for='reconciliation'>Reconciliation</label><br><input type='checkbox' id='religion' name='religion'> <label for='religion'>Religion</label><br><input type='checkbox' id='salvation' name='salvation'> <label for='salvation'>Salvation</label><br><input type='checkbox' id='school' name='school'> <label for='school'>School</label><br><input type='checkbox' id='serving' name='serving'> <label for='serving'>Serving</label><br><input type='checkbox' id='work' name='work'> <label for='work'>Work</label><br></form></div><div class='col-sm-6'><h4>Update Status</h4><select><option value='new'>New</option><option value='reviewed'>Reviewed</option><option value='in-progress'>In Progress</option><option value='active'>Active</option><option value='completed'>Completed</option></select></div><div class='clearfix'></div></div><div class='categories'></div><div class='more-info col-sm-1 col-xs-2 col-sm-offset-11 col-xs-offset-10 text-center'><i class='fa fa-chevron-down'></i></div><div class='clearfix'></div></div><div class='clearfix'></div></div><div class='clearfix'></div>");

      // update arrays with corresponding values
      ids.push(this.id);
      statuses.push(this.status);
      emails.push(this.email);
      categories.push(this.categories);
    });

    // Store all story divs
    var storyDivs = $(".story");

    // Loop through them
    $(storyDivs).each(function(index) {
      var storyId = "story-"+ids[index];
      var storyHashId = "#"+storyId;

      // set unique story id
      $(this).attr("id", storyId);

      // Fill in categories/set checks of edit categories
      $(categories[index]).each(function() {
        $(storyHashId+" .categories").append("<div class='col-sm-6'><div class='category'>"+this.replace(/-/g, " ")+"</div></div>");

        $(storyHashId+" form input[name='"+this+"']").attr("checked", "checked");
      });

      $(storyHashId+" .categories").append("<div class='clearfix'></div>");

      // Update status in edit
      $(storyHashId+" select").val(statuses[index]);


      // Add status class, unique save id, and email address link
      $(storyHashId+" .status").addClass(statuses[index]);
      $(storyHashId+" .save").attr("id", "save-"+ids[index]);
      $(storyHashId+" .email a").attr("href", "mailto:"+emails[index]);

      // Add functionality for remove button
      $(storyHashId+" .trash").click(function() {
        $(storyHashId).toggleClass("remove");
        $(storyHashId+" .trash i").toggleClass("fa-times fa-trash-o");
      });

      // Add functionality for more info
      $(storyHashId+" .more-info").click(function() {
        $(storyHashId+" .more").slideToggle();
        $(storyHashId+" .more-info i").toggleClass("fa-chevron-up fa-chevron-down");

        $(storyHashId+" .edit-categories").slideUp();
        $(storyHashId+" .edit-category i").removeClass("fa-chevron-up");
        $(storyHashId+" .edit-category i").addClass("fa-pencil-square-o");
      });

      // Add functionlity for editing categories
      $(storyHashId+" .edit-category").click(function() {
        $(storyHashId+" .edit-categories").slideToggle();
        $(storyHashId+" .edit-category i").toggleClass("fa-chevron-up fa-pencil-square-o");

        $(storyHashId+" .more").slideUp();
        $(storyHashId+" .more-info i").removeClass("fa-chevron-up");
        $(storyHashId+" .more-info i").addClass("fa-chevron-down");
      });

      // Add functionality for save
      $("#save-"+ids[index]).click(function() {

        // Three things that need to be updated
        var id = ids[index];
        var remove = 0;
        var categoryNames = [];
        var checked = [];

        // if has class remove, set remove to 1
        if ($(storyHashId).hasClass("remove")) {
          remove = 1;
        }

        categories = $(storyHashId+" .edit-categories form input");

        $(categories).each(function() {
          categoryNames.push($(this).attr("name"));

          if ($(this).is(':checked')) {
            checked.push(1);
          }
          else {
            checked.push(0);
          }
        });

        // get status value
        var status = $(storyHashId+" select").val();

        // find all checked categories in edit categories and push into array
        var checkedCategories = $(storyHashId+" .edit-categories form").find("input[type=checkbox]:checked");

        // Reset all filtering fields
        $("#filter-categories").find("input[type=checkbox]:checked").removeAttr("checked");
        $("#filter-statuses select").val("all");
        $("#filter-date input").val("");

        // Update stories admin
        $.ajax({
          type: "POST",
          url: "/_scripts/stories/admin_update.php",
          data: {
            id : id,
            remove : remove,
            categoryNames : categoryNames,
            checked : checked,
            status : status
          },
          success: function(data) {
            printStories(data);

            $("#notification").fadeIn();

            setTimeout(function() {
              $("#notification").fadeOut();
            }, 4000);
          }
        });
      });
    });
  };

  $.ajax({
    type: "POST",
    url: "/_scripts/stories/get_stories.php",
    success: function(data) {
      printStories(data);
    }
  });

  // On filtering submit
  $("#filter").click(function() {

    // If form is valid
    if ($("#filter-form").valid()) {

      // Get all checked categories
      var categoriesChecks = $("#filter-categories input");
      var categories = [];

      // For each checked category push its name into categories
      categoriesChecks.each(function() {
        if ($(this).is(":checked")) {
          categories.push($(this).attr("name"));
        }
      });

      // Get status from select
      var status = $("select").val();

      var date;
      var split;
      var startDate;
      var endDate;

      // Get start date from first date selector
      if ($("#from").val() != "") {
          date = $("#from").val();
          splitDate = date.split("/");
          startDate = splitDate[2] + "-" + splitDate[0] + "-" + splitDate[1] + " 00:00:01";
      }

      // Get end date from second date selector
      if ($("#to").val() != "") {
          date = $("#to").val();
          splitDate = date.split("/");
          endDate = splitDate[2] + "-" + splitDate[0] + "-" + splitDate[1] + " 23:59:59";
      }

      $.ajax({
        type: "POST",
        url: "/_scripts/stories/get_stories.php",
        data: {
          categories : categories,
          status : status,
          startDate : startDate,
          endDate : endDate
        },
        success: function(data) {
          printStories(data);
        }
      });
    }
  });
});
