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
		$userData = $facebook->api('/me?fields=likes');
  } catch (FacebookApiException $e) {
    echo 'couldnot find the user';
  }
} else {
  $loginUrl = $facebook->getLoginUrl(array(
    'canvas' => 1,
    'fbconnect' => 0,
    'scope' => 'email, user_actions.music, user_activities, user_events, user_hometown, ' .
							 'user_location, user_questions, user_religion_politics, user_videos, ' .
							 'publish_actions, user_actions.news, user_birthday, user_games_activity, ' .
							 'user_interests, user_notes, user_relationship_details, user_status, ' .
							 'user_website, user_about_me, user_actions.video, user_education_history, ' .
							 'user_groups, user_likes, user_photos, user_relationships, user_subscriptions, ' .
							 'user_work_history, read_stream, read_insights, read_requests, read_friendlists'	
	) );
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
	
	//table: user_likes
	$likes = array();
	if (isset($userData['likes'])) {
		echo 'in the loop';
		for($i=0; $i<count($userData['likes']['data']); $i++) {
			$likes[$i] = array();
			$likes[$i]['category'] = (isset($userData['likes']['data'][$i]['category'])?$userData['likes']['data'][$i]['category']:'');
			$likes[$i]['name'] = (isset($userData['likes']['data'][$i]['name'])?$userData['likes']['data'][$i]['name']:'');
			$likes[$i]['created_time'] = (isset($userData['likes']['data'][$i]['created_time'])?date('Y-m-d H:i:s', strtotime($userData['likes']['data'][$i]['created_time'])):'');
		}
	}

	//table: user_locations
	
	
	//
	/*
  //insert all data into database
  mysql_query("BEGIN");
  $q1 = mysql_query("INSERT INTO users (facebook_id, username, first_name, last_name) VALUES ('$facebook_id', '$username', '$first_name', '$last_name')") or die(mysql_error());
  $q2 = mysql_query("INSERT INTO user_bio (user_id, gender, birthday, email, hometown, language, politics, religion, website) VALUES ((SELECT id FROM users WHERE facebook_id = $facebook_id),'$gender', '$birthday', '$email', '$hometown', '$language', '$politics', '$religion', '$website')") or die(mysql_error());
  
  //check the process
  if($q1 && $q2){
    mysql_query("COMMIT");
    echo 'All info has been sent successfully! Thank you!';
  } else {
    mysql_query("ROLLBACK");
    echo 'error adding to database';
  }*/
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