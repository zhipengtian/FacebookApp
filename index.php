<?php
  /* This code is used for connecting facebook app to the databse
   * and storing info into our database */

require './config.php';
require './facebook.php';

session_start();
//create facebook application instance
$facebook = new Facebook(array(
  'appId' => $fb_app_id,
  'secret' => $fb_secret,
  'cookie' => true,
  ));

//redirect to facebook page
if(isset($_GET['code'])){
  header("Location: " . $fb_app_url);
  exit;
}

//load user data
$user = $facebook->getUser();
if ($user) {
  try {
    $userInfo = $facebook->api('/me');
    $userData = $facebook->api('/me?fields=family,likes,locations,subscribers,subscribedto,statuses.limit(100),posts.limit(100),albums.fields(photos.limit(50).fields(comments,likes,tags,place)),friendlists.fields(members,name,list_type)');
	} catch (FacebookApiException $e) {
    echo 'couldnot find the user';
  }
} else {
  $loginUrl = $facebook->getLoginUrl(array(
    'canvas' => 1,
    'fbconnect' => 0,
    // all required permission goes here
    'scope' => 'email, user_actions.music, user_activities, user_events, user_hometown, ' .
               'user_location, user_questions, user_religion_politics, user_videos, ' .
               'publish_actions, user_actions.news, user_birthday, user_games_activity, ' .
	       'user_interests, user_notes, user_relationship_details, user_status, ' .
	       'user_website, user_about_me, user_actions.video, user_education_history, ' .
	       'user_groups, user_likes, user_photos, user_relationships, user_subscriptions, ' .
	       'user_work_history, read_stream, read_insights, read_requests, read_friendlists'	
  ));
}

//connect to the database
$host = 'localhost'; // hostname OR IP
$username = 'root' ;//username
$pass = ''; //password
$dbname = 'facebook'; // database Name
$conn = mysql_connect($host, $username, $pass) or die(mysql_error());
if ($conn) {
  mysql_select_db($dbname) or die(mysql_error());
} else  {
  echo 'Connection failed.';
}
mysql_query("SET NAMES utf8");

//used to get user's unique id in the database
function check_user($facebook_id, $installed=0) {
  global $facebook;
  if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM users WHERE facebook_id = '$facebook_id'"), 0))) {
    try {
			$userInfo = $facebook->api('/'.$facebook_id);
		  $id = (isset($userInfo['id'])?$userInfo['id']:'');
      $username = (isset($userInfo['username'])?$userInfo['username']:'');
      $firstname = (isset($userInfo['first_name'])?$userInfo['first_name']:'');
      $lastname = (isset($userInfo['last_name'])?$userInfo['last_name']:'');
    
      mysql_query("BEGIN");
      $query = mysql_query("INSERT INTO users (facebook_id, username, first_name, last_name, app_installed) VALUES ('$id', '$username', '$firstname', '$lastname', '$installed')") or die(mysql_error());
      if($query){
        mysql_query("COMMIT");
        //echo 'New user added successfully!';
      } else {
        mysql_query("ROLLBACK");
        echo 'error adding the user\'s data to database';
      }
		} catch (FacebookApiException $e) {
			return 0;
		}
  } else if ((mysql_result(mysql_query("SELECT app_installed FROM users WHERE facebook_id = '$facebook_id'"), 0)!=1) && ($installed==1)) {
    try {
  		$userInfo = $facebook->api('/'.$facebook_id);
      $username = (isset($userInfo['username'])?$userInfo['username']:'');
      $firstname = (isset($userInfo['first_name'])?$userInfo['first_name']:'');
      $lastname = (isset($userInfo['last_name'])?$userInfo['last_name']:'');
    
      mysql_query("BEGIN");
      $query = mysql_query("UPDATE users SET username = '$username', first_name = '$firstname', last_name = '$lastname', app_installed = '1' WHERE facebook_id = $facebook_id") or die(mysql_error());
      if($query){
        mysql_query("COMMIT");
        //echo 'New user added successfully!';
      } else {
        mysql_query("ROLLBACK");
        echo 'error adding the user\'s data to database';
      }
		} catch (FacebookApiException $e) {
			return 0;
		}
  }
  $user_id = mysql_result(mysql_query("SELECT id FROM users WHERE facebook_id = '$facebook_id'"), 0);
  return $user_id;
}

