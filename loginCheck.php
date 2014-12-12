<?
if (!isset($_POST['username']) || !isset($_POST['password']))
{
	header("Refresh: 3 URL=\"login.php?action=loginFailed&reason=empty\"");
	echo "<h1>CANNOT LOGIN!</h1> <p>Reason: Password or name is empty. If your page doesn't refresh, <a href='login.php?action=loginFailed&reason=empty'>click here to back</a></p>";
}
else if (empty($_POST['username']) || empty($_POST['password']))
{
	header("Refresh: 3 URL=\"login.php?action=loginFailed&reason=empty\"");
	echo "<h1>CANNOT LOGIN!</h1> <p>Reason: Password or name is empty. If your page doesn't refresh, <a href='login.php?action=loginFailed&reason=empty'>click here to back</a></p>";
}
else
{
	include_once "config/opendb.php";
	include_once 'classes/AAA.php';
	$user = addslashes($_POST['username']);
	$pass = $_POST['password'];
	
	$userCheck = new User();
	if ($userCheck->authenticate_user($user, $pass))
	{
		$userID = $userCheck->get_user_id_by_user_name($user);
		$newUser = new User($userID);
		$access = $newUser->get_access_level();
		$full = $newUser->get_full_name();
		$newUser->update_last_login(getIP());
		session_start();
		$_SESSION['username'] = $user;
		$_SESSION['fullname'] = $full;
		$_SESSION['password'] = $pass;
		$_SESSION['access'] = $access;
		$_SESSION['userid'] = $userID;
		$_SESSION['action'] = "";
		header("Location: index.php");
		echo "Login complete.";
	}
	else
	{
		?>
        <form method='post' name="errorForm" action='login.php?action=loginFailed'>
        <input type='hidden' name='error' value="<? echo $userCheck->get_error(); ?>" />
        </form>
        <script LANGUAGE='JavaScript'>window.onload=function(){document.errorForm.submit()};</script>
        <?
		echo "<h1>CANNOT LOGIN!</h1> <p>Reason: ". $userCheck->get_error() .". If your page doesn't refresh, <a href='login.php?action=loginFailed'>click here to back</a></p>";
	}
}
function getIP() {
	$ip;
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else
		$ip = "UNKNOWN";
	return $ip;
} 
?>
