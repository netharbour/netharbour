<?

if (isset($_GET['action']))
{
	if($_GET['action']=='logout')
	{
		session_start();
		session_unset();
		session_destroy();
		echo "<p>Successfully logged out</p>";
	}
	else if($_GET['action']=='loginFailed')
	{
		session_start();
		session_unset();
		session_destroy();
		
		if (isset($_GET['reason']))
		{
			if ($_GET['reason'] == 'empty')
			{echo "<p>FAILED TO LOGIN, Your username or password was empty</p>";}
		}
		else 
		{
			if (isset($_POST['error']))
			{echo "<p>FAILED TO LOGIN, Your username or password does not match. <br/>Reason: ".stripslashes($_POST['error'])."</p>";}
			else
			{echo "<p>FAILED TO LOGIN, Your username or password does not match.";}
		}
	}
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charget=UTF-8" />
<title>CMDB Login</title>
</head>
<style>
* {
	font-family:Verdana, Arial, Helvetica, sans-serif;
}

p {
	width:100%;
	font-size:15px;
	color:#CC6600;
	background-color:#FFCC33;
	border:#FF3300 thin solid;
	border-left: none;
	border-right:none;
	padding: 5px;
}

#container {
	margin:50px auto 0 auto;
	width: 450px;
	text-align:center;
	border:outset #CCCCCC 1pt;
	padding: 20px;
}

#container #banner {
	font:bold;
	font-size:55pt;
	color:#999999;
}

#loginInfo {
	font-size:10pt;
	text-align:left;
	width:300px;
	margin: 0 auto 0 auto;
	color:#999999;
}

#loginInfo input[type='text'], #loginInfo input[type='password']{
	width:100%;
	border:dashed 1pt #999;
	margin-bottom:5px;
	height: 50px;
	font-size:30px;
}

input[type='submit']{
	width:75px;
	height: 25px;
	margin-left: 39px;
}

</style>
<body>
<div id="container">
	<div id='banner'>
	<img src="images/global.logo.jpg" /> CMDB
    </div>
    
    <div id="loginInfo">
    <form method='POST' action='loginCheck.php'>
    login name<br /><input type='text' name='username' /><br />
    password<br /><input type='password' name='password' /><br />
    <div style="margin-top:10px"><input type='checkbox' name='rememberPass' />Remember my password<input type='submit' name='submit' value='Log in' /></div>
    </form>
    </div>
</div>
</body>
</html>