<?
include_once("sessionCheck.php");
include_once("controlBar.php")?>

<script type="text/javascript" src="js/devKit/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/devKit/ui/ui.core.js"></script>
<script type="text/javascript" src="js/devKit/ui/ui.sortable.js"></script>
<script type="text/javascript">

$(function() {
	$("#col1").sortable({
		handle: ".portlet-header", opacity: 0.6, cursor: 'move', connectWith: '.infoBox', update: function(){
		var order = $(this).sortable("serialize") + '&action=col1';
		$.post("updatePortlet.php", order);
		}
	});
	
	$("#col2").sortable({
		handle: ".portlet-header", opacity: 0.6, cursor: 'move', connectWith: '.infoBox', update: function(){
		var order = $(this).sortable("serialize") + '&action=col2';
		$.post("updatePortlet.php", order);
		}
	});
	
	$("#col3").sortable({
		handle: ".portlet-header", opacity: 0.6, cursor: 'move', connectWith: '.infoBox', update: function(){
		var order = $(this).sortable("serialize") + '&action=col3';
		$.post("updatePortlet.php", order);
		}
	});

	$(".portlet-header .minimize").click(function() {
		$(this).toggleClass("maximize");
		$(this).parents(".portlet:first").find(".portlet-content").toggle();
	});
});
</script>
<?
include_once "classes/Widgets.php";

$userID = $_SESSION['userid'];

$curUser = new DashboardUsers($userID);
$userWidgets = $curUser->get_users_widgets();

if (isset($_POST['widget_update']))
{
	if ($userWidgets != '')
	{
		foreach ($userWidgets as $widgetID => $userID)
		{
			$curWidgetInfo = new Widgets($widgetID);
			
			if ($curWidgetInfo->get_class_name() == $_POST['class'])
			{
				$filename = $curWidgetInfo->get_filename();
				if (file_exists($filename))
				{
					include_once $filename;
					$widgetClass = $curWidgetInfo->get_class_name();
						
					if (class_exists($widgetClass))
					{
						$widget = new $widgetClass();
						
						$postValues = $_POST;
						$values = array();
						foreach ($postValues as $id => $value)
						{
							$pos = strpos($id, "Widget_");
							if ($pos === 0)
							{
								$widget->update_config();
							}
						}
					}
				}
			}
		}
	}
}

//make all the css for the modal		
echo "<style>";
foreach ($userWidgets as $widgetID => $userID)
{	
	echo "#modalBox #dialog".$widgetID;
	echo "{
		width:auto;
		max-width: 80%;
		min-width:40%;
  		height:auto;
		padding:10px;
		padding-top:10px;
		overflow:auto;
	}";
}
echo "</style>";

echo "<div id='main'>
<h1 id='mainTitle'>HOME</h1>";

echo "<div id='col1' class='infoBox'>";
if ($userWidgets != '')
{
	foreach ($userWidgets as $widgetID => $userID)
	{
		$curWidgetInfo = new Widgets($widgetID);
		$title = $curWidgetInfo->get_name();
		
		$col = $curUser->get_position_x($widgetID);
		if ($col == 0)
		{
			if (file_exists($curWidgetInfo->get_filename()))
			{
				include_once $curWidgetInfo->get_filename();
				$widgetClass = $curWidgetInfo->get_class_name();
				
				if (class_exists($widgetClass))
				{
					$widget = new $widgetClass();
					
					echo "<div class='portlet' id='p_".$widgetID."'>
							<div id='title' class='portlet-header'>";
					echo $title;
					
					echo "<div class='minimize'></div>";
					if(method_exists($widget, 'get_config')) {echo "<a name=modal href='#dialog".$widgetID."' style='float:right;color:#FFF'>Edit</a>";}
					echo"</div>
							<div id='id".$widgetID."' class='portlet-content information'>
							<h3> LOADING...</h3>";
					
					echo  "</div>
						   </div>";
					
					if(method_exists($widget, 'get_config'))
					{
						echo "<div id='modalBox'>
							<div id='dialog".$widgetID."' class='window'>";
						echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
						echo $widget->get_config();
						echo "</div>
							<div id='mask'></div>
							</div>";
					}
				}
				else {
					echo "something is wrong with your file, ".$curWidgetInfo->get_filename();	
				}
			}
		}
	}
}
echo "</div>";

