class FacebookEvent {
  private id: number;
  private attending_count: number;
  private cover: string;
  private description: string;
  private end_time: Date;
  private name: string;
  private place: string;
  private start_time: Date;

  public constructor(id: number, attending_count: number, cover: string, description: string, end_time: Date, name: string, place: string, start_time: Date) {
    this.id = id;
    this.attending_count = attending_count;
    this.cover = cover;
    this.description = description;
    this.end_time = end_time;
    this.name = name;
    this.place = place;
    this.start_time = start_time;
  }

  public render(): string {
    return `<div class="col-sm-12">${this.id}</div>`;
  }

  public getEvents(): void {
    $.ajax({
      url: "/php/events/getEvents.php",
      success: function(result) {
        var events = JSON.parse(result).data;
        $.each(events, function(index, e) {
          
        });
        console.log(result);
      },
      error: function(e) {
        alert(e);
      }
    });
  }
}
