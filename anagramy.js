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
for(x=0; x<result.results.length; x++) {
	var record = result.results[x];
	var array = record.value.split(',');
	if(typeof(array[1]) != 'undefined') {
		anagramy.push(record.value);
	}
}

printjson(anagramy);