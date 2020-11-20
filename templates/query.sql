SELECT DISTINCT word
FROM word2lemma
WHERE lemma IN
    (SELECT ?
     UNION SELECT lemma
     FROM word2lemma
     WHERE word = ?)
