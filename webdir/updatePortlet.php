<?
include_once ("sessionCheck.php");

$user = $_SESSION['userid'];
$curUser = new DashboardUsers($user);

if (isset($_POST['p']))
{
	$newPos = $_POST['p'];
	foreach ($newPos as $posY => $widgetID)
	{
		$curUser->set_widget_id($widgetID);
		$curUser->set_position_y($posY);
		
		if(isset($_POST['action']))
		{
			$newCol = $_POST['action'];
			switch ($newCol)
			{
				case 'col1':
				$curUser->set_position_x(0);
				break;
				
				case 'col2':
				$curUser->set_position_x(1);
				break;
				
				case 'col3':
				$curUser->set_position_x(2);
				break;
			}
			if(!$curUser->update_widget())
			{
				echo $curUser->get_error();
			}
		}
	}
}
?>