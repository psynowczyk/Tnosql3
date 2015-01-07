#Spis treści
- [Zadanie 1](#zadanie-1)

#Zadanie 1
Dane w postaci ciągów znaków zapisane w pliku [word_list.txt](http://wbzyl.inf.ug.edu.pl/nosql/doc/data/word_list.txt) zkonwertowałem do pliku .csv i zaimportowałem do bazy MongoDB.
```sh
mongoimport --db test --collection words --type csv --headerline --file word_list.csv

imported 8199 objects
```