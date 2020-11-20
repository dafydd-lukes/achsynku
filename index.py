#!/usr/bin/env python3

# enable debugging
# __import__("cgitb").enable()

# for cgitb to work, headers must have already been output when an error
# is encountered -> print them right off the bat
if __name__ == "__main__":
    print("Content-Type: text/html;charset=utf-8")
    print()

import cgi
import sqlite3
from pathlib import Path
from unicodedata import normalize

ROOT = Path(__file__).absolute().parent
DB = ROOT / "achsynku.sqlite"
TPLS = ROOT / "templates"
BASE = (TPLS / "base.html").read_text("utf-8")
WRAPPER = (TPLS / "wrapper.html").read_text("utf-8")
RESULT = (TPLS / "result.html").read_text("utf-8")
NO_RESULT = (TPLS / "no_result.html").read_text("utf-8")
SQL = (TPLS / "query.sql").read_text("utf-8")


def find_variants(query):
    """Determine variants for ``query`` found in ORAL series corpora.

    >>> find_variants("abÚzus")
    ['abuse']

    # NOTE: a 'Tata' variant actually exists, but since we're using
    # collate nocase, it's not returned; to make sure it's subsequently
    # found when searching the corpora, (?i) is added to the CQL query
    >>> find_variants("Tata")
    ['tata', 'tatou', 'tatové', 'tatovi', 'tatu', 'taty']

    >>> find_variants("zŽeNštilÝ")
    ['zženštilej']

    >>> find_variants("a b c")
    []

    """
    # the query string is NFD normalized because case insensitive
    # collation in sqlite3 only works for ASCII letters
    query = normalize("NFD", query)
    conn = sqlite3.connect(str(DB))
    return [
        normalize("NFC", v)
        for v in sorted(row[0] for row in conn.execute(SQL, (query, query)))
    ]


def main():
    form = cgi.FieldStorage()
    query = form.getfirst("query")
    if query is None:
        result = ""
    else:
        # sqlite3 makes sure query is SQL-escaped...
        variants = find_variants(query)
        # ... but since I'm using str.format as our "template engine",
        # I must take care of HTML-escaping manually
        query = cgi.escape(query)
        if variants:
            variants = "|".join(variants)
            # since I switched to the NFD trick + collate nocase instead
            # of precomputing lowercased data and inserting it into
            # separate columns in the database (cf. 99606f8), the query
            # returns only lowercase variants, so (?i) is needed to find
            # all case variants in the corpora
            cql_query = f'[word="(?i){variants}"]'
            result = RESULT.format(query=query, cql_query=cql_query)
        else:
            result = NO_RESULT.format(query=query)
        result = WRAPPER.format(content=result)
    print(BASE.format(result=result))


if __name__ == "__main__":
    main()

# vi: ft=python:
