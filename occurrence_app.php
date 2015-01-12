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