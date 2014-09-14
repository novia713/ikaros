<?php
$ul = 0;
$tl = 0;
foreach( $config['config']['query'] as $query) {

    //TODO: configure LANG, COUNT
	$reply = $cb->search_tweets("q=".$query . "&lang=es&count=99", true);



	$mysqli = new mysqli("localhost", "root", "", "ikaros");
	//TODO: nombres de las tablas !! //FIXME


	//INSERT USERS
	foreach ($reply->statuses as $doc) {
			//print_r($doc->user);die();
			//insert if not already in db
			$already = $mysqli->query("SELECT id_str FROM users WHERE id_str='".$doc->user->id_str."'");
			if (mysqli_num_rows($already) > 0) continue;

			$sql ="INSERT INTO users
					(id_str, created_at, profile_image_url, url, description, screen_name, name, followers_count, friends_count, listed_count, lang, location)
					values
					('". $doc->user->id_str ."','". $doc->user->created_at ."','". $doc->user->profile_image_url ."','". $doc->user->url ."','". $doc->user->description ."','".
					     $doc->user->screen_name ."','". $doc->user->name ."',"  . $doc->user->followers_count   .",".   $doc->user->friends_count .",". $doc->user->listed_count .",'". $doc->user->lang ."','". $doc->user->location ."')";

			$mysqli->query($sql); //TODO: handle errors
			$ul++;
	}

	//INSERT TWEETS
	foreach ($reply->statuses as $doc) {
			//print_r($doc->entities);die();
			//insert if not already in db
			$already = $mysqli->query("SELECT id_str FROM tweets WHERE id_str='".$doc->id_str."'");
			if (mysqli_num_rows($already) > 0) continue;

			$coordinates = (@$doc->coordinates->coordinates)? implode(",", $doc->coordinates->coordinates) : NULL;
			$sql ="INSERT INTO tweets
					(id_str, user_mentions, retweet_count, favorite_count, coordinates, lang, country_code, place_name, text, in_reply_to_status_id_str, in_reply_to_user_id_str, created_at)
					values
					('". $doc->id_str ."','". @$doc->entities->user_mentions[0]->id_str ."',". $doc->retweet_count .",". $doc->favorite_count .",'". $coordinates ."','".
					     $doc->lang ."','". @$doc->place->country_code ."','"  . @$doc->place->name   ."','".   $doc->text ."','". $doc->in_reply_to_status_id_str ."','". $doc->in_reply_to_user_id_str ."','". $doc->created_at ."')";

			//die();
			$mysqli->query($sql); //TODO: handle errors
			$tl++;
	}

	echo "$ul usuarios insertados, $tl tuits insertados en $query [mysql]\r\n";

}
?>
