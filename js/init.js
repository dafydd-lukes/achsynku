$(document).ready(function() {
    /**
    /* set up automatic resizing of all textareas according to content
     *
     * NOTE: this snippet does not require jQuery, it can be simply used within
     * a body onload function.
     */
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
                textarea.style.height = 0;
                textarea.style.height = textarea.scrollHeight + "px";
                // inform the parent frame our height may have changed
                parent.postMessage(getElemHeightById("box"), "*");
            }
        })());

    }

    /**
     * set up listeners for dynamically generating search links based on the
     * CQL query textarea when the corpus search buttons are clicked
     */
    $(".corpus-search").click(function(e) {
        e.preventDefault();
        var corpName = $(this).attr("id");
        var currQuery = encodeURIComponent($("textarea").val());
        var url = 'https://kontext.korpus.cz/first?shuffle=1&reload=&corpname=omezeni%2F'
                    + corpName
                    + '&queryselector=cqlrow&iquery=&phrase=&word=&char=&cql='
                    + currQuery
                    + '&default_attr=word&fc_lemword_window_type=both&fc_lemword_wsize=5&fc_lemword=&fc_lemword_type=all';
        console.log(url);
        window.open(url);
    });

    /**
    /* tell the parent frame how tall we are
     */
    parent.postMessage(getElemHeightById("box"), "*");
});
