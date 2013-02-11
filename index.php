<?php

require './config.php';
require './facebook.php';

//Create facebook application instance.
$facebook = new Facebook(array(
  'appId'  => $fb_app_id,
  'secret' => $fb_secret,
  'cookie' => true,
));

$friends = array();
$sent = false;
$userData = null;
$count = 0;
$male = 0;

//redirect to facebook page
if(isset($_GET['code'])){
	header("Location: " . $fb_app_url);
	exit;
}

$user = $facebook->getUser();
if ($user) {
	//get user data
	try {
		$userData = $facebook->api('/me');
	} catch (FacebookApiException $e) {
		//do something about it
	}
	
	//get 5 random friends
	try {
		$friendsTmp = $facebook->api('/' . $userData['id'] . '/friends?fields=id,name,gender');
		//shuffle($friendsTmp['data']);
		//array_splice($friendsTmp['data'], 5);
		$friends = $friendsTmp['data'];
	} catch (FacebookApiException $e) {
		//do something about it
	}
	
	//get most recent status
	try {
		$userStatus = $facebook->api('/me/statuses?limit=1');
	} catch (FacebookApiException $e) {
		//do something about it
	}
	
	//post message to wall if it is sent trough form
	if(isset($_POST['mapp_message'])){
		try {
			$facebook->api('/me/feed', 'POST', array(
				'message' => $_POST['mapp_message']
			));
			$sent = true;
		} catch (FacebookApiException $e) {
			//do something about it
		}
	}
	
} else {
	$loginUrl = $facebook->getLoginUrl(array(
		'canvas' => 1,
		'fbconnect' => 0,
		'scope' => 'read_stream, publish_stream, user_birthday, user_location, user_hometown, email, friends_birthday, friends_location',
	));
}

?>
<!DOCTYPE html 
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="et" lang="en">
	<head>
		<title>Facebook App Test</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			body { font-family:Verdana,"Lucida Grande",Lucida,sans-serif; font-size: 12px}
		</style>
	</head>
	<body>
		<h1>Facebook App Test</h1>
			
			<h3><?php echo ' Welcome ' . $userData['name'] . '!'; ?></h3>
			<?php if ($user){ ?>
				<?php if ($sent){ ?>
					<p><strong>Message sent!</strong></p>
				<?php } ?>
				<p><strong>Your most recent status is : </strong><?php echo $userStatus['data']['0']['message'] ?></p>
				<form method="post" action="">
					<p><input type="text" value="Update your status here..." size="60" name="mapp_message" /></p>
					<p><input type="submit" value="Send message to the wall" name="sendit" /></p>
				</form>
				<p>
					<br /><br />
					<h2>User Basic Info: </h2>
					<strong>Name: </strong><?php echo $userData['name'] ?><br />
					<strong>Gender: </strong><?php echo $userData['gender'] ?><br />
					<strong>Birthday: </strong><?php echo $userData['birthday'] ?><br />
					<strong>Email: </strong><?php echo $userData['email'] ?><br />
					<strong>Location: </strong><?php echo $userData['location']['name'] ?><br />
					<strong>Hometown: </strong><?php echo $userData['hometown']['name'] ?><br />
					<strong>Link: </strong><?php echo $userData['link'] ?><br />
					
					<br /><br />
					<h2>Friends Info:</h2>
					<?php foreach($friends as $k => $i){ ?>
						<!--<?php echo $i['name'] . '\'s location: ' . (isset($i['gender']) ? $i['gender'] : 'unknown')?><br />-->
						<?php if (isset($i['gender'])) {	$count += 1; if ($i['gender'] == 'male') { $male += 1; }} ?>
					<?php } ?>
					<?php echo 'male: ' . round($male*100/$count, 2) . '%, female: ' . round(($count-$male)*100/$count, 2) . '%' ?><br />
					
				</p>
				<form method='post' action='database.php'>
					
					<input name='first_name' type='hidden' readonly value='<?php echo $userData['first_name']; ?>' /><br />
					
					<input name='last_name' type='hidden' readonly value='<?php echo $userData['last_name']; ?>' /><br />
					
					<input name='gender' type='hidden' readonly value='<?php echo $userData['gender']; ?>' /><br />
					
					<input name='birthday' type='hidden' readonly value='<?php echo $userData['birthday']; ?>' /><br />
					
					<input name='email' type='hidden' readonly value='<?php echo $userData['email']; ?>' /><br />
					<input name='submit' type='submit' readonly value='submit' />
				</form>
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
