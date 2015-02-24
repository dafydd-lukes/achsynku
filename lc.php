<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Test Unicode lowercasing in PHP</title>
</head>
<body>
<form>
    <input type="text" name="query"/>
    <input type="submit"/>
</form>
<?php

echo mb_strtolower($_GET['query'], "UTF-8");

?>
</body>
</html>
