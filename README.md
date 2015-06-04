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
specify as **input**, **lowercase** it and **match** it against the lowercased
versions of the **lemmas** and **word forms** I know about. I **return** all the
**word forms** x that satisfy either of the following criteria:

- lc(lemma(x)) == lc(string)

- lc(lemma(x)) == lc(lemma(string))

In SQL terms what happens is the following -- I have a `word2lemma` table which
lists all the unique (word, lemma) pairs and their lowercase variants (the `id`
column is irrelevant, it's just the primary key):

| id | word | word_lc | lemma | lemma_lc |
|---|---|---|---|---|
| ... | ... | ... | ... | ... |
| 998 | ale | ale | ale | ale |
| 999 | Ale | ale | ale | ale |
| 1000 | ále | ále |ale | ale |
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

1. Create a `.tsv` file which lists all the unique (word, lemma) pairs in
the corpus.

2. Prepend an `id` column, which is just a unique numeric index (create it with
`seq` on the command line and `paste` it as the first column).

3. Add `word_lc` and `lemma_lc` columns after the `word` and `lemma` columns
respectively, storing the lowercase variants of the entries in the corresponding
rows. (Use e.g. `perl` and `lc` to generate them; don't forget `-CSD` to get
proper UTF-8 handling!)

4. Import the `.tsv` file into the database and index it on the lowercase
columns (which are the only ones being matched against currently).

SQLite cheat sheet:

```sql
$ sqlite3 achsynku.sqlite
sqlite> drop table word2lemma;
sqlite> create table word2lemma(id int primary key,
   ...>                         word text,
   ...>                         word_lc text,
   ...>                         lemma text,
   ...>                         lemma_lc text);
sqlite> .separator "\t"
sqlite> .import achsynku.tsv word2lemma
sqlite> create index word_lc_index on word2lemma(word_lc);
sqlite> create index lemma_lc_index on word2lemma(lemma_lc);
```

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
