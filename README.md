#Spis treści
- [Zadanie 1](#zadanie-1)

#Zadanie 1
Dane w postaci ciągów znaków zapisane w pliku [word_list.txt](http://wbzyl.inf.ug.edu.pl/nosql/doc/data/word_list.txt) skonwertowałem do pliku .csv i zaimportowałem do bazy MongoDB.
```sh
mongoimport --db test --collection words --type csv --headerline --file word_list.csv

imported 8199 objects
```

Wyszukiwanie anagramów za pomocą [skryptu](https://github.com/psynowczyk/Tnosql3/blob/master/anagramy.js)
```js
var connection = new Mongo();
var db = connection.getDB('test');

var m = function() {
	emit(this.word.split('').sort().toString(), this.word);
};

var r = function(key, values) {
	return values.toString();
};

coll = db.words;
var result = coll.mapReduce(m, r, {
	out: {inline: 1}
});

var anagramy = [];
for(x = 0; x < result.results.length; x++) {
	var record = result.results[x];
	var array = record.value.split(',');
	if(typeof(array[1]) != 'undefined') {
		anagramy.push(record.value);
	}
}

printjson(anagramy);
```

Wynik mapReduce zapisałem do pliku [anagramy.txt](https://github.com/psynowczyk/Tnosql3/blob/master/anagramy.txt)
```sh
mongo anagramy.js > anagramy.txt

head -100 anagramy.txt

[
	"abroad,aboard",
	"tablas,basalt",
	"bantam,batman",
	"vacate,caveat",
	"caiman,maniac",
	"rascal,scalar",
	"casual,causal",
	"dramas,madras",
	"raffia,affair",
	"manila,lamina,animal",
	"lariat,atrial",
	"saliva,avails",
	"marina,airman",
	"pinata,patina",
	"manual,alumna",
	"tarsal,altars,astral",
	"tantra,tartan,rattan",
	"dabber,barbed",
	"babier,barbie",
	"rabble,barbel",
	"braces,cabers",
	"carobs,cobras",
	"balded,bladed",
	"seabed,debase",
	"barged,badger,garbed",
	"abides,biased",
	"debark,barked,braked",
	"lambed,ambled,blamed,bedlam",
	"blared,balder",
	"abodes,adobes",
	"debars,breads,beards",
	"adverb,braved",
	"ribald,bridal",
	"disbar,braids",
	"adsorb,broads,boards",
	"enable,baleen",
	"rebate,berate,beater",
	"bagels,gables",
	"bather,breath",
	"labile,liable",
	"rabies,braise",
	"bakers,brakes,breaks",
	"marble,ramble,ambler,blamer",
	"ambles,blames",
	"nebula,unable",
	"blares,balers",
	"barley,bleary,barely",
	"stable,tables,ablest,bleats",
	"belays,basely",
	"tablet,battle",
	"nearby,barney",
	"barest,breast,baster",
	"abuser,bursae",
	"zebras,brazes",
	"basest,basset,bastes,beasts",
	"bairns,brains",
	"binary,brainy",
	"rumbas,umbras",
	"barony,baryon",
	"tabors,aborts",
	"sabots,boasts",
	"ipecac,icecap",
	"graced,cadger",
	"chased,cashed",
	"calked,lacked",
	"candle,lanced",
	"scaled,decals",
	"camped,decamp",
	"canoed,deacon",
	"dancer,craned",
	"dances,ascend",
	"canted,decant",
	"carped,redcap",
	"cadres,sacred,scared,cedars",
	"traced,crated,carted,redact",
	"craved,carved",
	"caused,sauced",
	"raceme,amerce",
	"seance,encase",
	"peaces,escape",
	"schema,sachem",
	"chaser,search,arches",
	"chases,cashes",
	"chaste,cheats,sachet",
	"eclair,lacier",
	"anemic,cinema,iceman",
	"packer,repack",
	"racket,tacker",
	"recall,cellar,caller",
	"camels,mescal",
	"lances,cleans",
	"alcove,coeval",
	"carpel,placer,parcel",
	"claret,cartel,rectal",
	"cleats,castle",
	"creams,scream",
	"canoes,oceans",
```