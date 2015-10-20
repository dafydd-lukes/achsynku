<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <title>Varianty v korpusech řady ORAL</title>
    <link rel="stylesheet" href="css/bootstrap.ucnk.min.css" />
    <link rel="stylesheet" href="css/achsynku.css" />
    <script type="text/javascript" src="js/messaging.js"></script>
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="js/init.js"></script>
  </head>

<?php
// error_reporting(-1);
// ini_set('display_errors', 'On');
?>

  <body>
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
                 placeholder="Zadejte tvar či spisovné lemma, k němuž chcete vyhledat varianty vyskytující se v korpusech řady ORAL.">
        </div>
        <input class="btn btn-primary" type="submit" value="Vyhledat varianty">
      </form>

<?php
$query = $_GET['query'];
if ($query) {
?>

      <br/>
      <div id="result">

<?php
  $db = new SQLite3('achsynku.sqlite');
  // the query string is NFD normalized by default (for case insensitive
  // collation in sqlite3 to work) and needs to be escaped
  $esc_query = $db->escapeString($query);
  $sql_query = Normalizer::normalize("
  SELECT DISTINCT word
  FROM word2lemma
  WHERE lemma IN
      (SELECT '$esc_query'
       UNION SELECT lemma
       FROM word2lemma
       WHERE word = '$esc_query');
  ", Normalizer::FORM_D);
  $results = $db->query($sql_query);

  $variants = array();
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    array_push($variants, $row['word']);
  }

  if ($variants) {
    $cql_query = Normalizer::normalize('[word="'.join('|', $variants).'"]',
                                       Normalizer::FORM_C);

    echo "<p>CQL dotaz, který v korpusu vyhledá možné varianty tvaru/lemmatu <b>$query</b>:</p>";
?>

        <p>
          <textarea class="form-control"><?php echo $cql_query; ?></textarea>
        </p>

        <p>Dotaz si můžete ručně <b>upravit</b> (např. umazat varianty, které se vám
nehodí či jsou podle vás špatně) a rovnou <b>použít</b> pro hledání v korpusu (stačí
<b>kliknout</b> na tlačítko se jménem požadovaného korpusu; automaticky se použije
<b>aktuální podoba dotazu v okýnku výše</b>):</p>

        <p>
          <a href="#" target="_blank" class="btn btn-success corpus-search"
id="oral2006">Prohledat ORAL2006</a>
          <a href="#" target="_blank" class="btn btn-warning corpus-search"
id="oral2008">Prohledat ORAL2008</a>
          <a href="#" target="_blank" class="btn btn-danger corpus-search"
id="oral2013">Prohledat ORAL2013</a>
        </p>

        <div class="alert alert-warning">
          <p>
            <b>Upozornění</b>: nabízené varianty jsou založené na experimentální
lemmatizaci mluvených korpusů. Zkontrolujte si, zda odpovídají vašemu záměru, a
pokud narazíte na chybu, <a href="https://podpora.korpus.cz/">dejte nám
vědět</a>!
          </p>
        </div>

<?php
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