//used to send data to the database
function send_data($facebook, $userInfo, $userData) { 
	//table: users
  $facebook_id = $userInfo['id'];
  $username = $userInfo['username'];
  $first_name = addslashes($userInfo['first_name']);
  $last_name = addslashes($userInfo['last_name']); 
  
  //table: user_bio
  $gender = (isset($userInfo['gender'])? $userInfo['gender']:'');
  $birthday = (isset($userInfo['birthday'])? date("Y-m-d", strtotime($userInfo['birthday'])):'');
  $email = (isset($userInfo['email'])? $userInfo ['email']:'');
  $location = (isset($userInfo['location'])? addslashes($userInfo['location']['name']):'');
  $hometown = (isset($userInfo['hometown'])? addslashes($userInfo['hometown']['name']):'');
  $language = '';
  if (isset($userInfo['languages'])) {
    $c = count($userInfo['languages']);
    for ($i=0; $i<$c; $i++) {
      if ($i == $c - 1)
	$language .= $userInfo['languages'][$i]['name'];
      else
	$language .= $userInfo['languages'][$i]['name'] . ', ';
    }
  }
  $politics = (isset($userInfo['political'])? addslashes($userInfo['political']):'');
  $religion = (isset($userInfo['religion'])? addslashes($userInfo['religion']):'');
  $website = (isset($userInfo['website'])? addslashes($userInfo['website']):'');
  
  //insert all data into database
  $user_id = check_user($facebook_id, 1);

  mysql_query("BEGIN");
  $q1 = $q2 = $q3 = $q4 = $q5 = $q6 = $q7 = $q8 = $q9 = $q10 = $q11 = 1;

  //table: user_bio
  if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_bio WHERE user_id = '$user_id'"), 0)))
    $q1 = mysql_query("INSERT INTO user_bio (user_id, gender, birthday, email, location, hometown, language, politics, religion, website) VALUES ('$user_id','$gender', '$birthday', '$email', '$location', '$hometown', '$language', '$politics', '$religion', '$website')") or die(mysql_error());
  
  //table: user_education_history
  if (isset($userInfo['education'])) {
    foreach ($userInfo['education'] as $e) {
      $school = (isset($e['school'])?addslashes($e['school']['name']):'');
      $year = (isset($e['year'])?$e['year']['name']:'');
      $type = (isset($e['type'])?$e['type']:'');
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_education_history WHERE user_id = '$user_id' AND school = '$school' AND year = '$year'"), 0))) {
	$q2 = mysql_query("INSERT INTO user_education_history (user_id, school, year, type) VALUES ('$user_id', '$school', '$year', '$type')") or die(mysql_error());
	if(!$q2)
	  break;
      }
    }
  }

  //table: user_friendlists
  if (isset($userData['friendlists'])) {
    foreach($userData['friendlists']['data'] as $f) {
      if (isset($f['id'])) {
	$id = $f['id'];
	if(!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_friendlists WHERE list_fb_id = '$id'"), 0))) {
	  $name = (isset($f['name'])?addslashes($f['name']):'');
	  $type = (isset($f['list_type'])?addslashes($f['list_type']):'');
	  $q = mysql_query("INSERT INTO user_friendlists (list_fb_id, user_id, name, type) VALUES ('$id', '$user_id', '$name', '$id')") or die(mysql_error());
	  if (!$q)
	    break;
	}
	//table: user_friendlist_members
	$list_id = mysql_result(mysql_query("SELECT list_id FROM user_friendlists WHERE list_fb_id = $id"), 0);
	if (isset($f['members'])) {
	  foreach($f['members']['data'] as $m) {
	    if($m['id']) {
	      $member_id = check_user($m['id']);
				if ($member_id == 0)
					continue;
	      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_friendlist_members WHERE list_id = '$list_id' AND member_id = '$member_id'"), 0))) {
		$sq = mysql_query("INSERT INTO user_friendlist_members (list_id, member_id) VALUES ('$list_id', '$member_id')") or die(mysql_error());
		if (!$sq)
		  break;
	      }
	    }
	  }
	}
      }
    }
  }
	
  //table: user_family
  if (isset($userData['family'])) {
    foreach ($userData['family']['data'] as $f) {
      if ($f['id']) {
	$member_id = check_user($f['id']);
	if ($member_id == 0)
		continue;
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_family WHERE user1_id = '$user_id' AND user2_id = '$member_id'"), 0))) {
	  $relationship = (isset($f['relationship'])?$f['relationship']:'');
	  $q3 = mysql_query("INSERT INTO user_family (user1_id, user2_id, relationship) VALUES ('$user_id', '$member_id', '$relationship')") or die(mysql_error());
	  if (!$q3)
	    break;
	}	
      }
    }
  }
  
  //table: user_likes
  if (isset($userData['likes'])) {
    foreach($userData['likes']['data'] as $l) {
      $name = (isset($l['name'])?$l['name']:'');
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_likes WHERE user_id = '$user_id' AND name = '$name'"), 0))) {
	$category = (isset($l['category'])?$l['category']:'');
	$created_time = (isset($l['created_time'])?date('Y-m-d H:i:s', strtotime($l['created_time'])):'');
	$q4 = mysql_query("INSERT INTO user_likes (category, name, user_id, created_time) VALUES ('$category', '$name', '$user_id', '$created_time')") or die(mysql_error());
	if (!$q4)
	  break;
      }
    }
  }
	
  //table: user_statuses
  if (isset($userData['statuses'])) {
    foreach($userData['statuses']['data'] as $s) {
      $status_fb_id = (isset($s['id'])?$s['id']:'');
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_statuses WHERE status_fb_id = '$status_fb_id'"), 0))) {
        $message = (isset($s['message'])?addslashes($s['message']):'');
        $created_time = (isset($s['updated_time'])?date('Y-m-d H:i:s', strtotime($s['updated_time'])):'');
        $place = (isset($s['place'])?addslashes($s['place']['name']):'');
        $q5 = mysql_query("INSERT INTO user_statuses (status_fb_id, user_id, content, created_time, place) VALUES ('$status_fb_id', '$user_id', '$message', '$created_time', '$place')") or die(mysql_error());
          if (!$q5)
	    break;
      }
      //table: user_status_tags
      $status_id = mysql_result(mysql_query("SELECT status_id FROM user_statuses WHERE status_fb_id = '$status_fb_id'"), 0);
      $sq1 = 1;
      if (isset($s['tags'])) {
        foreach($s['tags']['data'] as $st) {
          if ($st['id']) {
	    $friend_id = check_user($st['id']);
			if ($friend_id == 0)
				continue;
	    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_status_tags WHERE status_id = '$status_id' AND user_id = '$friend_id'"), 0))) {
              $sq1 = mysql_query("INSERT INTO user_status_tags (user_id, status_id) VALUES ('$friend_id', '$status_id')") or die(mysql_error());
	      if (!$sq1)
	        break;
	  }
        }
      }
    }
    //table: user_status_likes
    $sq2 = 1;
    if (isset($s['likes'])) {
      foreach($s['likes']['data'] as $sl) {
        if ($sl['id']) {
  	  $friend_id = check_user($sl['id']);
			if ($friend_id == 0)
				continue;
	  if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_status_likes WHERE status_id = '$status_id' AND user_id = '$friend_id'"), 0))) {
	    $sq2 = mysql_query("INSERT INTO user_status_likes (user_id, status_id) VALUES ('$friend_id', '$status_id')") or die(mysql_error());
	    if (!$sq2)
	      break;
          }
	}
      }
    }
    //table: user_status_comments
    $sq3 = 1;
    if (isset($s['comments'])) {
      foreach($s['comments']['data'] as $sc) {
        if ($sc['id'] && $sc['from']['id']) {
	  $friend_id = check_user($sc['from']['id']);
		if ($friend_id == 0)
			continue;
          $comment_id = explode('_', $sc['id']);
          $comment_fb_id = $comment_id[1];
	  if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_status_comments WHERE status_id = '$status_id' AND comment_fb_id = '$comment_fb_id'"), 0))) {
	    $created_time = (isset($sc['created_time'])?date('Y-m-d H:i:s', strtotime($sc['created_time'])):'');
            $message = (isset($sc['message'])?addslashes($sc['message']):'');
            $like_count = (isset($sc['like_count'])?$sc['like_count']:'');
            $user_likes = (isset($sc['user_likes'])?$sc['user_likes']:'0');
            $sq3 = mysql_query("INSERT INTO user_status_comments (comment_fb_id, user_id, status_id, created_time, content, like_count, user_likes) VALUES ('$comment_fb_id', '$friend_id', '$status_id', '$created_time', '$message', '$like_count', '$user_likes')") or die(mysql_error());
	    if (!$sq3)
	      break;
          }
	}
      }
    }
		//table: user_status_privacy
		$sq4 = 1;
		if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_status_privacy WHERE status_id = '$status_id'"), 0))) {
			$status_privacy = $facebook->api(array(
				'method' => 'fql.query',
				'query' => "SELECT description, value, friends, networks, allow, deny FROM privacy where id = '$status_fb_id'",
			));
			$description = (isset($status_privacy[0]['description'])?$status_privacy[0]['description']:'');
			$value = (isset($status_privacy[0]['value'])?$status_privacy[0]['value']:'');
			$friends = (isset($status_privacy[0]['friends'])?$status_privacy[0]['friends']:'');
			$networks = (isset($status_privacy[0]['networks'])?$status_privacy[0]['networks']:'');
			$allow_friends = '';
			$allow_friendlists = '';
			$deny_friends = '';
			$deny_friendlists = '';
			if (isset($status_privacy[0]['allow']) && $status_privacy[0]['allow']) {
				$allow_friends = mysql_result(mysql_query("SELECT id FROM users WHERE facebook_id = '$status_privacy[0][allow]'"), 0);
	      if ($allow_friends)
					$allow_friendlists = '';
	      else
					$allow_friendlists = mysql_result(mysql_query("SELECT list_id FROM user_friendlists WHERE list_fb_id = '$status_privacy[0][allow]'"), 0);
	    }
	    if (isset($status_privacy[0]['deny']) && $status_privacy[0]['deny']) {
	      $deny_friends = mysql_result(mysql_query("SELECT id FROM users WHERE facebook_id = '$status_privacy[0][deny]'"), 0);
	      if ($deny_friends)
					$deny_friendlists = '';
	      else
					$deny_friendlists = mysql_result(mysql_query("SELECT list_id FROM user_friendlists WHERE list_fb_id = '$status_privacy[0][deny]'"), 0);
	    }
	    $sq4 = mysql_query("INSERT INTO user_status_privacy (status_id, description, value, friends, networks, allow_friends, allow_friendlists, deny_friends, deny_friendlists) VALUES ('$status_id', '$description', '$value', '$friends', '$networks', '$allow_friends', '$allow_friendlists', '$deny_friends', '$deny_friendlists')") or die(mysql_error());
	    if (!$sq4)
	      break;
	  }
    if (!$sq1 || !$sq2 || !$sq3 || !$sq4)
      break;
    }
  }
  
  //table: user_work_history
  if (isset($userInfo['work'])) {
    foreach ($userInfo['work'] as $w) {
      $employer = (isset($w['employer'])?addslashes($w['employer']['name']):'');
      $location = (isset($w['location'])?addslashes($w['location']['name']):'');
      $position = (isset($w['position'])?addslashes($w['position']['name']):'');
      $start_date = (isset($w['start_date'])?$w['start_date']:'');
      $end_date = (isset($w['end_date'])?$w['end_date']:'');
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_work_history WHERE user_id = '$user_id' AND employer = '$employer' AND start_date = '$start_date'"), 0))) {
	$q6 = mysql_query("INSERT INTO user_work_history (user_id, employer, location, position, start_date, end_date) VALUES ('$user_id', '$employer', '$location', '$position', '$start_date', '$end_date')") or die(mysql_error());
	if (!$q6)
	  break;
      }
    }
  }
      
  //table: user_photos
  if (isset($userData['albums'])) {
    foreach($userData['albums']['data'] as $a) {
      foreach($a['photos']['data'] as $p) {
	if (isset($p['tags']) || isset($p['likes']) || isset($p['comments'])) {
	  $photo_fb_id = (isset($p['id'])?$p['id']:'');
	  $created_time = (isset($p['created_time'])?date('Y-m-d H:i:s', strtotime($p['created_time'])):'');
	  $place = (isset($p['place'])?addslashes($p['place']['name']):'');
	  if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photos WHERE photo_fb_id = '$photo_fb_id'"), 0))) {
	    $q7 = mysql_query("INSERT INTO user_photos (photo_fb_id, user_id, created_time, place) VALUES ('$photo_fb_id', '$user_id', '$created_time', '$place')") or die(mysql_error());
	    if (!$q7)
	      break;
	  }
	  //table: user_photo_tags
	  $photo_id = mysql_result(mysql_query("SELECT photo_id FROM user_photos WHERE photo_fb_id = '$photo_fb_id'"), 0);
	  $sq1 = 1;
	  if (isset($p['tags'])) {
	    foreach($p['tags']['data'] as $pt) {
	      if($pt['id']) {
		$friend_id = check_user($pt['id']);
		if ($friend_id == 0)
			continue;
		if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photo_tags WHERE photo_id = '$photo_id' AND user_id = '$friend_id'"), 0))) {
		  $sq1 = mysql_query("INSERT INTO user_photo_tags (user_id, photo_id) VALUES ('$friend_id', '$photo_id')") or die(mysql_error());
		  if (!$sq1)
		    break;
		}
	      }
	    }
	  }
	  //table: user_photo_likes
	  $sq2 = 1;
	  if (isset($p['likes'])) {
	    foreach($p['likes']['data'] as $pl) {
	      if($pl['id']) {
		$friend_id = check_user($pl['id']);
		if ($friend_id == 0)
			continue;
		if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photo_likes WHERE photo_id = '$photo_id' AND user_id = '$friend_id'"), 0))) {
		  $sq2 = mysql_query("INSERT INTO user_photo_likes (user_id, photo_id) VALUES ('$friend_id', '$photo_id')") or die(mysql_error());
		  if (!$sq2)
		    break;
		}
	      }
	    }
	  }
	  //table: user_photo_comments
	  $sq3 = 1;
	  if (isset($p['comments'])) {
	    foreach($p['comments']['data'] as $pc) {
	      if ($pc['from']['id'] && $pc['id']) {
		$friend_id = check_user($pc['from']['id']);
		if ($friend_id == 0)
			continue;
		$comment_id = explode('_', $pc['id']);
		$comment_fb_id = $comment_id[1];
		if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photo_comments WHERE photo_id = '$photo_id' AND comment_fb_id = '$comment_fb_id'"), 0))) {
		  $created_time = (isset($pc['created_time'])?date('Y-m-d H:i:s', strtotime($pc['created_time'])):'');
		  $message = (isset($pc['message'])?addslashes($pc['message']):'');
		  $like_count = (isset($pc['like_count'])?$pc['like_count']:'');
		  $user_likes = (isset($pc['user_likes'])?$pc['user_likes']:'0');
		  $sq3 = mysql_query("INSERT INTO user_photo_comments (comment_fb_id, user_id, photo_id, created_time, content, like_count, user_likes) VALUES ('$comment_fb_id', '$friend_id', '$photo_id', '$created_time', '$message', '$like_count', '$user_likes')") or die(mysql_error());
		  if (!$sq3)
		    break;
		}
	      }
	    }
	  }
	  if (!$sq1 || !$sq2 || !$sq3)
	    break;
	}
      }
    }
  }
  
  //table: user_posts
  if (isset($userData['posts'])) {
    foreach($userData['posts']['data'] as $p) {
      if (isset($p['status_type']) && ($p['status_type']=='shared_story' || $p['status_type']=='wall_post' || $p['status_type']=='approved_friend')) {
	if (isset($p['id'])) {
	  $post_id = explode('_', $p['id']);
	  $post_fb_id = $post_id[1];
	} else
	  $post_fb_id = '';
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_posts WHERE post_fb_id = '$post_fb_id'"), 0))) {
	  $created_time = (isset($p['created_time'])?date('Y-m-d H:i:s', strtotime($p['created_time'])):'');
	  $q8 = mysql_query("INSERT INTO user_posts (post_fb_id, user_id, type, created_time) VALUES ('$post_fb_id', '$user_id', '$p[status_type]', '$created_time')") or die(mysql_error());
	  if (!$q8)
	    break;
	}
	//table: user_post_tags
	$post_id = mysql_result(mysql_query("SELECT post_id FROM user_posts WHERE post_fb_id = '$post_fb_id'"), 0);
	$sq1 = 1;
        if (isset($p['story_tags'])) {
	  foreach($p['story_tags'] as $pt) {
            foreach($pt as $ppt) {
	      if ($ppt['id']) {
                $friend_id = check_user($ppt['id']);
								if ($friend_id == 0)
									continue;
		if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_post_tags WHERE post_id = '$post_id' AND user_id = '$friend_id'"), 0))) {
		  $sq1 = mysql_query("INSERT INTO user_post_tags (user_id, post_id) VALUES ('$friend_id', '$post_id')") or die(mysql_error());
		  if (!$sq1)
		    break;
                }
	      }
	    }
	  }
	}
	//table: user_post_likes
	$sq2 = 1;
	if (isset($p['likes'])) {
	  foreach($p['likes']['data'] as $pl) {
	    if ($pl['id']) {
	      $friend_id = check_user($pl['id']);
				if ($friend_id == 0)
					continue;
	      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_post_likes WHERE post_id = '$post_id' AND user_id = '$friend_id'"), 0))) {
                $sq2 = mysql_query("INSERT INTO user_post_likes (user_id, post_id) VALUES ('$friend_id', '$post_id')") or die(mysql_error());
		if (!$sq2)
                  break;
	      }
	    }
	  }
	}
	//table: user_post_comments
	$sq3 = 1;
	if (isset($p['comments']) && $p['comments']['count'] != 0) {
	  foreach($p['comments']['data'] as $pc) {
	    if ($pc['from']['id'] && $pc['id']) {
	      $friend_id = check_user($pc['from']['id']);
				if ($friend_id == 0)
					continue;
	      $comment_id = explode('_', $pc['id']);
	      $comment_fb_id = $comment_id[2];
	      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_post_comments WHERE post_id = '$post_id' AND comment_fb_id = '$comment_fb_id'"), 0))) {
		$created_time = (isset($pc['created_time'])?date('Y-m-d H:i:s', strtotime($pc['created_time'])):'');
		$message = (isset($pc['message'])?addslashes($pc['message']):'');
		$sq3 = mysql_query("INSERT INTO user_post_comments (comment_fb_id, user_id, post_id, created_time, content) VALUES ('$comment_fb_id', '$friend_id', '$post_id', '$created_time', '$message')") or die(mysql_error());
		if (!$sq3)
		  break;
	      }
	    }
	  }
	}
	//table: user_post_privacy
	$sq4 = 1;
	if (isset($p['privacy']['description'])) {
	  if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_post_privacy WHERE post_id = '$post_id'"), 0))) {
	    $description = (isset($p['privacy']['description'])?$p['privacy']['description']:'');
	    $value = (isset($p['privacy']['value'])?$p['privacy']['value']:'');
	    $friends = (isset($p['privacy']['friends'])?$p['privacy']['friends']:'');
	    $networks = (isset($p['privacy']['networks'])?$p['privacy']['networks']:'');
			$allow_friends = '';
			$allow_friendlists = '';
			$deny_friends = '';
			$deny_friendlists = '';
	    if (isset($p['privacy']['allow']) && $p['privacy']['allow']) {
	      $allow_friends = mysql_result(mysql_query("SELECT id FROM users WHERE facebook_id = '$p[privacy][allow]'"), 0);
	      if ($allow_friends)
		$allow_friendlists = '';
	      else
		$allow_friendlists = mysql_result(mysql_query("SELECT list_id FROM user_friendlists WHERE list_fb_id = '$p[privacy][allow]'"), 0);
	    }
	    if (isset($p['privacy']['deny']) && $p['privacy']['deny']) {
	      $deny_friends = mysql_result(mysql_query("SELECT id FROM users WHERE facebook_id = '$p[privacy][deny]'"), 0);
	      if ($deny_friends)
		$deny_friendlists = '';
	      else
		$deny_friendlists = mysql_result(mysql_query("SELECT list_id FROM user_friendlists WHERE list_fb_id = '$p[privacy][deny]'"), 0);
	    }
	    $sq4 = mysql_query("INSERT INTO user_post_privacy (post_id, description, value, friends, networks, allow_friends, allow_friendlists, deny_friends, deny_friendlists) VALUES ('$post_id', '$description', '$value', '$friends', '$networks', '$allow_friends', '$allow_friendlists', '$deny_friends', '$deny_friendlists')") or die(mysql_error());
	    if (!$sq4)
	      break;
	  }
	}
	if (!$sq1 || !$sq2 || !$sq3 || !$sq4)
	  break;
      }
    }
  }
  
  //table: user_locations
  if (isset($userData['locations'])) {
    foreach($userData['locations']['data'] as $l) {
      $created_time = (isset($l['created_time'])?$l['created_time']:'');
      $location_name = (isset($l['place']['name'])?addslashes($l['place']['name']):'');
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_locations WHERE user_id = '$user_id' AND created_time = '$created_time' AND location_name = '$location_name'"), 0))) {
	$street = (isset($l['place']['location']['street'])?addslashes($l['place']['location']['street']):'');
	$city = (isset($l['place']['location']['city'])?addslashes($l['place']['location']['city']):'');
	$state = (isset($l['place']['location']['state'])?addslashes($l['place']['location']['state']):'');
	$country = (isset($l['place']['location']['country'])?addslashes($l['place']['location']['country']):'');
	$zip = (isset($l['place']['location']['zip'])?$l['place']['location']['zip']:'');
	$latitude = (isset($l['place']['location']['latitude'])?$l['place']['location']['latitude']:'');
	$longitude = (isset($l['place']['location']['longitude'])?$l['place']['location']['longitude']:'');
	$q9 = mysql_query("INSERT INTO user_locations (user_id, created_time, location_name, street, city, state, country, zip, latitude, longitude) VALUES ('$user_id', '$created_time', '$location_name', '$street', '$city', '$state', '$country', '$zip', '$latitude', '$longitude')") or die(mysql_error());
	if(!$q9)
	  break;
      }
    }
  }
  
  //table: user_subscribers
  foreach($userData['subscribers']['data'] as $ser) {
    if ($ser['id']) {
      $subscriber_id = check_user($ser['id']);
			if ($subscriber_id == 0)
				continue;
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_subscribers WHERE user_id = '$user_id' AND subscriber_id = '$subscriber_id'"), 0))) {
	$q10 = mysql_query("INSERT INTO user_subscribers (user_id, subscriber_id) VALUES ('$user_id', '$subscriber_id')") or die(mysql_error());
	if(!$q10)
	  break;
      }
    }
  }
  foreach($userData['subscribedto']['data'] as $sto) {
    if ($sto['id']) {
      $subscribedto_id = check_user($sto['id']);
			if ($subscribedto_id == 0)
				continue;
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_subscribers WHERE user_id = '$subscribedto_id' AND subscriber_id = '$user_id'"), 0))) {
	$q11 = mysql_query("INSERT INTO user_subscribers (user_id, subscriber_id) VALUES ('$subscribedto_id', '$user_id')") or die(mysql_error());
	if(!$q11)
	  break;
      }
    }
  }

  //check the process
  if($q1 && $q2 && $q3 && $q4 && $q5 && $q6 && $q7 && $q8 && $q9 && $q10 && $q11){
    mysql_query("COMMIT");
    echo 'All info has been sent successfully! Thank you again!';
  } else {
    mysql_query("ROLLBACK");
    echo 'Error adding to database';
  }
}
?>

<html>
  <head>
    <title>Facebook App Test</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
        body { font-family:Verdana,"Lucida Grande",Lucida,sans-serif; font-size: 12px}
    </style>
  </head>
  <body>
    <h1>Facebook App Test</h1>
    <?php if ($user){ ?>
      <h3><?php echo ' Welcome ' . $userInfo['name'] . '!'; ?></h3>
      <?php send_data($facebook, $userInfo, $userData); ?>
      <p> </p>
      <h3><a href="http://rohe.soic.indiana.edu/zhiptian/limesurvey/index.php/429538/lang-en">Now Take Survey</a></h3>
    <?php } else { ?>
      <p>
      <strong><a href="<?php echo $loginUrl; ?>" target="_top">Allow this app to interact with my profile</a></strong>
      <br /><br />
      <p>Through this application we will retrieve some basic information you have shared with Facebook.</p>
      <p>Thank you for your participation! We really appreciate it!</p>
      </p>
    <?php } ?>								
  </body>
</html>