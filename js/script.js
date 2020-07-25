$(document).ready(function () {
  chatService.initializeApp();

  $("#message-form").submit(function (e) {
    e.preventDefault();

    $(".old-chats").remove();
    chatService.sendMessage();

    $("#message-form").trigger("reset");
  });
});
