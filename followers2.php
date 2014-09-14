<?php
date_default_timezone_set("Europe/Madrid");


define('KEY',    'N08DqCrPH55ExS8M6ItVQ');
define('SECRET', 'ut4Xbc7WrBmDmK6CtAK8bhAvzWcqxNL9kgOBrJ82ykM');

require_once ('inc/codebird.php');
\Codebird\Codebird::setConsumerKey(KEY, SECRET);

$cb = \Codebird\Codebird::getInstance();

$file = file($argv[1]);

foreach ($file as $name) {
	$result = [];
	$result[0] = $cb->followers_list("screen_name=" . $name. "&count=4000&cursor=-1");

	$nextCursor = @$result[0]->next_cursor_str;

	$i = 1;
	if ( @$nextCursor ) {
		while ($nextCursor > 0) {
		  $result[$i] = $cb->followers_list('screen_name=' . $name. '&count=4000&cursor=' . $nextCursor);
		  $nextCursor = @$result[$i]->next_cursor_str;
		  //echo "paso $i → ". count($result[$i]->users)."\n\r";
		  $i++;
		sleep(25);
		}
	}

	$_ =[];
	foreach ($result as $r) {

		foreach ($r->users as $item) {
			$_[] = (string)@$item->screen_name;
		}
	}
	if (is_array($_)) echo "{ $name: [". implode(",", @$_) ."]}\n";
}





















?>
