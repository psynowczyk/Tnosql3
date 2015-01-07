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