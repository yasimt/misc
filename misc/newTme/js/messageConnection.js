var wsuri;
var user_id = '200';
if (document.location.origin == "file://") {
	wsuri = "ws://192.168.12.138:9000/ws";
} else {
	wsuri = (document.location.protocol === "http:" ? "ws:" : "wss:") + "//192.168.12.138:8080/ws";
}

var connection = new autobahn.Connection({
	url: wsuri,
	realm: "realm1"
});

document.addEventListener('DOMContentLoaded', function () {
  if (Notification.permission !== "granted")
    Notification.requestPermission();
});

function notifyMe(data) {
	
	var message = jQuery(data.message).text();
	
  if (!Notification) {
    alert('Desktop notifications not available in your browser. Try Chromium.'); 
    return;
  }

  if (Notification.permission !== "granted")
    Notification.requestPermission();
  else {
    var notification = new Notification('You Have Notification', {
      body: message,
      icon: 'http://akam.cdn.jdmagicbox.com/images/icontent/jdrwd/homepageicon.png',
      dir:'auto'
    });

    notification.onclick = function () {
     connection.session.publish('message.messageSend.close', [data]);
    };
    
  }
}

connection.onopen = function (session, details) {
};

connection.onclose = function (reason, details) {
	console.log("Connection lost: " + reason);
}
connection.open();

