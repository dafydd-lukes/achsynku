#!/usr/bin/env bash

if [[ $1 = -h || $1 = --help ]]; then
    cat <<EOF
WARNING: run this script from AchSynku's root directory.

Usage: ./init_db.sh [input.tsv]

If input.tsv is not provided explicitly, achsynku.tsv is assumed.
EOF
    exit 0
fi

if [[ -z $1 ]]; then
    tsv=achsynku.tsv

    if [[ ! -f $tsv ]]; then
        echo "Fetching $tsv..."
        curl -O https://trnka.korpus.cz/~lukes/files/achsynku.tsv || {
            echo "Unable to fetch $tsv!"
            exit 1
        }
    fi
else
    tsv="$1"
fi

echo "NFD-normalizing input .tsv: $tsv..."
perl -i -CSAD -MUnicode::Normalize -nle 'print NFD $_' "$tsv"

echo "Removing old achsynku.sqlite database..."
rm -f achsynku.sqlite

echo "Creating new achsynku.sqlite database based on $tsv..."
cat <<EOF | sqlite3 achsynku.sqlite && echo "Done." && exit 0
create table word2lemma(id int primary key,
                        word text collate nocase,
                        lemma text collate nocase);
.separator "\t"
.import "$tsv" word2lemma
create index word_index on word2lemma (word);
create index lemma_index on word2lemma (lemma);
EOF

exit 1
