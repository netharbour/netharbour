<?
include_once("sessionCheck.php");
include_once "classes/Widgets.php";

$userID = $_SESSION['userid'];

$curUser = new DashboardUsers($userID);
$userWidgets = $curUser->get_users_widgets();

if ($userWidgets != '')
{
	foreach ($userWidgets as $widgetID => $userID)
	{
		if($_GET['widget'] == $widgetID)
		{
			$curWidgetInfo = new Widgets($widgetID);
			$filename = $curWidgetInfo->get_filename();
			if (file_exists($filename))
			{
				include_once $filename;
				$widgetClass = $curWidgetInfo->get_class_name();
				
				if (class_exists($widgetClass))
				{
					$widget = new $widgetClass();
					if(method_exists($widget, 'get_content')) {echo $widget->get_content();}
					else{echo "No Content to retrieve";}
				}
			}			
		}
	}
}
?>