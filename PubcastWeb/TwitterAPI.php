

<?php
session_start();
require_once("./twitteroauth/twitteroauth.php"); //Path to twitteroauth library you downloaded in step 3

$twitteruser = "NatureRevCancer"; //user name you want to reference
$twitterusers=array("bffo","Alexbateman1","ewanbirney","wakibbe","eric_lander","newscientist","openscience","NatureRevCancer");
$notweets = 5; //how many tweets you want to retrieve
$consumerkey = "2cZK0pUibXIuxXgAkNmFRA"; //Noted keys from step 2
$consumersecret = "WAfvx5NNnzvXZXs3aKOIfLwMJw6DuyiV4mMh5Ye0"; //Noted keys from step 2
$accesstoken = "1104885242-gBfcX8jM95Lrmte4yJUTNv8OarAKEFnSyqRsuqz"; //Noted keys from step 2
$accesstokensecret = "KGLYLZtFXmg06XlAq3HEQlR9TLYk9waPLADJH5eaVbRs1"; //Noted keys from step 2

function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}
 
$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
//$tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);
$tweets=array();
foreach($twitterusers as $user) {
	$tweet = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$user."&count=".$notweets);
	array_push($tweets,$tweet);
}
 
echo json_encode($tweets) ;
	?>
