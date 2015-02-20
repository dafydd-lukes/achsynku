<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Varianty v korpusech řady ORAL</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
<style>
#box {
  max-width: 55em;
  border-radius: 9px;
  background-color: #d2edc0;
  box-shadow: 0 0 4px #a0a0a0;
  box-sizing: border-box;
  padding: 20px;
}

#box > :first-child {
  margin-top: 0px;
}

#result > :last-child {
  margin-bottom: 0px;
}

a.btn {
  margin-right: 1em;
}

pre {
  white-space: pre-wrap;
}
</style>
<script>
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
</script>
  </head>

<?php
// error_reporting(-1);
// ini_set('display_errors', 'On');
?>

  <body onload="parent.postMessage(getElemHeightById('box'), '*');">
    <div id="box">
      <h3>Vyhledávač variant v korpusech řady ORAL</h3>
      <p>
Pokud si nejste jisti, v jakých variantách by se vámi hledaný tvar mohl v 
mluvených korpusech vyskytovat, můžete si pravděpodobné kandidáty najít pomocí 
tohoto formuláře. Výsledek se zobrazí rovnou v podobě CQL dotazu, který lze 
zadat do korpusového manažeru KonText. Zkuste si např. vyhledat, v jakých 
zápisových variantách se v korpusech řady ORAL vyskytuje slovo 
<em>protože</em>.
      </p>
      <form>
        <div class="form-group">
          <label for="query">Tvar, (spisovné) lemma</label>
          <input type="text" class="form-control" id="query" name="query"
                 placeholder="Zadejte tvar či spisovné lemma, k němuž chcete 
vyhledat varianty vyskytující se v korpusech řady ORAL.">
        </div>
        <input class="btn btn-primary" type="submit" value="Vyhledat varianty">
      </form>

<?php
$query = $_GET['query'];
if ($query) {
?>
      <br>
      <div id="result">

<?php
$db = new SQLite3('achsynku.sqlite');
$esc_query = $db->escapeString($query);
$sql_query = "
SELECT word
FROM word2lemma
WHERE lemma IN
    (SELECT '$esc_query'
     COLLATE NOCASE
     UNION SELECT lemma
     FROM word2lemma
     WHERE word = '$esc_query'
     COLLATE NOCASE);
";
$results = $db->query($sql_query);

$variants = array();
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
  array_push($variants, $row['word']);
}

if ($variants) {
  $cql_query = '[word="'.join('|', $variants).'"]';

  echo "<p>CQL dotaz, který v korpusu vyhledá možné varianty tvaru/lemmatu <b>$query</b>:</p>";
  echo '<p><pre>';
  echo $cql_query;
  echo '</pre></p>';
  echo '<p>Použít dotaz pro hledání v korpusu:</p>';
  echo "<p><a target='_blank' class='btn btn-success' href='https://kontext.korpus.cz/first?shuffle=1&reload=&corpname=omezeni%2Foral2006&queryselector=cqlrow&iquery=&phrase=&word=&char=&cql=$cql_query&default_attr=word&fc_lemword_window_type=both&fc_lemword_wsize=5&fc_lemword=&fc_lemword_type=all'>ORAL2006</a>";
  echo "<a target='_blank' class='btn btn-warning' href='https://kontext.korpus.cz/first?shuffle=1&reload=&corpname=omezeni%2Foral2008&queryselector=cqlrow&iquery=&phrase=&word=&char=&cql=$cql_query&default_attr=word&fc_lemword_window_type=both&fc_lemword_wsize=5&fc_lemword=&fc_lemword_type=all'>ORAL2008</a>";
  echo "<a target='_blank' class='btn btn-danger' href='https://kontext.korpus.cz/first?shuffle=1&reload=&corpname=omezeni%2Foral2013&queryselector=cqlrow&iquery=&phrase=&word=&char=&cql=$cql_query&default_attr=word&fc_lemword_window_type=both&fc_lemword_wsize=5&fc_lemword=&fc_lemword_type=all'>ORAL2013</a></p>";
  echo '<div class="alert alert-warning"><p><b>Upozornění</b>: nabízené varianty jsou založené na experimentální lemmatizaci mluvených korpusů. Zkontrolujte si, zda odpovídají vašemu záměru, a pokud narazíte na chybu, <a href="https://podpora.korpus.cz/">dejte nám vědět</a>!</p></div>';
} else {
  echo "<p>Tvar/lemma <b>$query</b> se v korpusech řady ORAL nevyskytuje a tudíž nemá ani žádné varianty.</p>";
}
?>

      </div>

<?php
}
?>
    </div>
  </body>
</html>
