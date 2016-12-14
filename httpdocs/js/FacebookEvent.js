var FacebookEvent = (function () {
    function FacebookEvent(id, attending_count, cover, description, end_time, name, place, start_time) {
        this.id = id;
        this.attending_count = attending_count;
        this.cover = cover;
        this.description = description;
        this.end_time = end_time;
        this.name = name;
        this.place = place;
        this.start_time = start_time;
    }
    FacebookEvent.prototype.render = function () {
        return "<div class=\"col-sm-12\">" + this.id + "</div>";
    };
    FacebookEvent.prototype.getEvents = function () {
        $.ajax({
            url: "/php/events/getEvents.php",
            success: function (result) {
                var events = JSON.parse(result).data;
                $.each(events, function (index, e) {
                });
                console.log(result);
            },
            error: function (e) {
                alert(e);
            }
        });
    };
    return FacebookEvent;
})();
