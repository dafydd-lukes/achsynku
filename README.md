# Try me out

## Where?

[Here](https://wiki.korpus.cz/doku.php/kurz:hledani_v_mluvenych_korpusech#jak_spravne_zadat_hledane_slovo)
or [here](https://trnka.korpus.cz/~lukes/achsynku/index.php).

## What am I good for?

Searching for transcription variants of a word form or lemma in the ORAL series
corpora.

## How do I work?

I have a database which stores all the unique (word, lemma) pairs occurring in
the experimentally lemmatized `oral_v4` corpus. I take the **string** you
specify as **input**, **lowercase** it and **match** it against the lowercased
versions of the **lemmas** and **word forms** I know about. I **return** all the
unique **word forms** x that satisfy either of the following criteria:

- lc(string) ∈ lc(lemma(x))<sup><a name="fn1-ref" href="#fn1">1</a></sup>

- | lc(lemma(string)) ∩ lc(lemma(x)) | > 0

In SQL terms what happens is the following -- I have a `word2lemma` table which
lists all the unique (word, lemma) pairs and their lowercase variants (the `id`
column is irrelevant, it's just the primary key):

| id | word | word_lc | lemma | lemma_lc |
|---|---|---|---|---|
| ... | ... | ... | ... | ... |
| 998 | ale | ale | ale | ale |
| 999 | Ale | ale | ale | ale |
| 1000 | ále | ále | ale | ale |
| ... | ... | ... | ... | ... |

And I run the following query against it (where `$query_string` is the
lowercased version of the input string entered by the user):

```sql
SELECT DISTINCT word
FROM word2lemma
WHERE lemma_lc IN
    (SELECT '$query_string'
     UNION SELECT lemma_lc
     FROM word2lemma
     WHERE word_lc = '$query_string');
```

**NB**: The table column with the case-sensitive variant of the lemma is not
currently being used for anything but is kept around in case it is needed in the
future for implementing more refined search options.

# Maintenance and deployment

## Updating the database based on a new lemmatization

Steps 1 and 2 are only necessary if you use a custom (word, lemma) mapping file
instead of the provided one (`achsynku.tsv`). Step 3 can be automated by
running the `init_db.sh` script from the root directory of the app.

1. Create a `.tsv` file which lists all the unique (word, lemma) pairs in
the corpus.

2. Prepend an `id` column, which is just a unique numeric index (create it with
`seq` on the command line and `paste` it as the first column).

3. Import the `.tsv` file into the database, taking care to specify `COLLATE
NOCASE` for the text columns, and create indices for faster searching.

## Embedding the variant search box in another webpage as an iframe

```html
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

# Why "AchSynku"?

[Because](http://cs.wikipedia.org/wiki/Ach_synku,_synku):

> Ach [syn](http://wiki.korpus.cz/doku.php/cnk:syn)ku, synku, doma-li jsi?
>
> Tatíček se ptá, [oral](http://wiki.korpus.cz/doku.php/cnk:oral2013)-li jsi?
>
> Oral jsem oral, ale málo,
>
> množství těch tvarů mě rozeštkalo...

# License

Copyright © 2015 David Lukeš

Distributed under the
[GNU General Public License v3](http://www.gnu.org/licenses/gpl-3.0.en.html).

---

<a name="fn1" href="#fn1-ref">1</a>: If the results returned by the lemma()
relation are all strings that are also valid word forms, this criterion is
superfluous. In general however, lemmas can take a form which doesn't exactly
correspond to any of their corresponding word forms (e.g. when distinguishing
multiple word senses, as in *tear^1* and *tear^2*). This is not the case here,
yet we still implement this criterion for another, purely practical reason: our
`word2lemma` table only contains entries for word forms which have actually
occurred in the corpus. In other words, if the only occurrence of the lemma
*zženštilý* is as the word form *zženštilej*, lemma(*zženštilý*) will return an
empty set, but we still want to return *zženštilej* as a result to the query
*zženštilý*, hence the need to check lemma(*zženštilej*) == {*zženštilý*}
directly against the query *zženštilý*.
