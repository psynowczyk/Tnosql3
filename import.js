fs = require('fs');
var mongoose = require('mongoose');
mongoose.connect('mongodb://localhost/test');
var db = mongoose.connection;
db.on('error', console.error.bind(console, 'connection error:'));
db.once('open', function (callback) {
	var wordSchema = mongoose.Schema({
		'word': String
	});
	var Word = mongoose.model('Word', wordSchema);

	fs.readFile('word_list.txt', 'utf8', function (err,data) {
		if (err) return console.log(err);
		else {
			var words = data.split('\n');
			var counter = 0;
			for(var x=0; x<words.length; x++) {
				if(words[x].length > 0) {
					var w1 = new Word();
					w1.word = words[x];
					w1.save();
					counter++;
				}
			}
			console.log('Imported '+ counter +' records.');
			mongoose.connection.close();
		}
	});
});