function init() {
    // set up automatic resizing of textareas according to content
    var textareas = document.getElementsByTagName("textarea");

    for (var i = 0; i < textareas.length; i++) {

        // add a listener to each textarea for continuous height updates
        textareas[i].addEventListener("keyup", (function() {
            var textarea = textareas[i];

            // perform a starting height update based on initial content
            textarea.style.height = textarea.scrollHeight + "px";

            // this function will be run on each new typing event
            return function() {
                // update the textarea height
                textarea.style.height = textarea.scrollHeight + "px";
                // inform the parent frame our height may have changed
                parent.postMessage(getElemHeightById("box"), "*");
            }
        })());

    }

    // tell the parent frame how tall we are
    parent.postMessage(getElemHeightById("box"), "*");
}
