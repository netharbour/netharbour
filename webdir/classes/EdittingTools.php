
<?
/*******************************Editting tools should extend from TOOLS**************************************/

class EdittingTools
{
	//Create a new tool
	public function createNewTools($names, $icons, $handler, $toolName='')
	{
		//if no tool name give default name
		if ($toolName == '')
		{
			$toolName = "Tools";	
		}
		
		//these are the existing icons
		$iconExists = array("add", "edit", "search", "stat", "line", "contact", "client", "person", "people", "device", "location", "industry", "room", "service", "home", "plugin", "tool", "filter", "delete", "report", "graph", "graphPrev", "list");
		
		$form = '';
		$form .= "<div id=\"controlBar\">
            	<ul>
				<li><h2 class=\"controlTools\" icon=\"tool\">".$toolName."</h2>
                <ul>";
		
		//implement the tool menus
		foreach($names as $index=>$iconName)
		{
			//check if it's an url or an existing php file, if it is implement the handler url
			if(preg_match("/^http\:\/\/./i", $handler[$index]) || preg_match("/^[a-z]+\.php/i", $handler[$index]) )
			{$curHandler = "onclick=\"window.location.href='".$handler[$index]."'\"";}
			else {$curHandler = "onclick=\"".$handler[$index]."\"";}
			
			//check if the icon exists in the icon array, if it dones't put it as a url background
			if (in_array($icons[$index], $iconExists)){$fontStyle = "icon='".$icons[$index]."'";}
			else {$fontStyle = "style='background:transparent url(".$icons[$index].") no-repeat top left;'";}
			
			//check if there's tool tips, if there is, apply the tool tip
			if (preg_match("/\.tip\./", $iconName))
			{
				$nameTipSplit = explode(".tip.", $iconName, 2);
				$iconName = $nameTipSplit[0];
				$tip = $nameTipSplit[1];
				
				if(isset($handler[$index])) {
					$form .= "<a class='tooltip' title='".$tip."'><li class='menuItem' ".$curHandler."><font ".$fontStyle."><img src='icons/Info.png' style='width:20%; height:20%;'>".$iconName."</font></li></a>";
				}
				//or else just implement the menu	
				else
				{$form .= "<li class='menuItem' ".$curHandler."><font ".$fontStyle.">".$iconName."</font></li>";}
			}
			//or else just implement the menu
			else
			{
				if(isset($handler[$index])) {$form .= "<li class='menuItem' ".$curHandler."><font ".$fontStyle.">".$iconName."</font></li>";}
				else
				{$form .= "<li class='menuItem' onclick=\"\"><font ".$fontStyle.">".$iconName."</font></li>";}
			}
		}
		
		$form .= "</ul>
				</li>
				</ul>
				</div>";
		
		return $form;
	}
	
	//create new button tabs
	public function createNewButtons($names, $div, $page)
	{
		$buttons = '';
		//create the tab buttons
		foreach ($names as $id => $value)
		{
			//check if this tab is the first tab, if it is give  it the green tab
			if (preg_match("/\.first\./", $value))
			{
				$value = str_replace(".first.", "", $value);
				$buttons .= "<input type='button' id='firstItem' style='background-image:url(icons/buttongreen.png);' class='ajaxBut' name='".$value."' onClick=\"return LoadPage('".$page[$id]."', '".$div."'), addButToArray(this), changeBut(this);\" value='".$value."'/>";
			}
			//or else it's an inactive tab
			else
			{
				$buttons .= "<input type='button' class='ajaxBut' name='".$value."' onClick=\"return LoadPage('".$page[$id]."', '".$div."'), addButToArray(this), changeBut(this);\" value='".$value."'/>";
			}
		}
		return $buttons;
	}
	
	//create a modal activator
	public function createNewModal($names, $icons, $modalID, $toolName='')
	{
		//if no name is given, give the name the default tool name
		if ($toolName == '')
		{
			$toolName = "Tools";	
		}
		
		//existing array of icons
		$iconExists = array("add", "edit", "search", "stat", "line", "contact", "client", "person", "people", "device", "location", "industry", "room", "service", "home", "plugin", "tool", "filter", "delete", "report", "graph", "graphPrev", "list");
		
		$modal = '';
		$modal .= "<div id=\"controlBar\" style=\"clear:left;\">
            	<ul>
				<li><h2 class=\"controlTools\" icon=\"tool\">".$toolName."</h2>
                	<ul>";
					
		//create the menus
		foreach($names as $index=>$iconName)
		{
			//if the icon exists give it, or else set it as a url background
			if (in_array($icons[$index], $iconExists)){$fontStyle = "icon='".$icons[$index]."'";}
			else {$fontStyle = "style='background:transparent url(".$icons[$index].") no-repeat top left;'";}
			
			//check if there's a tool tip, if so implement the tool tip
			if (preg_match("/\.tip\./", $iconName))
			{
				$nameTipSplit = explode(".tip.", $iconName, 2);
				$iconName = $nameTipSplit[0];
				$tip = $nameTipSplit[1];
				
				$modal .= "<a class='tooltip' title='".$tip."'><li class='menuItem' ".$curHandler."><a name=modal href='#".$modalID[$index]."'><font ".$fontStyle."><img src='icons/Info.png' style='width:20%; height:20%;'>".$iconName."</font></a></li></a>";
			}
			//or else just implement the default
			else
			{
				$modal .= "<li class='menuItem' onclick=\"\"><font ".$fontStyle."><a name=modal href='#".$modalID[$index]."'>".$iconName."</font></a></li>";
			}
		}
		
		$modal .= "</ul>
				</li>
				</ul>
				</div>";
		
		return $modal;
	}
	
