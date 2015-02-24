// when a message is received from the parent window that it has been resized, 
// send a message to it requesting that the iframe be resized too

function getElemHeightById(id) {
  return document.getElementById(id).scrollHeight;
}

// cross-browser compatible infrastructure
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var eventer = window[eventMethod];
var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

// listen to message from parent window
eventer(messageEvent, function(e) {
  if (e.origin == "https://wiki.korpus.cz") {
    parent.postMessage(getElemHeightById("box"), "*");
  } else {
    console.log("Was expecting a message from https://wiki.korpus.cz, got "
      + e.origin + " instead.");
  }
});
