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
    $userData = $facebook->api('/me?fields=family,likes,statuses.limit(10)');
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
$username = 'facebook' ;//username
$pass = 'facebook' ; //password
$dbname = 'facebook'; // database Name
$conn = mysql_connect($host, $username, $pass) or die(mysql_error());
if ($conn) {
  mysql_select_db($dbname) or die(mysql_error());
} else  {
  echo 'Connection failed.';
}

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
			echo 'User added successfully!';
		} else {
			mysql_query("ROLLBACK");
			echo 'error adding the user\'s data to database';
		}
	}
	$user_id = mysql_result(mysql_query("SELECT id FROM users WHERE facebook_id = $facebook_id"), 0);
	return $user_id;
}
	
function send_data($userInfo, $userData) { 
  //table: users	
  $facebook_id = $userInfo['id'];
  $username = $userInfo['username'];
  $first_name = $userInfo['first_name'];
  $last_name = $userInfo['last_name']; 
  
  //table: user_bio
  $gender = (isset($userInfo['gender'])? $userInfo['gender']:'');
  $birthday = (isset($userInfo['birthday'])? date("Y-m-d", strtotime($userInfo['birthday'])):'');
  $email = (isset($userInfo['email'])? $userInfo ['email']:'');
  $hometown = (isset($userInfo['hometown'])? $userInfo['hometown']['name']:'');
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
  $politics = (isset($userInfo['political'])? $userInfo['political']:'');
  $religion = (isset($userInfo['religion'])? $userInfo['religion']:'');
  $website = (isset($userInfo['website'])? $userInfo['website']:'');
  
  //table: user_education_history
  $education = array();
  if (isset($userInfo['education'])) {
    for($i=0; $i<count($userInfo['education']); $i++) {
      $education[$i] = array();
      $education[$i]['school'] = (isset($userInfo['education'][$i]['school'])?$userInfo['education'][$i]['school']['name']:'');
      $education[$i]['year'] = (isset($userInfo['education'][$i]['year'])?$userInfo['education'][$i]['year']['name']:'');
      $education[$i]['type'] = (isset($userInfo['education'][$i]['type'])?$userInfo['education'][$i]['type']:'');
    }
  }
  
  //table: user_family
  $family = array();
  if (isset($userData['family'])) {
    for($i=0; $i<count($userData['family']['data']); $i++) {
      $family[$i] = array();
      $family[$i]['name'] = (isset($userData['family']['data'][$i]['name'])?$userData['family']['data'][$i]['name']:'');
      $family[$i]['fb_id'] = (isset($userData['family']['data'][$i]['id'])?$userData['family']['data'][$i]['id']:'');
      $family[$i]['relationship'] = (isset($userData['family']['data'][$i]['relationship'])?$userData['family']['data'][$i]['relationship']:'');
    }
  }
  
  //table: user_likes
  $likes = array();
  if (isset($userData['likes'])) {
    for($i=0; $i<count($userData['likes']['data']); $i++) {
      $likes[$i] = array();
      $likes[$i]['category'] = (isset($userData['likes']['data'][$i]['category'])?$userData['likes']['data'][$i]['category']:'');
      $likes[$i]['name'] = (isset($userData['likes']['data'][$i]['name'])?$userData['likes']['data'][$i]['name']:'');
      $likes[$i]['created_time'] = (isset($userData['likes']['data'][$i]['created_time'])?date('Y-m-d H:i:s', strtotime($userData['likes']['data'][$i]['created_time'])):'');
    }
  }
  
  //table: user_locations
  
  
  
  
  
  //table: user_statuses
  $statuses = array();
  if (isset($userData['statuses'])) {
    for($i=0; $i<count($userData['statuses']['data']); $i++) {
      $statuses[$i] = array();
      $statuses[$i]['message'] = (isset($userData['statuses']['data'][$i]['message'])?$userData['statuses']['data'][$i]['message']:'');
      $statuses[$i]['updated_time'] = (isset($userData['statuses']['data'][$i]['updated_time'])?date('Y-m-d H:i:s', strtotime($userData['statuses']['data'][$i]['updated_time'])):'');
      $statuses[$i]['place'] = (isset($userData['statuses']['data'][$i]['place'])?$userData['statuses']['data'][$i]['place']['name']:'');
      
      //table: user_status_tags
			$statuses[$i]['tags'] = array();
      if (isset($userData['statuses']['data'][$i]['tags'])) {
	      for($x=0; $x<count($userData['statuses']['data'][$i]['tags']['data']); $x++) {
	        $statuses[$i]['tags'][$x] = array();
	        $statuses[$i]['tags'][$x]['id'] = (isset($userData['statuses']['data'][$i]['tags']['data'][$x]['id'])?$userData['statuses']['data'][$i]['tags']['data'][$x]['id']:'');
	        $statuses[$i]['tags'][$x]['name'] = (isset($userData['statuses']['data'][$i]['tags']['data'][$x]['name'])?$userData['statuses']['data'][$i]['tags']['data'][$x]['name']:'');
        }
			}

      //table: user_status_likes
			$statuses[$i]['likes'] = array();
      if (isset($userData['statuses']['data'][$i]['likes'])) {
	      for($y=0; $y<count($userData['statuses']['data'][$i]['likes']['data']); $y++) {
	        $statuses[$i]['likes'][$y] = array();
	        $statuses[$i]['likes'][$y]['id'] = (isset($userData['statuses']['data'][$i]['likes']['data'][$y]['id'])?$userData['statuses']['data'][$i]['likes']['data'][$y]['id']:'');
	        $statuses[$i]['likes'][$y]['name'] = (isset($userData['statuses']['data'][$i]['likes']['data'][$y]['name'])?$userData['statuses']['data'][$i]['likes']['data'][$y]['name']:'');
	      }
      }

      //table: user_status_comments
			$statuses[$i]['comments'] = array();
      if (isset($userData['statuses']['data'][$i]['comments'])) {
	      for($z=0; $z<count($userData['statuses']['data'][$i]['comments']['data']); $z++) {
	        $statuses[$i]['comments'][$z] = array();
	        $statuses[$i]['comments'][$z]['id'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['from'])?$userData['statuses']['data'][$i]['comments']['data'][$z]['from']['id']:'');
	        $statuses[$i]['comments'][$z]['name'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['from'])?$userData['statuses']['data'][$i]['comments']['data'][$z]['from']['name']:'');
	        $statuses[$i]['comments'][$z]['message'] = (isset($userData['statuses']['data'][$i]['comments']['data'][$z]['message'])?$userData['statuses']['data'][$i]['comments']['data'][$z]['message']:'');
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
      $work[$i]['employer'] = (isset($userInfo['work'][$i]['employer'])?$userInfo['work'][$i]['employer']['name']:'');
      $work[$i]['location'] = (isset($userInfo['work'][$i]['location'])?$userInfo['work'][$i]['location']['name']:'');
      $work[$i]['position'] = (isset($userInfo['work'][$i]['position'])?$userInfo['work'][$i]['position']['name']:'');
      $work[$i]['start_date'] = (isset($userInfo['work'][$i]['start_date'])?$userInfo['work'][$i]['start_date']:'');
      $work[$i]['end_date'] = (isset($userInfo['work'][$i]['end_date'])?$userInfo['work'][$i]['end_date']:'');
    }
  }
	

  //insert all data into database
	$user_id = check_user($facebook_id, 1);
  mysql_query("BEGIN");
	/*$q1 = mysql_query("INSERT INTO user_bio (user_id, gender, birthday, email, hometown, language, politics, religion, website) VALUES ('$user_id','$gender', '$birthday', '$email', '$hometown', '$language', '$politics', '$religion', '$website')") or die(mysql_error());
  foreach ($education as $e) {
	  $q2 = mysql_query("INSERT INTO user_education_history (user_id, school, year, type) VALUES ('$user_id', '$e[school]', '$e[year]', '$e[type]')") or die(mysql_error());
		if(!$q2)
			break;
	}
	foreach ($family as $f) {
		$member_id = check_user($f['fb_id']);
		$q3 = mysql_query("INSERT INTO user_family (user1_id, user2_id, relationship) VALUES ('$user_id', '$member_id', '$f[relationship]')") or die(mysql_error());
		if (!$q3)
			break;	
	}
	foreach($likes as $l) {
		$q4 = mysql_query("INSERT INTO user_likes (category, name, user_id, created_time) VALUES ('$l[category]', '$l[name]', '$user_id', '$l[created_time]')") or die(mysql_error());
		if (!$q4)
			break;
	}*/
	$q5 = 1;
	foreach($statuses as $s) {
		$q5 = mysql_query("INSERT INTO user_statuses (user_id, message, updated_time, place) VALUES ('$user_id', '$s[message]', '$s[updated_time]', '$s[place]')") or die(mysql_error());
		if (!$q5)
			break;
		$status_id = 	mysql_result(mysql_query("SELECT status_id FROM user_statuses WHERE updated_time = '$s[updated_time]'"), 0);
		foreach($s['tags'] as $st) {
			$friend_id = check_user($st['id']);
			$sq1 = mysql_query("INSERT INTO user_status_tags (user_id, status_id) VALUES ('$friend_id', '$status_id')") or die(mysql_error());
			if (!$sq1)
				break;
		}
		foreach($s['likes'] as $sl) {
			$friend_id = check_user($sl['id']);
			$sq2 = mysql_query("INSERT INTO user_status_likes (user_id, status_id) VALUES ('$friend_id', '$status_id')") or die(mysql_error());
			if (!$sq2)
				break;
		}
		//foreach($s['comments']
		if (!$sq1 || !$sq2)
			break;
	}
	//check the process
  if($q5 ){
    mysql_query("COMMIT");
    echo 'All info has been sent successfully! Thank you!';
  } else {
    mysql_query("ROLLBACK");
    echo 'error adding to database';
  }
}
?>

<html>
  <head>
    <title>Facebook App</title>
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
    <?php } else { ?>
      <p>
      <strong><a href="<?php echo $loginUrl; ?>" target="_top">Allow this app to interact with my profile</a></strong>
      <br /><br />
      This is just a simple app for testing some facebook graph API calls. After allowing this application, 
      it can be used to post messages on your wall and do something other stuff.
      </p>
    <?php } ?>								
  </body>
</html>