	//create a filter bar
	public function createNewFilters($list="")
	{
		$filter = '';
		//check if the list of filter is an array
		if (is_array($list))
		{
			//if it is start make the javascript for the filter
			$filter .= "<script language='javascript'>
						$(function() {";
			foreach ($list as $id => $value)
			{
				//check if it's an array in an array
				if (is_array($value))
				{
					//fi it is make the value based on the array, click to hide and show
					foreach ($value as $fID => $fValue)
					{
						$filter .="$('#hide".$fValue."').click(function(){
						if ($('.".$fValue."').is(':hidden') && $('#hide".$fValue."').attr('checked'))
						{
							$('.".$fValue."').show();
						}
						else
						{
							$('.".$fValue."').hide();
						}
						})
						
						"; 
					}
				}
				//or else just make the value with the first array, click to hide and show
				else
				{
					$filter .="$('#hide".$value."').click(function(){
						if ($('.".$value."').is(':hidden') && $('#hide".$value."').attr('checked'))
						{
							$('.".$value."').show();
						}
						else
						{
							$('.".$value."').hide();
						}
						})
					
						";
				}
			}
			$filter .= "});
						</script>";
		}
		
		//make the filter menu
		$filter .= "<div id=\"controlBar\">
            	<ul>
				<li><h2 class=\"controlTools\" icon=\"filter\">Filter</h2>
                <ul>";
		
		//check if the list is an array again, if it is create the filters
		if (is_array($list))
		{
			foreach ($list as $id => $value)
			{
				//if it's an array in array, create the filters based on the array in array
				if (is_array($value))
				{
					$filter .= "<li class='menuItem'>".$id."<ul>";
					foreach ($value as $fID => $fValue)
					{
						$filter.= "<li class='menuItem' style='width:100%;'><input name='".$fValue."' type='checkbox' id='hide".$fValue."' checked />".$fValue."</li>";
					}
					
					
					$filter .= "</ul></li>";
				}
				//or else just make the first array
				else
				{
					$filter .= "<li class='menuItem' style='width:100%;'><input id='hide".$value."' name='".$value."' type='checkbox' checked />".$id."</li>";
				}
			}
		}
		
		//create the keyword filter bar
		$filter .= "<form name='filterForm' method='POST'>";
		
		$filter .= "<li class='menuItem'>Keyword
				  	<div id='search'>
					<input type='text' name='filter' value='' id='filter' style='width:100%;' />
      				</div>
				  	</li>";
		$filter	.= "</form>";
				  
		$filter .= "</ul>
            		</li>
            		</ul>
					</div>";
			
		return $filter;
	}
	
	/*Taken out because createNewFilter does this as well
	public function createFilterBar()
	{
		$filter = '';
		$filter .= "<div id=\"controlBar\">
            	<ul>
				<li><h2 class=\"controlTools\" icon=\"filter\">Filter</h2>
                <ul>";
		
		
		$filter .= "<form name='filterForm' method='POST'>
				  <li class='menuItem'>Keyword
				  	<div id='search'>
					<input type='text' name='filter' value='' id='filter' style='width:100%;' />
      				</div>
				  </li>
				 </form>
                </ul>
			</div>";
			
		return $filter;
	}
	*/
	
	//a calculator function to calculat things
	public function calculator($function, $value)
	{
		//what kind of function will it calculate
		switch ($function)
		{
			//convert the number into bits
			case "convertBits":
			$bitsIndex = 0;
			//divide until it cannot be divided
			while (($value/1000)>=1)
			{
				//converted to Megabits
				$value /= 1000;
				$bitsIndex++;				
			}
			//give it the correct measurement based on the division
			$value = round($value, 3);
			switch ($bitsIndex)
			{
				case 0:
				return $value."bps";
				
				case 1:
				return $value."Kbps";
				break;
				
				case 2:
				return $value."Mbps";
				break;
				
				case 3:
				return $value."Gbps";
				break;
				
				case 4:
				return $value."Tbps";
				break;
				
				default: return "This value is too BIG";
				
			}
			break;
			
			// convert into percent
			case "convertPercent":
			$value*=100;
			$value = round($value, 3);			
			return $value."%";
			break;			
			
		}
		
	}
	
	//create a navigation menu
	public function createNavigation($curPageNum, $numPages)
	{
		$nav = '';
		//check the page number if it exists
		if(isset($_GET['pageNum']))
		{
			$curPageNum = $_GET['pageNum'];
		}
		
		//create the page numbers
		if (($curPageNum - 3) > 1)
		{
			//add it into the site
			$site = $_SERVER['REQUEST_URI'];
			if(strpos($site, "pageNum=") !== false)
			{
				$site = str_replace("mode=", "neglect=", $site);
				$site = str_replace("pageNum=".$curPageNum, "pageNum=1", $site);
				$nav .= "<a href='".$site."' onclick=\"handleEvent('".$site."')\">First </a>";
				$nav .= "...";
			}
			//display First page variable with the link
			else if (strpos($site, ".php?") !== false)
			{
				$nav .= "<a href='".$site."&pageNum=1' onclick=\"handleEvent('".$site."&pageNum=1')\">First </a>";
				$nav .= "...";
			}
			else
			{
				$nav .= "<a href='".$site."?pageNum=1' onclick=\"handleEvent('".$site."?pageNum=1')\">First </a>";
				$nav .= "...";
			}
		}
		
		//display the other pages
		for ($index=1; $index <= $numPages; $index++)
		{
			//check which page the current page is and display it.
			if($curPageNum==$index)
			{
				$nav .= "| <u><b style='font-size:10pt;'>".$index."</b></u> | ";
			}
			//start the limit for how many pages are allowed to be displayed in the navigation.
			else {
				$limitStart = $curPageNum - 3;
				
				//make sure it does not go below 1
				if($limitStart<1)
				{
					$limitStart=1;
				}
				
				$limitEnd = $limitStart + 7;
				
				//make sure it does not go beyond the last page
				if($limitEnd >= $numPages)
				{
					$limitStart = $numPages - 7;
				}
				//limit is 7 page being displayed
				
				//display all other pages
				if($index < $limitEnd && $index >= $limitStart)
				{
					$site = $_SERVER['REQUEST_URI'];
					//make sure it does so error check
					if(strpos($site, "pageNum=") !== false)
					{
						$site = str_replace("mode=", "neglect=", $site);
						$site = str_replace("pageNum=".$curPageNum, "pageNum=".$index, $site);
						$nav .= "<a href='".$site."' onclick=\"handleEvent('".$site."')\">".$index."</a> ";
					}
					else if (strpos($site, ".php?") !== false)
					{
						$nav .= "<a href='".$site."&pageNum=".$index."' onclick=\"handleEvent('".$site."&pageNum=".$index."')\">".$index."</a> ";
					}
					else
					{
						$nav .= "<a href='".$site."?pageNum=".$index."' onclick=\"handleEvent('".$site."?pageNum=".$index."')\">".$index."</a> ";
					}
				}
			}
		}
		
		//show the Last page variable so that they can navigate to the last page, if there's more than 3 pages after the current page displayed, show this variable
		if (($curPageNum + 3 ) < $numPages)
		{
			$nav .= "...";
			$site = $_SERVER['REQUEST_URI'];
			$site = str_replace("mode=", "neglect=", $site);
			$site = str_replace("pageNum=".$curPageNum, "pageNum=".$numPages, $site);
			$nav .= "<a href='".$site."' onclick=\"handleEvent('".$site."')\"> Last</a>";
		}
		
		return $nav;
	}
	
	//create a search bar
	public function createSearchBar($link, $options='')
	{
		$search = '';
		$search .= "<div id='search' style='margin-bottom:5px; float:left; clear:left;'>
				<form method='post' id='searchForm' action='".$link."'>";
				if ($options != '')
				{
					$search .= "<select name='options'>";
					foreach($options as $id=>$value)
					{
						$search .= "<option value='".$value."'>".$value."</option>";
					}
					$search .= "</select> ";
				}
		$search .= "<input type='text' name='keyword' id='keyword' />
				<input type='submit' name='search' value='search' />
				</form>
		  		</div>";
		return $search;
	}
}
?>

<script language="javascript">
//change buttons
function changeBut(but)
{
	 var curBut=but;
	 //figure out which id has the firstitem
	 var firstItem = document.getElementById('firstItem');
	 //give the first item the green button
	 if (firstItem != curBut)
	 {
	 	firstItem.style.backgroundImage='url(icons/buttonblue.png)';
	 }
	 for (var i = 0; i<allBut.length; i++)
	 {
	 	allBut[i].style.backgroundImage='url(icons/buttonblue.png)';
	 }
	 but.style.backgroundImage='url(icons/buttongreen.png)';
	 
}
//make an array of buttons
var allBut = new Array();

//store all button into the array
function addButToArray(but)
{
	
	var inArray = false;
	for (var i=0; i<allBut.length; i++)
	{
		if (allBut[i]==but)
		{inArray = true; break;}
	}
	if (!inArray){allBut.push(but);}
}
</script>