echo "<div id='col2' class='infoBox'>";
if ($userWidgets != '')
{
	foreach ($userWidgets as $widgetID => $userID)
	{
		$curWidgetInfo = new Widgets($widgetID);
		$title = $curWidgetInfo->get_name();
		$col = $curUser->get_position_x($widgetID);
		if ($col == 1)
		{
			if (file_exists($curWidgetInfo->get_filename()))
			{
				include_once $curWidgetInfo->get_filename();
				$widgetClass = $curWidgetInfo->get_class_name();
				
				if (class_exists($widgetClass))
				{
					$widget = new $widgetClass();
					
					echo "<div class='portlet' id='p_".$widgetID."'>
							<div id='title' class='portlet-header'>";
					echo $title;
					
					echo "<div class='minimize'></div>";
					
					if(method_exists($widget, 'get_config')) {echo "<a name=modal href='#dialog".$widgetID."' style='float:right;color:#FFF'>Edit</a>";}
					echo"</div>
							<div id='id".$widgetID."' class='portlet-content information'>
							<h3> LOADING...</h3>";
					
					echo  "</div>
						   </div>";	
						   
					if(method_exists($widget, 'get_config'))
					{
						echo "<div id='modalBox'>
							<div id='dialog".$widgetID."' class='window'>";
						echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
						echo $widget->get_config();
						echo "</div>
							<div id='mask'></div>
							</div>";
					}
				}
				else {
					echo "something is wrong with your file, ".$curWidgetInfo->get_filename();
				}
			}
		}
	}
}
echo "</div>";

echo "<div id='col3' class='infoBox'>";
if ($userWidgets != '')
{
	foreach ($userWidgets as $widgetID => $userID)
	{
		$curWidgetInfo = new Widgets($widgetID);
		$title = $curWidgetInfo->get_name();
		
		$col = $curUser->get_position_x($widgetID);
		if ($col == 2)
		{
			if (file_exists($curWidgetInfo->get_filename()))
			{
				include_once $curWidgetInfo->get_filename();
				$widgetClass = $curWidgetInfo->get_class_name();
				
				if (class_exists($widgetClass))
				{
					$widget = new $widgetClass();
					
					echo "<div class='portlet' id='p_".$widgetID."'>
							<div id='title' class='portlet-header'>";
					echo $title;
					
					echo "<div class='minimize'></div>";
					
					if(method_exists($widget, 'get_config')) {echo "<a name=modal href='#dialog".$widgetID."' style='float:right;color:#FFF'>Edit</a>";}
					echo"</div>
							<div id='id".$widgetID."' class='portlet-content information'>
							<h3> LOADING...</h3>";
					echo  "</div>
						   </div>";
					
					if(method_exists($widget, 'get_config'))
					{
						echo "<div id='modalBox'>
							<div id='dialog".$widgetID."' class='window'>";
						echo $widget->get_config();
						echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
						echo "</div>
							<div id='mask'></div>
							</div>";
					}
				}
				else {
					echo "something is wrong with your file, ".$curWidgetInfo->get_filename();	
				}
			}	
		}
	}
}
echo "</div>";

/*echo "<div id='col2' class='infoBox'>";
if ($userWidgets != '')
{
	foreach ($userWidgets as $widgetID => $userID)
	{
		$curWidgetInfo = new Widgets($widgetID);
		$title = $curWidgetInfo->get_name();
		
		if ($curWidgetInfo->get_position_x() == 1)
		{
			include_once $curWidgetInfo->get_filename();
			$widgetClass = $curWidgetInfo->get_class_name();
			$widget = new $widgetClass();
			
			echo "<div class='portlet' id='p_1'>
					<div id='title' class='portlet-header'>";
			echo $title;
			echo "<div class='minimize'></div></div>
					<div id='information' class='portlet-content'>";
			echo $widget->get_content();
			echo  "</div>
				   </div>";
		}
	}
}
echo "</div>";*/
print "<script language='javascript'>
$(function() {";
foreach ($userWidgets as $widgetID => $userID)
{
	$curWidgetInfo = new Widgets($widgetID);
	
	$widgetClass = $curWidgetInfo->get_class_name();
	
	$phpFile =$curWidgetInfo->get_filename();
	$fh = fopen($phpFile, 'r');
	$data = fread($fh, filesize($phpFile));
	fclose($fh);
	
	if (!preg_match("/echo /i", $data) && !preg_match("/print /i", $data))
	{
				
	//	if (preg_match
		if (class_exists($widgetClass))
		{
			$widget = new $widgetClass();
		}
		else {print "$(\"#id".$widgetID."\").html(\"Your class, \"".$widgetClass."\", taken from the database does not exist in your php file\");";}
		if(method_exists($widget, 'get_content')) {
		
			if($widget->get_content() !='')
			{
				echo "$(\"#id".$widgetID."\").load(\"loadPortlet.php?widget=".$widgetID."\");";
			}
		}
		else{echo "No Content to retrieve";}
	}
	else
	{
		print "$(\"#id".$widgetID."\").html(\"You cannot echo or print your content out\");";
	}
}
print "});
	</script>";
?>
<?php include("footer.php") ?>
        
     </div>

</body>
</html>
