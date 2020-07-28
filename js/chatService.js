const chatService = (function () {
  $("#empty-chat").hide();
  $("#group-message-holder").hide();
  $("#loading-message-container").hide();
  $("#send-message-spinner").hide();

  let messageArray = [];

  return {
    initializeApp: function () {
      let cometChatAppSetting = new CometChat.AppSettingsBuilder()
        .subscribePresenceForAllUsers()
        .setRegion("US")
        .build();

      CometChat.init(APP_ID, cometChatAppSetting).then(
        () => {
          console.log("Initialization completed successfully");
          this.retrieveUserDetails($("#username").text());
        },
        (error) => {
          console.log("Initialization failed with error:", error);
        }
      );
    },
    retrieveUserDetails: function (username) {
      CometChat.getUser(username).then(
        (user) => {
          console.log("User details fetched for user:", user);
          this.authLoginUser(user.authToken);
        },
        (error) => {
          console.log("User details fetching failed with error:", error);
          this.createUserOnCometChat(username);
        }
      );
    },
    createUserOnCometChat: function (username) {
      let url = `https://api-us.cometchat.io/v2.0/users`;
      let data = {
        uid: username,
        name: `${username} sample`,
        avatar:
          "https://data-us.cometchat.io/assets/images/avatars/captainamerica.png",
      };

      fetch(url, {
        method: "POST",
        headers: new Headers({
          appid: APP_ID,
          apikey: REST_API_KEY,
          "Content-Type": "application/json",
        }),
        body: JSON.stringify(data),
      })
        .then((response) => response.json())
        .then((result) => {
          this.addUserToAGroup(result.data.uid);
          console.log(result, "User created");
        })
        .catch((error) => console.log(error));
    },
    addUserToAGroup: function (uid) {
      let url = `https://api-us.cometchat.io/v2.0/groups/supergroup/members`;
      let data = {
        participants: [uid],
      };

      fetch(url, {
        method: "POST",
        headers: new Headers({
          appid: APP_ID,
          apikey: REST_API_KEY,
          "Content-Type": "application/json",
        }),
        body: JSON.stringify(data),
      })
        .then((response) => response.json())
        .then((result) => {
          this.generateAuthToken(uid);
          console.log(result, "User added to a group");
        });
    },
    generateAuthToken: function (uid) {
      let url = `https://api-us.cometchat.io/v2.0/users/${uid}/auth_tokens`;

      fetch(url, {
        method: "POST",
        headers: new Headers({
          appid: APP_ID,
          apikey: REST_API_KEY,
          "Content-Type": "application/json",
        }),
      })
        .then((response) => response.json())
        .then((result) => {
          console.log(result, "Token generated");
          this.authLoginUser(result.data.authToken);
        });
    },
    authLoginUser: function (token) {
      $("#loading-message-container").show();

      CometChat.login(token).then(
        (User) => {
          console.log("Login successfully");
          this.getLoggedInUser();
        },
        (error) => {
          alert(
            "Whops. Something went wrong. This commonly happens when you enter a username that doesn't exist. Check the console for more information"
          );
          console.log("Login failed with error:", error);
        }
      );
    },
    getLoggedInUser: function () {
      CometChat.getLoggedinUser().then(
        (user) => {
          $("#username").text(user.name);
          $("#loggedInUserAvatar").attr("src", user.avatar);
          $("#loggedInUID").val(user.uid);

          $("#loading-message-container").hide();

          this.fetchMessages();
        },
        (error) => {
          console.log(error);
        }
      );
    },
    fetchMessages: function () {
      const messagesRequest = new CometChat.MessagesRequestBuilder()
        .setLimit(100)
        .build();
      messagesRequest.fetchPrevious().then(
        (messages) => {
          messageArray = [...messageArray, ...messages];

          if (messageArray.length < 1) {
            $("#empty-chat").show();
            $("#group-message-holder").hide();
          } else {
            $("#group-message-holder").show();
          }

          $.each(messageArray, function (index, value) {
            let messageList;
            let currentLoggedUID = $("#loggedInUID").val();

            if (value.sender.uid != currentLoggedUID) {
              messageList = `
                            <div class="received-chats old-chats">
                            <div class="received-chats-img">
                                <img src="${value.sender.avatar}" alt="Avatar" class="avatar">
                            </div>
        
                            <div class="received-msg">
                                <div class="received-msg-inbox">
                                    <p>
                                        <span id="message-sender-id">${value.sender.uid}</span><br />
                                        ${value.data.text}
                                    </p>
                                </div>
                            </div>
                        </div>                    
                            `;
            } else {
              messageList = `
                            <div class="outgoing-chats old-chats">
                                <div class="outgoing-chats-msg">
                                    <p>${value.data.text}</p>
                                </div>
                                <div class="outgoing-chats-img">
                                    <img src="${value.sender.avatar}" alt="" class="avatar">
                                </div>
                            </div>
        `;
            }

            $("#group-message-holder").append(messageList);
          });
          this.scrollToBottom();
        },
        (error) => {
          console.log("Message fetching failed with error:", error);
        }
      );
    },
    sendMessage: function () {
      $("#send-message-spinner").show();
      let receiverID = "supergroup";
      let messageText = $("#input-text").val();
      let receiverType = CometChat.RECEIVER_TYPE.GROUP;

      let textMessage = new CometChat.TextMessage(
        receiverID,
        messageText,
        receiverType
      );

      CometChat.sendMessage(textMessage).then(
        (message) => {
          $("#message-form").trigger("reset");
          messageArray = [...messageArray, message];

          $.each(messageArray, function (index, value) {
            let messageList;
            let currentLoggedUID = $("#loggedInUID").val();

            if (value.sender.uid != currentLoggedUID) {
              messageList = `
                            <div class="received-chats old-chats">
                                <div class="received-chats-img">
                                    <img src="${value.sender.avatar}" alt="Avatar" class="avatar">
                                </div>
            
                                <div class="received-msg">
                                    <div class="received-msg-inbox">
                                        <p>
                                            <span id="message-sender-id">${value.sender.uid}</span><br />
                                            ${value.data.text}
                                        </p>
                                    </div>
                                </div>
                            </div>                    
                            `;
            } else {
              messageList = `
                            <div class="outgoing-chats old-chats">
                                <div class="outgoing-chats-msg">
                                    <p>${value.data.text}</p>
                                </div>
                                <div class="outgoing-chats-img">
                                    <img src="${value.sender.avatar}" alt="" class="avatar">
                                </div>
                            </div>
        `;
            }

            $("#group-message-holder").append(messageList);
          });

          this.onMessageReceived();
          this.scrollToBottom();
        },
        (error) => {
          console.log("Message sending failed with error:", error);
        }
      );
    },
    onMessageReceived: function () {
      $("#empty-chat").hide();
      $("#group-message-holder").show();
      $("#send-message-spinner").hide();
      let listenerID = "UNIQUE_LISTENER_ID";

      CometChat.addMessageListener(
        listenerID,
        new CometChat.MessageListener({
          onTextMessageReceived: (textMessage) => {
            messageArray = [...messageArray, textMessage];

            $(".old-chats").remove();

            $.each(messageArray, function (index, value) {
              let messageList;
              let currentLoggedUID = $("#loggedInUID").val();

              if (value.sender.uid != currentLoggedUID) {
                messageList = `
                                <div class="received-chats old-chats">
                                    <div class="received-chats-img">
                                        <img src="${value.sender.avatar}" alt="Avatar" class="avatar">
                                    </div>
                
                                    <div class="received-msg">
                                        <div class="received-msg-inbox">
                                            <p>
                                                <span id="message-sender-id">${value.sender.uid}</span><br />
                                                ${value.data.text}
                                            </p>
                                        </div>
                                    </div>
                               </div>                    
                                `;
              } else {
                messageList = `
                                <div class="outgoing-chats old-chats">
                                    <div class="outgoing-chats-msg">
                                        <p>${value.data.text}</p>
                                    </div>
                                    <div class="outgoing-chats-img">
                                        <img src="${value.sender.avatar}" alt="" class="avatar">
                                    </div>
                                </div>
            `;
              }

              $("#group-message-holder").append(messageList);
            });
            this.scrollToBottom();
          },
        })
      );
    },
    scrollToBottom() {
      const chat = document.getElementById("msg-page");
      chat.scrollTo(0, chat.scrollHeight + 30);
    },
  };
})();
