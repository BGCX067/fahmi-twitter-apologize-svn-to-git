<?php include('header.tpl') ?>
<?php
/**
 * @file
 * User has successfully authenticated with Twitter. Access tokens saved to session and DB.
 */

$output = "";

/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');


/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: ./clearsessions.php');
}
/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

/* If method is set change API call made. Test is called by default. */
$credential = $connection->get('account/verify_credentials');

$tmp = $connection->get('account/rate_limit_status');

// post message
$loop = $_POST['loop'];
if(!empty($loop) && $loop > 0)
{
	$content = "";
	$msg = $_POST['msg'];
	for($i=0; $i<$loop; $i++)
	{
		$postMsg = ($i+1)."/{$loop} ".$msg;

		$postMsg = substr(stripslashes($postMsg), 0, 140);
		$content .= "Posting Message: '{$postMsg}'...\n";
		$connection->post('statuses/update', array('status' => $postMsg));
	}
}

?>

<?php if (isset($menu)) { ?>
	<?php echo $menu; ?>
<?php } ?>

<?php if (isset($status_text)) { ?>
	<?php echo '<h3>'.$status_text.'</h3>'; ?>
<?php } ?>

<?php if(!empty($credential)): ?>
<div id="credential">
	<span class="profile-image"><img src="<?php echo $credential->profile_image_url ?>" /></span>
	Welcome, <span class="screen-name"><?php echo $credential->screen_name ?></span>. Your current API hits remaining: <?php echo "{$tmp->remaining_hits}" ?>.
</div>
<?php endif; ?>

<form action="index.php" method="post">
	<div class="item"><label for="msg">Message</label><textarea id="msg" name="msg"><?php echo (!empty($_POST['msg'])) ? stripslashes($_POST['msg']) : "I've DEFAMED Blu Inc Media & Female Magazine. My tweets on their HR Policies are untrue. I retract those words & hereby apologize" ?></textarea><span class="hint">What's your apologize message? Keep it short as twitter has a 140 characters limit.</span></div>
	<div class="item"><label for="loop">Loop</label>
		<select id="loop" name="loop">
		<?php $limit = (!empty($_POST['loop'])) ? $_POST['loop'] : $tmp->remaining_hits; for($i=0; $i<$limit; $i++): ?>
			<option <?php if($limit == $i+1) echo 'selected="selected"'?> value="<?php echo $i+1; ?>"><?php echo $i+1; ?></option>
		<?php endfor; ?>
		</select>
		<span class="hint">How many message you wanto post? For your info, Blu Inc Media & Female Magazine requested Fahmi to post 100 messages but I think 3 is good enough. Your maximum post allowed is the limit of your available Twitter API Call for today.</span>
	</div>
	<div class="item"><input type="submit" value="POST" /></div>
</form>

<?php if(!empty($content)): ?>
<h3>Result:</h3>
<div id="result">
<?php echo nl2br($content); ?>
</div>
<?php endif; ?>

<?php include('footer.tpl') ?>

