#!/usr/bin/env bash

rm -f achsynku.sqlite
cat <<EOF | sqlite3 achsynku.sqlite
create table word2lemma(id int primary key,
                        word text collate nocase,
                        lemma text collate nocase);
.separator "\t"
.import achsynku.tsv word2lemma
create index word_index on word2lemma (word);
create index lemma_index on word2lemma (lemma);
EOF
