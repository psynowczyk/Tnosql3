#Spis treści
- [Zadanie 1](#zadanie-1)
- [Zadanie 2](#zadanie-2)

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

Wynik mapReduce zapisałem do pliku [anagramy.txt](https://github.com/psynowczyk/Tnosql3/blob/master/anagramy.txt)<br>
Przykładowe rekordy:
```sh
mongo anagramy.js > anagramy.txt

head -10 anagramy.txt

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
	"manila,lamina,animal"
```

Diagram wygenerowany za pomocą [skryptu](https://github.com/psynowczyk/Tnosql3/blob/master/anagramy_app.php)
```php
<?php

	$anagrams = [
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
		"barged,badger,garbed"
		// ...
	];
	
	$colors = [
		'#b8b8b8',
		'#a3a3a3',
		'#8f8f8f',
		'#7a7a7a',
		'#666666',
		'#525252',
		'#3d3d3d',
		'#292929',
		'#141414',
		'#000000'	
	];

	$result = '';
	foreach ($anagrams as $anagram) {
		$keys = '';
		$words = split(',', $anagram);
		$color = $colors[count($words)-2];
		foreach ($words as $word) {
			$keys .= '<div class="word">'.$word.'</div>';
		}
		$result .= '<div class="squere" style="background-color: '.$color.';">'.$keys.'</div>';
	}

	$myfile = fopen('anagramy_graph.html', 'w') or die('Unable to open file!');

	$txt = '
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8">
				<title>Occurrence graph</title>
				<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
				<style>
					body							{font-family: "Open Sans", sans-serif; margin: 20px; padding: 0; width: calc(100% - 40px); height: calc(100% - 40px);}
						.box						{width: 728px;}
							.squere				{float: left; padding: 2px 0; width: 55px; height: 89px; border-right: 1px solid #ffffff; border-bottom: 1px solid #ffffff;}
							.squere:nth-child(13n)	{width: 56px; border-right: none;}
								.word				{font-size: 11px; width: 100%; height: 12px; line-height: 12px; text-align: center; color: #ffffff;}
				</style>
			</head>
			<body>
				<div class="box">'.$result.'</div>
			</body>
		</html>
	';
	fwrite($myfile, $txt);
	fclose($myfile);
?>
```
![alt text](https://raw.githubusercontent.com/psynowczyk/Tnosql3/master/anagramy_graph.png "")

#Zadanie 2
Dane zapisane w pliku plwiki-latest-pages-articles.xml.bz2 zaimportowałem do bazy MongoDB za pomocą [skryptu](https://github.com/psynowczyk/Tnosql3/blob/master/import_xml.php).
```php
#!/bin/env php
<?php

/**
 * @copyright 2013 James Linden <kodekrash@gmail.com>
 * @author James Linden <kodekrash@gmail.com>
 * @link http://jameslinden.com/dataset/wikipedia.org/xml-dump-import-mongodb
 * @link https://github.com/kodekrash/wikipedia.org-xmldump-mongodb
 * @license BSD (2 clause) <http://www.opensource.org/licenses/BSD-2-Clause>
 */

$dsname = 'mongodb://localhost/test';
$file = 'plwiki-latest-pages-articles.xml.bz2';
$logpath = './';

/*************************************************************************/

date_default_timezone_set('Europe/Warsaw');

function abort( $s ) {
	die( 'Aborting. ' . trim( $s ) . PHP_EOL );
}

if( !is_file( $file ) || !is_readable( $file ) ) {
	abort( 'Data file is missing or not readable.' );
}

if( !is_dir( $logpath ) || !is_writable( $logpath ) ) {
	abort( 'Log path is missing or not writable.' );
}

$in = bzopen( $file, 'r' );
if( !$in ) {
	abort( 'Unable to open input file.' );
}

$out = fopen( rtrim( $logpath, '/' ) . '/wikipedia.org_xmldump-' . date( 'YmdH' ) . '.log', 'w' );
if( !$out ) {
	abort( 'Unable to open log file.' );
}

try {
	$dc = new mongoclient( $dsname );
	$ds = $dc->selectdb( trim( parse_url( $dsname, PHP_URL_PATH ), '/' ) );
} catch( mongoconnectionexception $e ) {
	abort( $e->getmessage() );
}
$ds_page = new mongocollection( $ds, 'page' );
$ds_ns = [];

$time = microtime( true );

$start = false;
$chunk = null;
$count = 0;
$line = null;
while( !feof( $in ) ) {
	$l = bzread( $in, 1 );
	if( $l === false ) {
		abort( 'Error reading compressed file.' );
	}
	if( $l == PHP_EOL ) {
		$line = trim( $line );
		if( $line == '<namespaces>' || $line == '<page>' ) {
			$start = true;
		}
		if( $start === true ) {
			$chunk .= $line . PHP_EOL;
		}
		if( $line == '</namespaces>' ) {
			$start = false;
			$chunk = str_replace( [ 'letter">', '</namespace>' ], [ 'letter" name="', '" />' ], $chunk );
			$x = simplexml_load_string( $chunk );
			if( $x ) {
				foreach( $x->namespace as $y ) {
					$y = (array)$y;
					$dns = [ 'id' => (int)$y['@attributes']['key'], 'name' => null ];
					if( array_key_exists( 'name', $y['@attributes'] ) ) {
						$dns['name'] = (string)$y['@attributes']['name'];
					}
					$ds_ns[ $dns[ 'id' ] ] = $dns;
				}
				$chunk = null;
			} else {
				abort( 'Unable to parse namespaces.' );
			}
		} else if( $line == '</page>' ) {
			$start = false;
			$x = simplexml_load_string( $chunk );
			$chunk = $line = null;
			if( $x ) {
				$dpage = [ '_id' => (int)$x->id, 'title' => (string)$x->title, 'ns' => $ds_ns[ (int)$x->ns ] ];
				if( $x->redirect ) {
					$y = (array)$x->redirect;
					$dpage['redirect'] = $y['@attributes']['title'];
				} else {
					$dpage['redirect'] = false;
				}
				if( $x->revision ) {
					$drev = [ 'id' => (int)$x->revision->id, 'parent' => (int)$x->revision->parentid ];
					$drev['timestamp'] = new mongodate( strtotime( (string)$x->revision->timestamp ) );
					if( $x->revision->contributor ) {
						$drev['contributor'] = [
							'id' => (int)$x->revision->contributor->id,
							'username' => (string)$x->revision->contributor->username
						];
					}
					$drev['minor'] = $x->revision->minor ? true : false;
					$drev['comment'] = (string)$x->revision->comment;
					$drev['sha1'] = (string)$x->revision->sha1;
					$drev['length'] = strlen( (string)$x->revision->text );
					$drev['text'] = (string)$x->revision->text;
					$dpage['revision'] = $drev;
					unset( $drev );
				}
				try {
					if( $ds_page->save( $dpage ) ) {
						$count ++;
						$m = date( 'Y-m-d H:i:s' ) . chr(9) . $dpage['_id'] . chr(9) . $dpage['title'] . PHP_EOL;
						fwrite( $out, $m );
						echo $m;
					}
				} catch( mongocursorexception $e ) {
					abort( $e->getmessage() );
				}
			}
		}
		$line = null;
	} else {
		$line .= $l;
	}
}

fclose( $out );
bzclose( $in );

echo PHP_EOL;

?>
```

```sh
time php import_xml.php

real	61m 18.178s
user	56m 14.447s
sys	1m 18.528s
```

```sh
db.page.count()
1671883
```

Obliczenia za pomocą [skryptu](https://github.com/psynowczyk/Tnosql3/blob/master/occurrence.js)
```js
var connection = new Mongo();
var db = connection.getDB('test');

var m = function() {
	var array = this.revision.text.match(/([a-zA-Z]+|[^\x00-\x7F]+)+/g);
	if(array != null) array.forEach(function(word) {
		emit(word, 1);
	});
};

var r = function(key, values) {
	return Array.sum(values);
};

coll = db.page;
coll.mapReduce(m, r, {
	out: 'pr'
});
```

```sh
time mongo occurrence.js

real	137m 30.636s
user	0m 0.025s
sys	0m 0.007s
```

```js
db.pr.count()
5441098

db.pr.find({value: {$gt: 100}}).sort({value: -1})
{ "_id" : "w", "value" : 13318251 }
{ "_id" : "i", "value" : 5673827 }
{ "_id" : "align", "value" : 4910631 }
{ "_id" : "–", "value" : 4812528 }
{ "_id" : "na", "value" : 4483914 }
{ "_id" : "z", "value" : 4411273 }
{ "_id" : "ref", "value" : 4264212 }
{ "_id" : "data", "value" : 3495151 }
{ "_id" : "Kategoria", "value" : 3169246 }
{ "_id" : "do", "value" : 2823615 }
{ "_id" : "center", "value" : 2719307 }
{ "_id" : "się", "value" : 2571716 }
{ "_id" : "http", "value" : 2324475 }
{ "_id" : "br", "value" : 2221204 }
{ "_id" : "W", "value" : 2072932 }
{ "_id" : "www", "value" : 2053453 }
{ "_id" : "left", "value" : 2037762 }
{ "_id" : "tytuł", "value" : 1668387 }
{ "_id" : "roku", "value" : 1511654 }
{ "_id" : "small", "value" : 1466680 }
{ "_id" : "a", "value" : 1449271 }
{ "_id" : "style", "value" : 1432662 }
{ "_id" : "bgcolor", "value" : 1400557 }
{ "_id" : "flaga", "value" : 1370419 }
{ "_id" : "px", "value" : 1339655 }
{ "_id" : "nie", "value" : 1290672 }
{ "_id" : "r", "value" : 1286691 }
{ "_id" : "RD", "value" : 1275575 }
{ "_id" : "jest", "value" : 1260071 }
{ "_id" : "pl", "value" : 1235733 }
{ "_id" : "name", "value" : 1098228 }
{ "_id" : "o", "value" : 1077834 }
{ "_id" : "język", "value" : 1059868 }
{ "_id" : "nazwa", "value" : 1056992 }
{ "_id" : "Plik", "value" : 1023572 }
{ "_id" : "to", "value" : 1021988 }
{ "_id" : "przez", "value" : 1010958 }
{ "_id" : "url", "value" : 1005558 }
{ "_id" : "infobox", "value" : 995637 }
{ "_id" : "span", "value" : 991489 }
Type "it" for more
```

Diagram wygenerowany za pomocą [skryptu](https://github.com/psynowczyk/Tnosql3/blob/master/occurrence_app.php)
```php
<?php

	$connection = new MongoClient();
	$db = $connection->test;
	$coll = $db->pr;

	$cursor = $coll->find()->sort(array('value' => -1))->limit(200);
	$result = '';
	$keys = '';
	foreach ($cursor as $doc) {
		$width = ((int)$doc['value'] / 13318251) * 100;
		$result .= '
			<div class="bar">
				<div class="stick" style="width: '.$width.'%;"></div>
				<div class="value">'.$doc['value'].'</div>
			</div>
		';
		$keys .= '<div class="key">'.$doc['_id'].'</div>';
	}

	$myfile = fopen('occurrence_graph.html', 'w') or die('Unable to open file!');

	$txt = '
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8">
				<title>Occurrence graph</title>
				<style>
					body							{margin: 20px; padding: 0; width: calc(100% - 40px); height: calc(100% - 40px);}
						.keys						{float: left; width: 100px; margin-right: 10px; text-align: right;}
							.key					{font-size: 9px; margin-top: 1px; width: 100%; height: 9px; line-height: 9px;}
							.key:first-child	{margin-top: 0;}
						.box						{float: left; padding-left: 10px; width: 607px; border-left: 1px solid #000000;}
							.bar					{margin-top: 1px; width: inherit; height: 9px; line-height: 9px;}
								.stick			{float: left; max-width: 507px; height: inherit; background-color: #247eb3;}
								.value			{float: left; font-size: 9px; margin-left: 2px; width: 98px; height: inherit;}
							.bar:first-child	{margin-top: 0;}
				</style>
			</head>
			<body>
				<div class="keys">'.$keys.'</div>
				<div class="box">'.$result.'</div>
			</body>
		</html>
	';
	fwrite($myfile, $txt);
	fclose($myfile);
?>
```
![alt text](https://raw.githubusercontent.com/psynowczyk/Tnosql3/master/occurrence_graph.png "")