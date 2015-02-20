<!DOCTYPE html>
<html>
<title>AchSynku README</title>

<xmp theme="simplex">
# Try me out

## Where?

[Here](https://wiki.korpus.cz/doku.php/kurz:hledani_v_mluvenych_korpusech#jak_spravne_zadat_hledane_slovo)
or [here](index.php).

## What am I good for?

Searching for transcription variants of a word form or lemma in the ORAL series
corpora.

## How do I work?

I have a database which stores all the unique (word, lemma) pairs occurring in
the experimentally lemmatized `oral_v4` corpus. I take the **string** you
specify as **input** and **match** it both against the **lemmas** and **word
forms**. I **return** all the **word forms** x that satisfy either of the
following criteria:

- lemma(x) == string

- lemma(x) == lemma(string)

# Maintenance and deployment

## Updating the database based on a new lemmatization

0. Make a backup of the old database. ;)

1. Create a `.tsv` file which lists all the unique (id, word, lemma) pairs in
the corpus. *id* is just a unique numeric index (create it with `seq` on the
command line and `paste` it as the first column).

2. Import the `.tsv` file into the database and index it on both columns.

SQLite cheat sheet:

```
$ sqlite3 achsynku.sqlite
sqlite> drop table word2lemma;
sqlite> create table word2lemma(id int primary key, word text, lemma text);
sqlite> .separator "\t"
sqlite> .import id_word_lemma.tsv word2lemma
sqlite> create index word_index on word2lemma(word);
sqlite> create index lemma_index on word2lemma(lemma);
```

## Embedding the variant search box in another webpage as an iframe

```
<html>
<script>
function resizeIframe(pixels) {
    document.getElementById("varianty").style.height = pixels + "px";
}

// cross-browser compatible infrastructure
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var eventer = window[eventMethod];
var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

// listen to message from iframe
eventer(messageEvent, function(e) {
  if (e.origin == "https://trnka.korpus.cz") {
    var key = e.message ? "message" : "data";
    var data = e[key];
    resizeIframe(data);
  } else {
    console.log("Was expecting a message from https://trnka.korpus.cz, got " + e.origin + " instead.");
  }
}, false);

// send message to iframe on window resize
window.onresize = function() {
  document.getElementById("varianty").contentWindow.postMessage("parentWindowResized", "*");
}
</script>
<iframe id="varianty" src="https://trnka.korpus.cz/~lukes/achsynku/" frameborder="0" width="100%"></iframe>
</html>
```
</xmp>

<script src="https://trnka.korpus.cz/~lukes/strapdown/v/0.2/strapdown.js"></script>
</html>
