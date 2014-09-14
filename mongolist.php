<?php
date_default_timezone_set("Europe/Madrid");

foreach( $config['config']['query'] as $query) {
	/*** EXECUTION TIME 1 ***/
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
	/*** EXECUTION TIME 1 ***/

    //TODO: configure LANG, COUNT
	$reply = $cb->search_tweets("q=".$query . "&lang=es&count=99", true);
	//print_r($reply);die();

    //una tabla por mes
    if (date('m') > 3) {
        $query_orig = $query;
        $query = "$query".date('Ym');
    }
    //end una tabla por mes

	$m = new MongoClient(); // conectar
	$db = $m->selectDB("test");
	$coll = $db->selectCollection($query);

	//$cursor = $names->find();




	//INSERT TWEETS
	$im = 0;
	foreach ($reply->statuses as $doc) {
		    //mentions
			preg_match_all("/((^|\s)@(\w+))+/", $doc->text, $mentions);
			if ($mentions) {
				foreach ($mentions as $mention) {
					if (empty($doc->mentions)) {
						$doc->mentions = $mentions[3];
					}
				}
			}
			//end mentions

			//print_r($doc);die();
			//insert if not already in db
			$already = $coll->findOne(array('id_str' => $doc->id_str));
			if ($already === NULL) {
				$filtered = NULL;
				if ($config['config']['filters'] || $config['config']['user_filters']){
					//TODO filters
					foreach ($config['config']['user_filters'] as $f) {
						$k = key($f);
						if ($doc->user->$k != $f[key($f)]) {
							$filtered = TRUE;
							continue;
						}
					}
				}
				if (!$filtered) {
					$coll->insert($doc);
					$im++;
				}
			}else{
				break;
			}
	}


	/*** EXECUTION TIME 2 ***/
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	$totaltime = number_format($totaltime, 2, ',', '.');
	/*** EXECUTION TIME 2 ***/



	//LOG INSERT
	if ($im > 0) {
		$coll = $db->selectCollection($query . "_LOG");
        $coll->insert(array('time'=>time(), 'inserted'=> $im, 'query' => $query_orig, 'user' => "{$config['config']['user']}", 'segs' => $totaltime));
	}

	echo "$im tweets insertados, en $totaltime segs en $query  [mongo]\n\r";



	//mentions & hastags
	$wip = $wop = 0;

	foreach ($reply->statuses as $doc) { //print_r($doc);die();
		$time   = $doc->created_at;
		$id_str = $doc->id_str;
		$in_reply_to_user   = $doc->in_reply_to_user_id_str;
		$in_reply_to_status = $doc->in_reply_to_status_id_str;
		$userid_from = $doc->user->id_str;
		$name_from   = $doc->user->screen_name;

		$coll = $db->selectCollection($query . "_mentions");

		foreach ($doc->entities->user_mentions as $mention) {
			$userid_to = $mention->id_str;
			$name_to   = $mention->screen_name;

			$already = $coll->findOne(array('id_str' => $doc->id_str));
			if ($already === NULL) {

				$coll->insert(array('time'        => $time,
									'id_str'      => $id_str,
									'in_reply_to_user'   => $in_reply_to_user,
									'in_reply_to_status' => $in_reply_to_status,
									'userid_from' => $userid_from,
									'name_from'   => $name_from,
									'userid_to'   => $userid_to,
									'name_to'     => $name_to

									));
				$wip++;
			}
		}

		$coll = $db->selectCollection($query . "_hashtags");
		foreach ($doc->entities->hashtags as $hashtag) {

			$already = $coll->findOne(array('id_str' => $doc->id_str));
			if ($already === NULL) {

				$coll->insert(array('time'        => $time,
									'id_str'      => $id_str,
									'userid_from' => $userid_from,
									'name_from'   => $name_from,
									'hashtag'     => $hashtag->text

									));
				$wop++;
			}
		}
	}
	echo $wip . " mentions inserted - " . $wop. " hashtags inserted\n\r";

}

?>
