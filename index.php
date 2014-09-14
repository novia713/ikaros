<?php
	/*** EXECUTION TIME 1 ***/
	$ttime = microtime();
	$ttime = explode(" ",$ttime);
	$ttime = $ttime[1] + $ttime[0];
	$tstarttime = $ttime;
	/*** EXECUTION TIME 1 ***/

define('KEY',    'N08DqCrPH55ExS8M6ItVQ');
define('SECRET', 'ut4Xbc7WrBmDmK6CtAK8bhAvzWcqxNL9kgOBrJ82ykM');

require_once ('inc/codebird.php');
\Codebird\Codebird::setConsumerKey(KEY, SECRET);

$cb = \Codebird\Codebird::getInstance();


//CONFIG
$config = require_once("conf.php");
if ($config['config']['query']) {
	if ($config['config']['db']['mongodb'] == 1) {
		require_once "mongolist.php";
		$script ="mongolist";
	}
	if ($config['config']['db']['mysql'] == 1) {
		require_once "mysqllist.php";
		$script = "mysqllist";
	}
}

if ($config['config']['list']) {
	require_once "mysqllista.php";
	$script ="mysqllista";
}
//CONFIG

	/*** EXECUTION TIME 2 ***/
	$ttime = microtime();
	$ttime = explode(" ",$ttime);
	$ttime = $ttime[1] + $ttime[0];
	$tendtime = $ttime;
	$ttotaltime = ($tendtime - $tstarttime);
	$ttotaltime = number_format($ttotaltime, 2, ',', '.');
	/*** EXECUTION TIME 2 ***/

echo "script ejecutado en $ttotaltime segs\n\r";
if (!@$m) $m = new MongoClient();
$db = $m->selectDB("test");
$coll = $db->selectCollection('scripts_timerun');
$coll->insert(array('time'=>time(), 'script' => "$script", 'user' => "{$config['config']['user']}", 'segs' => $ttotaltime));
?>
