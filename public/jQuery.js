// Jquery search
$(document).ready(function () {
  $("#searchInput").on("keyup", function () {
    var searchTerm = $(this).val().toLowerCase();
    // var found = false;
    $("#catList .cat-item").each(function () {
      var itemName = $(this).data("name");
      if (searchTerm === "" || itemName.indexOf(searchTerm) > -1) {
        $(this).css("display", "block");
        // found = true;
      } else {
        $(this).hide();
      }
    });
  });
});
