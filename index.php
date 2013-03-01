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
    $userData = $facebook->api('/me?fields=family,likes,locations,subscribers,subscribedto,statuses.limit(100),posts.limit(100),albums.fields(photos.limit(50).fields(comments,likes,tags,place))');
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
$pass = 'rh+he=my+sql' ; //password
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
  } else if ((mysql_result(mysql_query("SELECT app_installed FROM users WHERE facebook_id = $facebook_id"), 0)!=1) && ($installed==1)) {
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
  }
  $user_id = mysql_result(mysql_query("SELECT id FROM users WHERE facebook_id = $facebook_id"), 0);
  return $user_id;
}

//used to send data to the database
function send_data($userInfo, $userData) { 
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
  
  //table: user_education_history
  $education = array();
  if (isset($userInfo['education'])) {
    for($i=0; $i<count($userInfo['education']); $i++) {
      $education[$i] = array();
      $education[$i]['school'] = (isset($userInfo['education'][$i]['school'])?addslashes($userInfo['education'][$i]['school']['name']):'');
      $education[$i]['year'] = (isset($userInfo['education'][$i]['year'])?$userInfo['education'][$i]['year']['name']:'');
      $education[$i]['type'] = (isset($userInfo['education'][$i]['type'])?$userInfo['education'][$i]['type']:'');
    }
  }
  
  //table: user_family
  $family = array();
  if (isset($userData['family'])) {
    for($i=0; $i<count($userData['family']['data']); $i++) {
      $family[$i] = array();
      $family[$i]['name'] = (isset($userData['family']['data'][$i]['name'])?addslashes($userData['family']['data'][$i]['name']):'');
      $family[$i]['fb_id'] = (isset($userData['family']['data'][$i]['id'])?$userData['family']['data'][$i]['id']:'');
      $family[$i]['relationship'] = (isset($userData['family']['data'][$i]['relationship'])?$userData['family']['data'][$i]['relationship']:'');
    }
  }
  
  //table: user_likes
  $likes = array();
  if (isset($userData['likes'])) {
    for($i=0; $i<count($userData['likes']['data']); $i++) {
      $likes[$i] = array();
      $likes[$i]['category'] = (isset($userData['likes']['data'][$i]['category'])?addslashes($userData['likes']['data'][$i]['category']):'');
      $likes[$i]['name'] = (isset($userData['likes']['data'][$i]['name'])?addslashes($userData['likes']['data'][$i]['name']):'');
      $likes[$i]['created_time'] = (isset($userData['likes']['data'][$i]['created_time'])?date('Y-m-d H:i:s', strtotime($userData['likes']['data'][$i]['created_time'])):'');
    }
  }
  
  //table: user_statuses
  $statuses = array();
  if (isset($userData['statuses'])) {
    for($i=0; $i<count($userData['statuses']['data']); $i++) {
      $statuses[$i] = array();
      $statuses[$i]['status_fb_id'] = (isset($userData['statuses']['data'][$i]['id'])?$userData['statuses']['data'][$i]['id']:'');
      $statuses[$i]['message'] = (isset($userData['statuses']['data'][$i]['message'])?addslashes($userData['statuses']['data'][$i]['message']):'');
      $statuses[$i]['updated_time'] = (isset($userData['statuses']['data'][$i]['updated_time'])?date('Y-m-d H:i:s', strtotime($userData['statuses']['data'][$i]['updated_time'])):'');
      $statuses[$i]['place'] = (isset($userData['statuses']['data'][$i]['place'])?addslashes($userData['statuses']['data'][$i]['place']['name']):'');
      
      //table: user_status_tags
      $statuses[$i]['tags'] = array();
      if (isset($userData['statuses']['data'][$i]['tags'])) {
	for($x=0; $x<count($userData['statuses']['data'][$i]['tags']['data']); $x++) {
	  $statuses[$i]['tags'][$x] = array();
	  $statuses[$i]['tags'][$x]['id'] = (isset($userData['statuses']['data'][$i]['tags']['data'][$x]['id'])?$userData['statuses']['data'][$i]['tags']['data'][$x]['id']:'');
	  $statuses[$i]['tags'][$x]['name'] = (isset($userData['statuses']['data'][$i]['tags']['data'][$x]['name'])?addslashes($userData['statuses']['data'][$i]['tags']['data'][$x]['name']):'');
        }
      }
      
      //table: user_status_likes
      $statuses[$i]['likes'] = array();
      if (isset($userData['statuses']['data'][$i]['likes'])) {
	for($y=0; $y<count($userData['statuses']['data'][$i]['likes']['data']); $y++) {
	  $statuses[$i]['likes'][$y] = array();
	  $statuses[$i]['likes'][$y]['id'] = (isset($userData['statuses']['data'][$i]['likes']['data'][$y]['id'])?$userData['statuses']['data'][$i]['likes']['data'][$y]['id']:'');
	  $statuses[$i]['likes'][$y]['name'] = (isset($userData['statuses']['data'][$i]['likes']['data'][$y]['name'])?addslashes($userData['statuses']['data'][$i]['likes']['data'][$y]['name']):'');
	}
      }
      
      //table: user_status_comments
      $statuses[$i]['comments'] = array();
      if (isset($userData['statuses']['data'][$i]['comments'])) {
	for($z=0; $z<count($userData['statuses']['data'][$i]['comments']['data']); $z++) {
	  $statuses[$i]['comments'][$z] = array();
	  if (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['id'])) {
	    $comment_id = explode('_', $userData['statuses']['data'][$i]['comments']['data'][$z]['id']);
	    $statuses[$i]['comments'][$z]['comment_fb_id'] = $comment_id[1];
	  }
	  $statuses[$i]['comments'][$z]['id'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['from'])?$userData['statuses']['data'][$i]['comments']['data'][$z]['from']['id']:'');
	  $statuses[$i]['comments'][$z]['name'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['from'])?addslashes($userData['statuses']['data'][$i]['comments']['data'][$z]['from']['name']):'');
	  $statuses[$i]['comments'][$z]['message'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['message'])?addslashes($userData['statuses']['data'][$i]['comments']['data'][$z]['message']):'');
	  $statuses[$i]['comments'][$z]['created_time'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['created_time'])?date('Y-m-d H:i:s', strtotime($userData['statuses']['data'][$i]['comments']['data'][$z]['created_time'])):'');
	  $statuses[$i]['comments'][$z]['like_count'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['like_count'])?$userData['statuses']['data'][$i]['comments']['data'][$z]['like_count']:'');
	  $statuses[$i]['comments'][$z]['user_likes'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['user_likes'])?$userData['statuses']['data'][$i]['comments']['data'][$z]['user_likes']:'0');
	}
      }	
    }
  }
  
  //table: user_work_history
  $work = array();
  if (isset($userInfo['work'])) {
    for($i=0; $i<count($userInfo['work']); $i++) {
      $work[$i] = array();
      $work[$i]['employer'] = (isset($userInfo['work'][$i]['employer'])?addslashes($userInfo['work'][$i]['employer']['name']):'');
      $work[$i]['location'] = (isset($userInfo['work'][$i]['location'])?addslashes($userInfo['work'][$i]['location']['name']):'');
      $work[$i]['position'] = (isset($userInfo['work'][$i]['position'])?addslashes($userInfo['work'][$i]['position']['name']):'');
      $work[$i]['start_date'] = (isset($userInfo['work'][$i]['start_date'])?$userInfo['work'][$i]['start_date']:'');
      $work[$i]['end_date'] = (isset($userInfo['work'][$i]['end_date'])?$userInfo['work'][$i]['end_date']:'');
    }
  }
  
  //table: user_photos
  foreach ($userData['albums']['data'] as $a) {
    foreach ($a['photos']['data'] as $p) {
      if (isset($p['tags']) || isset($p['likes']) || isset($p['comments']))
	$userData['photos'][] = $p;
    }
  }

  $photos = array();
  if (isset($userData['photos'])) {
    for($i=0; $i<count($userData['photos']); $i++) {
      $photos[$i] = array();
      $photos[$i]['photo_fb_id'] = (isset($userData['photos'][$i]['id'])?$userData['photos'][$i]['id']:'');
      $photos[$i]['created_time'] = (isset($userData['photos'][$i]['created_time'])?date('Y-m-d H:i:s', strtotime($userData['photos'][$i]['created_time'])):'');
      $photos[$i]['place'] = (isset($userData['photos'][$i]['place'])?addslashes($userData['photos'][$i]['place']['name']):'');
      
      //table: user_photo_tags
      $photos[$i]['tags'] = array();
      if (isset($userData['photos'][$i]['tags'])) {
	for($x=0; $x<count($userData['photos'][$i]['tags']['data']); $x++) {
	  $photos[$i]['tags'][$x] = array();
	  $photos[$i]['tags'][$x]['id'] = (isset($userData['photos'][$i]['tags']['data'][$x]['id'])?$userData['photos'][$i]['tags']['data'][$x]['id']:'');
	  $photos[$i]['tags'][$x]['name'] = (isset($userData['photos'][$i]['tags']['data'][$x]['name'])?addslashes($userData['photos'][$i]['tags']['data'][$x]['name']):'');
        }
      }

      //table: user_photo_likes
      $photos[$i]['likes'] = array();
      if (isset($userData['photos'][$i]['likes'])) {
	for($y=0; $y<count($userData['photos'][$i]['likes']['data']); $y++) {
	  $photos[$i]['likes'][$y] = array();
	  $photos[$i]['likes'][$y]['id'] = (isset($userData['photos'][$i]['likes']['data'][$y]['id'])?$userData['photos'][$i]['likes']['data'][$y]['id']:'');
	  $photos[$i]['likes'][$y]['name'] = (isset($userData['photos'][$i]['likes']['data'][$y]['name'])?addslashes($userData['photos'][$i]['likes']['data'][$y]['name']):'');
	}
      }
      
      //table: user_photo_comments
      $photos[$i]['comments'] = array();
      if (isset($userData['photos'][$i]['comments'])) {
	for($z=0; $z<count($userData['photos'][$i]['comments']['data']); $z++) {
	  $photos[$i]['comments'][$z] = array();
	  if (isset($userData['photos'][$i]['comments']['data'][$z]['id'])) {
	    $comment_id = explode('_', $userData['photos'][$i]['comments']['data'][$z]['id']);
	    $photos[$i]['comments'][$z]['comment_fb_id'] = $comment_id[1];
	  }
	  $photos[$i]['comments'][$z]['id'] = (isset($userData['photos'][$i]['comments']['data'][$z]['from'])?$userData['photos'][$i]['comments']['data'][$z]['from']['id']:'');
	  $photos[$i]['comments'][$z]['name'] = (isset($userData['photos'][$i]['comments']['data'][$z]['from'])?addslashes($userData['photos'][$i]['comments']['data'][$z]['from']['name']):'');
	  $photos[$i]['comments'][$z]['message'] = (isset($userData['photos'][$i]['comments']['data'][$z]['message'])?addslashes($userData['photos'][$i]['comments']['data'][$z]['message']):'');
	  $photos[$i]['comments'][$z]['created_time'] = (isset($userData['photos'][$i]['comments']['data'][$z]['created_time'])?date('Y-m-d H:i:s', strtotime($userData['photos'][$i]['comments']['data'][$z]['created_time'])):'');
	  $photos[$i]['comments'][$z]['like_count'] = (isset($userData['photos'][$i]['comments']['data'][$z]['like_count'])?$userData['photos'][$i]['comments']['data'][$z]['like_count']:'');
	  $photos[$i]['comments'][$z]['user_likes'] = (isset($userData['photos'][$i]['comments']['data'][$z]['user_likes'])?$userData['photos'][$i]['comments']['data'][$z]['user_likes']:'0');
	}
      }
    }
  }

  //table: user_posts
  $posts = array();
  if (isset($userData['posts'])) {
    foreach($userData['posts']['data'] as $p) {
      if ($p['status_type']=='shared_story' || $p['status_type']=='wall_post' || $p['status_type']=='approved_friend') {
	$i = count($posts);
	if (isset($p['id'])) {
	  $post_id = explode('_', $p['id']);
	  $posts[$i]['post_fb_id'] = $post_id[1];
	} else
	  $posts[$i]['post_fb_id'] = '';
	$posts[$i]['type'] = $p['status_type'];
	$posts[$i]['created_time'] = (isset($p['created_time'])?date('Y-m-d H:i:s', strtotime($p['created_time'])):'');
	
	//table: user_post_tags
	$posts[$i]['tags'] = array();
	if (isset($p['story_tags'])) {
	  foreach ($p['story_tags'] as $pt) {
	    $x = count($posts[$i]['tags']);
	    $posts[$i]['tags'][$x] = array();
	    $posts[$i]['tags'][$x]['id'] = (isset($pt[0]['id'])?$pt[0]['id']:'');
	    $posts[$i]['tags'][$x]['name'] = (isset($pt[0]['name'])?addslashes($pt[0]['name']):'');
	  }
	}
	
	//table: user_post_likes
	$posts[$i]['likes'] = array();
	if (isset($p['likes'])) {
	  for ($y=0; $y<count($p['likes']['data']); $y++) {
	    $posts[$i]['likes'][$y] = array();
	    $posts[$i]['likes'][$y]['id'] = (isset($p['likes']['data'][$y]['id'])?$p['likes']['data'][$y]['id']:'');
	    $posts[$i]['likes'][$y]['name'] = (isset($p['likes']['data'][$y]['id'])?addslashes($p['likes']['data'][$y]['name']):'');
	  }
	}
	
	//table: user_post_comments
	$posts[$i]['comments'] = array();
	if (isset($p['comments']) && ($pc['count'] != '0')) {
	  foreach ($p['comments']['data'] as $pc) {
	    $z = count($posts[$i]['comments']);
	    $posts[$i]['comments'][$z] = array();
	    $comment_id = explode('_', $pc['id']);
	    $posts[$i]['comments'][$z]['comment_fb_id'] = $comment_id[2];
	    $posts[$i]['comments'][$z]['id'] = (isset($pc['from']['id'])?$pc['from']['id']:'');
	    $posts[$i]['comments'][$z]['name'] = (isset($pc['from']['name'])?addslashes($pc['from']['name']):'');
	    $posts[$i]['comments'][$z]['message'] = (isset($pc['message'])?addslashes($pc['message']):'');
	    $posts[$i]['comments'][$z]['created_time'] = (isset($pc['created_time'])?date('Y-m-d H:i:s', strtotime($pc['created_time'])):'');
	  }
	}
      }
    }
  }

  //table: user_locations
  $locations = array();
  if (isset($userData['locations'])) {
    for($i=0; $i<count($userData['locations']['data']); $i++) {
      $locations[$i]['created_time'] = (isset($userData['locations']['data'][$i]['created_time'])?$userData['locations']['data'][$i]['created_time']:'');
      $locations[$i]['location_name'] = (isset($userData['locations']['data'][$i]['place']['name'])?addslashes($userData['locations']['data'][$i]['place']['name']):'');
      $locations[$i]['street'] = (isset($userData['locations']['data'][$i]['place']['location']['street'])?addslashes($userData['locations']['data'][$i]['place']['location']['street']):'');
      $locations[$i]['city'] = (isset($userData['locations']['data'][$i]['place']['location']['city'])?addslashes($userData['locations']['data'][$i]['place']['location']['city']):'');
      $locations[$i]['state'] = (isset($userData['locations']['data'][$i]['place']['location']['state'])?addslashes($userData['locations']['data'][$i]['place']['location']['state']):'');
      $locations[$i]['country'] = (isset($userData['locations']['data'][$i]['place']['location']['country'])?addslashes($userData['locations']['data'][$i]['place']['location']['country']):'');
      $locations[$i]['zip'] = (isset($userData['locations']['data'][$i]['place']['location']['zip'])?$userData['locations']['data'][$i]['place']['location']['zip']:'');
      $locations[$i]['latitude'] = (isset($userData['locations']['data'][$i]['place']['location']['latitude'])?$userData['locations']['data'][$i]['place']['location']['latitude']:'');
      $locations[$i]['longitude'] = (isset($userData['locations']['data'][$i]['place']['location']['longitude'])?$userData['locations']['data'][$i]['place']['location']['longitude']:'');
    }
  }
  
  //insert all data into database
  $user_id = check_user($facebook_id, 1);
  mysql_query("BEGIN");
  $q1 = $q2 = $q3 = $q4 = $q5 = $q6 = $q7 = $q8 = $q9 = $q10 = $q11 = 1;
  if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_bio WHERE user_id = '$user_id'"), 0)))
    $q1 = mysql_query("INSERT INTO user_bio (user_id, gender, birthday, email, location, hometown, language, politics, religion, website) VALUES ('$user_id','$gender', '$birthday', '$email', '$location', '$hometown', '$language', '$politics', '$religion', '$website')") or die(mysql_error());
  foreach ($education as $e) {
    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_education_history WHERE user_id = '$user_id' AND school = '$e[school]' AND year = '$e[year]'"), 0))) {
      $q2 = mysql_query("INSERT INTO user_education_history (user_id, school, year, type) VALUES ('$user_id', '$e[school]', '$e[year]', '$e[type]')") or die(mysql_error());
      if(!$q2)
	break;
    }
  }
  foreach ($family as $f) {
    if ($f['fb_id']) {
      $member_id = check_user($f['fb_id']);
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_family WHERE user1_id = '$user_id' AND user2_id = '$member_id'"), 0))) {
	$q3 = mysql_query("INSERT INTO user_family (user1_id, user2_id, relationship) VALUES ('$user_id', '$member_id', '$f[relationship]')") or die(mysql_error());
	if (!$q3)
	break;
      }	
    }
  }
  foreach($likes as $l) {
    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_likes WHERE user_id = '$user_id' AND name = '$l[name]'"), 0))) {
      $q4 = mysql_query("INSERT INTO user_likes (category, name, user_id, created_time) VALUES ('$l[category]', '$l[name]', '$user_id', '$l[created_time]')") or die(mysql_error());
      if (!$q4)
	break;
    }
  }
  foreach($statuses as $s) {
    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_statuses WHERE status_fb_id = '$s[status_fb_id]'"), 0))) {
      $q5 = mysql_query("INSERT INTO user_statuses (status_fb_id, user_id, content, created_time, place) VALUES ('$s[status_fb_id]', '$user_id', '$s[message]', '$s[updated_time]', '$s[place]')") or die(mysql_error());
      if (!$q5) {
	echo "status broke!!!";
	break;
      }
    }
    $status_id = mysql_result(mysql_query("SELECT status_id FROM user_statuses WHERE status_fb_id = '$s[status_fb_id]'"), 0);
    $sq1 = 1;
    foreach($s['tags'] as $st) {
      if ($st['id']) {
	$friend_id = check_user($st['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_status_tags WHERE status_id = '$status_id' AND user_id = '$friend_id'"), 0))) {
	  $sq1 = mysql_query("INSERT INTO user_status_tags (user_id, status_id) VALUES ('$friend_id', '$status_id')") or die(mysql_error());
	  if (!$sq1)
	    break;
	}
      }
    }
    $sq2 = 1;
    foreach($s['likes'] as $sl) {
      if ($sl['id']) {
	$friend_id = check_user($sl['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_status_likes WHERE status_id = '$status_id' AND user_id = '$friend_id'"), 0))) {
	  $sq2 = mysql_query("INSERT INTO user_status_likes (user_id, status_id) VALUES ('$friend_id', '$status_id')") or die(mysql_error());
	  if (!$sq2)
	    break;
	}
      }
    }
    $sq3 = 1;
    foreach($s['comments'] as $sc) {
      if ($sc['id']) {
	$friend_id = check_user($sc['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_status_comments WHERE status_id = '$status_id' AND comment_fb_id = '$sc[comment_fb_id]'"), 0))) {
	  $sq3 = mysql_query("INSERT INTO user_status_comments (comment_fb_id, user_id, status_id, created_time, content, like_count, user_likes) VALUES ('$sc[comment_fb_id]', '$friend_id', '$status_id', '$sc[created_time]', '$sc[message]', '$sc[like_count]', '$sc[user_likes]')") or die(mysql_error());
	  if (!$sq3)
	    break;
	}
      }
    }
    if (!$sq1 || !$sq2 || !$sq3)
      break;
  }
  foreach($work as $w) {
    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_work_history WHERE user_id = '$user_id' AND employer = '$w[employer]' AND start_date = '$w[start_date]'"), 0))) {
      $q6 = mysql_query("INSERT INTO user_work_history (user_id, employer, location, position, start_date, end_date) VALUES ('$user_id', '$w[employer]', '$w[location]', '$w[position]', '$w[start_date]', '$w[end_date]')") or die(mysql_error());
      if (!$q6)
	break;
    }
  }
  foreach($photos as $p) {
    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photos WHERE photo_fb_id = '$p[photo_fb_id]'"), 0))) {
      $q7 = mysql_query("INSERT INTO user_photos (photo_fb_id, user_id, created_time, place) VALUES ('$p[photo_fb_id]', '$user_id', '$p[created_time]', '$p[place]')") or die(mysql_error());
      if (!$q7)
	break;
    }
    $photo_id = mysql_result(mysql_query("SELECT photo_id FROM user_photos WHERE photo_fb_id = '$p[photo_fb_id]'"), 0);
    $sq1 = 1;
    foreach($p['tags'] as $pt) {
      if($pt['id']) {
	$friend_id = check_user($pt['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photo_tags WHERE photo_id = '$photo_id' AND user_id = '$friend_id'"), 0))) {
	  $sq1 = mysql_query("INSERT INTO user_photo_tags (user_id, photo_id) VALUES ('$friend_id', '$photo_id')") or die(mysql_error());
	  if (!$sq1)
	    break;
	}
      }
    }
    $sq2 = 1;
    foreach($p['likes'] as $pl) {
      if($pl['id']) {
	$friend_id = check_user($pl['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photo_likes WHERE photo_id = '$photo_id' AND user_id = '$friend_id'"), 0))) {
	  $sq2 = mysql_query("INSERT INTO user_photo_likes (user_id, photo_id) VALUES ('$friend_id', '$photo_id')") or die(mysql_error());
	  if (!$sq2)
	    break;
	}
      }
    }
    $sq3 = 1;
    foreach($p['comments'] as $pc) {
      if ($pc['id']) {
	$friend_id = check_user($pc['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_photo_comments WHERE photo_id = '$photo_id' AND comment_fb_id = '$pc[comment_fb_id]'"), 0))) {
	  $sq3 = mysql_query("INSERT INTO user_photo_comments (comment_fb_id, user_id, photo_id, created_time, content, like_count, user_likes) VALUES ('$pc[comment_fb_id]', '$friend_id', '$photo_id', '$pc[created_time]', '$pc[message]', '$pc[like_count]', '$pc[user_likes]')") or die(mysql_error());
	  if (!$sq3)
	    break;
	}
      }
    }
    if (!$sq1 || !$sq2 || !$sq3)
      break;
  }
  foreach($posts as $po) {
    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_posts WHERE post_fb_id = '$po[post_fb_id]'"), 0))) {
      $q8 = mysql_query("INSERT INTO user_posts (post_fb_id, user_id, type, created_time) VALUES ('$po[post_fb_id]', '$user_id', '$po[type]', '$po[created_time]')") or die(mysql_error());
      if (!$q8)
	break;
    }
    $post_id = mysql_result(mysql_query("SELECT post_id FROM user_posts WHERE post_fb_id = '$po[post_fb_id]'"), 0);
    $sq1 = 1;
    foreach($po['tags'] as $pot) {
      if ($pot['id']) {
	$friend_id = check_user($pot['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_post_tags WHERE post_id = '$post_id' AND user_id = '$friend_id'"), 0))) {
	  $sq1 = mysql_query("INSERT INTO user_post_tags (user_id, post_id) VALUES ('$friend_id', '$post_id')") or die(mysql_error());
	  if (!$sq1)
	    break;
	}
      }
    }
    $sq2 = 1;
    foreach($po['likes'] as $pol) {
      if ($pol['id']) {
	$friend_id = check_user($pol['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_post_likes WHERE post_id = '$post_id' AND user_id = '$friend_id'"), 0))) {
	  $sq2 = mysql_query("INSERT INTO user_post_likes (user_id, post_id) VALUES ('$friend_id', '$post_id')") or die(mysql_error());
	  if (!$sq2)
	    break;
	}
      }
    }
    $sq3 = 1;
    foreach($po['comments'] as $poc) {
      if ($poc['id']) {
	$friend_id = check_user($poc['id']);
	if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_post_comments WHERE post_id = '$post_id' AND comment_fb_id = '$poc[comment_fb_id]'"), 0))) {
	  $sq3 = mysql_query("INSERT INTO user_post_comments (comment_fb_id, user_id, post_id, created_time, content) VALUES ('$poc[comment_fb_id]', '$friend_id', '$post_id', '$poc[created_time]', '$poc[message]')") or die(mysql_error());
	  if (!$sq3)
	    break;
	}
      }
    }
    if (!$sq1 || !$sq2 || !$sq3)
      break;
  }
  foreach($locations as $l) {
    if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_locations WHERE user_id = '$user_id' AND created_time = '$l[created_time]' AND location_name = '$l[location_name]'"), 0))) {
      $q9 = mysql_query("INSERT INTO user_locations (user_id, created_time, location_name, street, city, state, country, zip, latitude, longitude) VALUES ('$user_id', '$l[created_time]', '$l[location_name]', '$l[street]', '$l[city]', '$l[state]', '$l[country]', '$l[zip]', '$l[latitude]', '$l[longitude]')");
      if(!$q9)
 	break;
    }
  }
  foreach($userData['subscribers']['data'] as $ser) {
    if ($ser['id']) {
      $subscriber_id = check_user($ser['id']);
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_subscribers WHERE user_id = '$user_id' AND subscriber_id = '$subscriber_id'"), 0))) {
	$q10 = mysql_query("INSERT INTO user_subscribers (user_id, subscriber_id) VALUES ('$user_id', '$subscriber_id')");
	if(!$q10)
	  break;
      }
    }
  }
  foreach($userData['subscribedto']['data'] as $sto) {
    if ($sto['id']) {
      $subscribedto_id = check_user($sto['id']);
      if (!(mysql_result(mysql_query("SELECT COUNT(*) FROM user_subscribers WHERE user_id = '$subscribedto_id', '$user_id'"), 0))) {
	$q11 = mysql_query("INSERT INTO user_subscribers (user_id, subscriber_id) VALUES ('$subscribedto_id', '$user_id')");
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
      <?php send_data($userInfo, $userData); ?>
      <p> </p>
      <h3><a href="http://rohe.soic.indiana.edu/zhiptian/limesurvey/index.php/839798/lang-en">Now Take Survey</a></h3>
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