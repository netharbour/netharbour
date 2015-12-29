<?
session_start();
include_once "config/opendb.php";
include_once "classes/Dashboard.php";
if (!isset($_SESSION['username']) || !isset($_SESSION['password']))
{
	header("Location: login.php");
}
$access = $_SESSION['access'];

if (preg_match("/configurations.php/", $_SERVER['PHP_SELF']))
{
	if ($access != 100)
	{
		header("Location: index.php");
		echo "<h1>You have no permission</h1>";
	}
}

if ($_SESSION['action'] !="")
{
	$allUpdates = Updates::get_updates();
	$index = 0;
	foreach ($allUpdates as $id => $value)
	{
		$index++;
		if ($index >10)
		{
			$curUpdate = new Updates($id);
			if ($curUpdate->get_archived() == 0)
			{
				$curUpdate->set_archived(1);
				if(!$curUpdate->update())
				{echo 'update widget failed. Reason: '.$curUpdate->get_error();}
			}
		}
	}
	$update = new Updates();
	$update->set_action($_SESSION['action']);
	$update->set_username($_SESSION['fullname']);
	$update->set_archived(0);
	if($update->insert_update())
	{$_SESSION['action']="";}
}
?>
