<script type='text/javascript' src='js/table/common.js'></script>
<script type='text/javascript' src='js/table/css.js'></script>
<script type='text/javascript' src='js/table/standardista-table-sorting.js'></script>
<!--<script type='text/javascript' src='js/sorttable.js'></script>-->
<script type='text/javascript' src='js/mouseClicks.js'></script>

<?
/*****************FORM CREATION**************************************************************
Originally I want to extend this to another class called form. But because there are so little forms right now, it has not been done yet. For future reference, this class should be extended to another class called "Form" and the class name should be called "ClientForm'

New Fixes:
Generic values, more options, and better and easier functionality.
Modal Forms
handler forms
and more
*/
class Form
{
	//use the indexes to increment the arrays
	private $headingIndex = 0;
	private	$infoIndex=0;
	private	$infoCheck;
	private	$checkingIndex=0;
	
	//All the form variables are declared here
	private $formMethod = "POST";
	private $formAction = "";
	private $formName = "MyClassForm";
	private $update;
	private $updateText = "Update";
	private $add;
	private $addText = "Add New Value";
	private $width;
	private $titleWidth;
	private $rows;
	private $cols;
	private $heading = array();
	private $title = array();
	private $data = array();
	private $fieldType = array();
	private $filter = array();
	//private $custom = array();
	private $colsWidth;
	private $eHandler = array();
	private $mouseHandlerType;
	private $databaseKeys = array();
	private $sortable = false;
	
	//datepicker
	// Only include the JS stuff once:w
	private $included_datepicker_js = 0;

	//dropdowns
	private $type = array();
	private $typeIndex = 0;
	private $contacts = array();
	
	//dynamics
	private $modalID;
	private $newModalID;
	private $tooltip = array();
	
	private $first;
	
	//constructor, row is meaningless right now
	function __construct($row = "", $col =0) {
		$this->rows = $row;
		$this->cols = $col;
	}
	
	//sets add post values
	function setAddValue($add){$this->add = $add;}
	
	//sets the text of the button
	function setAddText($addText){$this->addText = $addText;}
	
	//sets the update Method  values (get or post)
	function setMethod($formMethod){$this->formMethod = $formMethod;}

	//sets the update the ACTION=URL attribute in form
	function setAction($formAction){$this->formAction = $formAction;}

	//sets form name attribute in form
	function setFormName($formName){$this->formName = $formName;}

	//sets the update post values
	function setUpdateValue($update){$this->update = $update;}
	
	//sets the text of the button
	function setUpdateText($updateText){$this->updateText = $updateText;}
	
	//sets the width of the table
	function setTableWidth($width){$this->width = $width;}
	
	//sets the width of the title
	function setTitleWidth($width){$this->titleWidth = $width;}
	
	//sets the headings of the form
	function setHeadings($headingName){$this->heading=$headingName;}
	
	//sets the titles of the form
	function setTitles($titleName){$this->title = $titleName;}
	
	//sets the data of the form
	function setData($dataInfo){$this->data = $dataInfo;}
	
	//sets the keys used for the post value data
	function setDatabase($databaseInfo){$this->databaseKeys = $databaseInfo;}
	
	//sets the field type for these datas
	function setFieldType($fieldType){$this->fieldType = $fieldType;}
		
	//function setCustomFieldType($custom){$this->custom = $custom;}
	
	//sets the rows, but rows are currently useless right now
	function setRows($row){$this->rows = $row;}
	
	//sets the width of each column
	function setColsWidth($colWidth){$this->colsWidth = $colWidth;}
	
	//sets the amount of columns
	function setCols($col){$this->cols = $col;}
	
	//sets the event handler for when a row is clicked
	function setEventHandler($handler){$this->eHandler = $handler;}
	
	//sets if the table is sortable
	function setSortable($sortable){
		if ($sortable==true){$this->sortable = "sortable";}
		else {$this->sortable = false;}			
	}
	
	//sets the drop down menu list
	function setType($type){
		$this->type[$this->typeIndex] = $type;
		$this->typeIndex++;}
	
	//sets what kind of mouse event for the mouse event handler
	function setMouseHandlerType($mode){
		$value;
		switch ($mode)
		{
			case 1:
			$value = "onmouseover";
			break;
			
			case 2:
			$value = "onmouseout";
			break;
			
			case 3:
			$value = "link";
			break;
			
			default:
			$value = "onclick";
			break;
		}
		$this->mouseHandlerType = $value;
	}
	
	//sets the filter values
	function setFilter($filter){$this->filter = $filter;}
	
	//sets the modal window id
	function setModalID($id) {$this->modalID = $id;}
	
	//sets the modal windo id for the new modal window
	function setNewModalID($id) {$this->newModalID = $id;}
	
	//sets the table to be the first item on the row
	function setFirst($first) {$this->first = $first;}
	
	//function setTooltip($tip) {$this->tooltip = $tip;}
	
	/****************************************GET FUNCTIONS*************************************/
	
	//gets the update post values
	function getUpdateValue(){return $this->update;}
	
	//gets the update text
	function getUpdateText(){return $this->updateText;}
	
	//gets the add post values
	function getAddValue(){return $this->add;}
	
	//gets the add text
	function getAddText(){return $this->addText;}
	
	//gets the table width
	function getTableWidth(){
		if(isset($this->width))
		{return $this->width;}
		else {
			$this->width='1024px;';
			return $this->width;
		}
	}
	
	//gets the title width
	function getTitleWidth(){
		if(isset($this->titleWidth))
		{return $this->titleWidth;}
		else {
			$this->titleWidth='20%';
			return $this->titleWidth;
		}
	}
	
	//gets the amount of rows there are
	function getRows(){return $this->rows;}
	
	//gets the amount of columns there are
	function getCols(){return $this->cols;}
	
	//gets the width of each column
	function getColsWidth(){
		if (!isset($this->colsWidth))
		{$this->colsWidth = "auto";}
		return $this->colsWidth;}
	
	//gets the titles
	function getTitles(){return $this->title;}
	
	//gets the heading
	function getHeading(){return $this->heading;}
	
	//gets the data
	function getData(){return $this->data;}
	
	//gets the post values for the data
	function getDatabase(){return $this->databaseKeys;}
	
	//gets the field types
	function getFieldType(){return $this->fieldType;}
	
	//function getCustomFieldType(){return $this->custom;}
	
	//checks if the field is sortable or not
	function isSortable(){
		if ($this->sortable == "sortable"){return true;}
		else {return false;}	
	}
	
	//gets the different handlers
	function getEventHandler(){return $this->eHandler;}
	
	//gets the list of drop down menus
	function getType(){return $this->type;}
	
	//gets the mouse handler being used
	function getMouseHandlerType(){return $this->mouseHandlerType;}
	
	//gets the filter values
	function getFilter(){return $this->filter;}
	
	//gets the modal window ID
	function getModalID() {
		if (isset($this->modalID)) {return $this->modalID;}
		else{return "dialog";}
	}
	
	//gets the new modal wind ID
	function getNewModalID() {
		if (isset($this->newModalID)) {return $this->newModalID;}
		else{return "newDialog";}
	}
	
	//tells if the table is first or not
	function getFirst() {
		return $this->first;
	}
	
	//function getTooltip() {return $this->tooltip;}
	
	//make a form for showing
	function showForm($numHead =''){
		
		//declare the important variables
		$width = $this->getTableWidth();
		$titleWidth = $this->getTitleWidth();
		$colWidth = $this->getColsWidth();
		$first = $this->getFirst();
		$fieldType = $this->getFieldType();
		
		$form = "";
		
		/* Andree June 14 Disabled the form opening and closing below
			show form is always a table
			so we don't need the <form> statement
		*/

		//if first is true, then make the form start a new form in a new row below the previous item, else start it beside the previous item
		if ($first == true || !isset($first))
		{
			$form .= "<table class='".$this->sortable."' style='width:".$width."; clear:left;' id=\"sortDataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\">";
		}
		else
		{
			$form .= "<table class='".$this->sortable."' style='width:".$width."; clear:none;' id=\"sortDataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\">";
		}
		
		//number of headings
		if($numHead=="")
		{
			$numHead=1;
		}
		
		//start initializing the indexes here
		$index=0;
		$titleIndex=0;
		$dataValueIndex=0;
		
		//if it's sortable make the form sortable
		if ($this->isSortable())
		{
			for ($headIndex=0; $headIndex<$numHead; $headIndex++)
			{	
				$form .= "<thead>
						<tr>";
				$colspan = 0;
				//foreach ($this->heading as $index => $key)
				
				$tempIndex = $index;
				
				//calculates the ratio of headings for colspan values
				while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
				{	
					$index++; $colspan++;
				}
				
				$index = $tempIndex;
				$colspan = $this->cols - $colspan + 1;
				
				//make the colspan values
				while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
				{
					$form .= "<th colspan=\"$colspan\" align=\"left\">".$this->heading[$index]."</th>";
					$index++;
				}
				$form .= "</tr>
						</thead>";
						
				//foreach ($this->title as $index => $key)
				$form .= "<tbody>";
				
				//declare the handlers types
				$mouseHandlerType = $this->getMouseHandlerType();
				if(!isset($mouseHandlerType))
				{$mouseHandlerType='onclick';}
				
				//check if there are any filter values
				$filter = $this->getFilter();
				
				//create the title
				while($this->title[$titleIndex] != "*<break>*" && count($this->title) > $titleIndex && !empty($this->title))
				{
					//if the title is in an array, display all the values of the title array
					$title = $this->title[$titleIndex];
					if (is_array($title))
					{
						$curTitle = '';
						foreach ($title as $id => $value)
						{
							$curTitle .= $value . "<br/>";
						}
						$title = $curTitle;
					}
					
					//set the handler here, separate each row for a easier read by giving it odd or even values
					$handler = $this->getEventHandler();
					if($odd == 0)
					{
						$form .= "<tr ".$mouseHandlerType."=\"".$this->eHandler[$titleIndex]."\" class='".$filter[$titleIndex]."'>";
						$odd = 1;
					}
					else
					{
						$form .= "<tr class=\"odd ".$filter[$titleIndex]."\" ".$mouseHandlerType."=\"".$this->eHandler[$titleIndex]."\">";
						$odd = 0;
					}
					
					//if the handler type is "link" then split the title into sections because you want to display the id and the name for the graph link
					if($mouseHandlerType=='link')
					{
						if(preg_match("/devices\.php/i", $_SERVER['PHP_SELF']) || preg_match("/services\.php/i", $_SERVER['PHP_SELF']))
						{
							$titleList = explode('//', $title, 3);
							$titleName = $titleList[0];
							$interfaceID=$titleList[1];
							$deviceID=$titleList[2];
							//"graph.php?file=deviceid".$value->get_device_id()."_".$name."&titel=".$nameTitle."---Bits%20Per%20Second&type=traffic";
							//http://localhost:8888/BCNET/statistics.php?action=showGraphDetail&ID=3&interID=4514&active=up&type=traffic
								$form .= "<td class='title' style='width:".$titleWidth.";'><a href='statistics.php?action=showGraphDetail&ID=".$deviceID."&interID=".$interfaceID."&active=up&type=traffic' class='screenshot' title='Traffic' style='padding-left:30px;' icon='graphPrev' rel='".$handler[$titleIndex]."'>".$titleName."</a></td>";
						}
						//or else just be normal
						else {$form .= "<td class='title' style='width:".$titleWidth.";'><a href='#' style='cursor:default; padding-left:30px;' class='screenshot' title='Traffic' icon='graphPrev' rel='".$handler[$titleIndex]."'>".$title."</a></td>";}
					}
					
					//if there is a tool tip, give it a tool tip option
					else if(preg_match("/\.tip\./", $title))
					{
						$fullTitle = explode(".tip.", $title, 2);
						$title = $fullTitle[0];
						$tip = $fullTitle[1];
						$form .= "<td class='title'  style='width:".$titleWidth.";'><a class='tooltip' title='".$tip."'><img src='icons/Info.png' height='16' width='16'>".$title."</a></td>";
					}
					//or else just be normal
					else
					{
						$form .= "<td class='title' style='width:".$titleWidth.";'>".$title."</td>";	
					}
					
					//insert the data into the field
					for($dataIndex = 1; $dataIndex < $this->cols; $dataIndex++)
					{
						$data = $this->data[$dataValueIndex];
						if (is_array($data))
						{
							$curData = '';
							foreach ($data as $id => $value)
							{
								$curData .= $value . "<br/>";
							}
							$data = $curData;
						}
						//Quick fix for text area
						//if there is a modifier for the field type detect it
						if (!preg_match("/\\\./", $fieldType[$dataValueIndex]))
						{
							//split these modifiers from the title
							if (preg_match("/\.[a-z]+/i", $fieldType[$dataValueIndex]))
							{
								$splitField = explode(".", $fieldType[$dataValueIndex],2);
								$curFieldType = $splitField[0];
								$moreOption = $splitField[1];
								
								$options = explode(".", $moreOption);
							}
							else {
								$curFieldType = $fieldType[$dataValueIndex];
							}
							//make sure the periods that are slashed are unslashed
							$fieldType[$dataValueIndex] = str_replace("\.", ".", $fieldType[$dataValueIndex]);
						}
						
						//print_r($fieldType);
						//echo $dataValueIndex;
						//check what field tupes there are
						switch ($curFieldType)
						{
							//a text area field type
							case "text_area":
							$data = str_replace("<br />", "\n", $data);
							
							$form .= "<td class='info' style='width:".$colWidth."'>";
							
							//if there is a width and height modifier, detect it and set it
							if(isset($options))
							{
								foreach ($options as $id => $value)
								{
									if (preg_match("/width\:/", $value))
									{
										$tWidth = str_replace("width:", "", $value);
									}
									else if (preg_match("/height\:/", $value))
									{
										$tHeight = str_replace("height:", "", $value);
									}
								}
								
								//if not give them 100% values
								if (!isset($tWidth)) {$tWidth = "100%";}
								if (!isset($tHeight)) {$tHeight = "100%";}
								
								//create the form with the values
								$form .= "<textArea readonly style='width:".$tWidth."; height:".$tHeight."' rows=\"5\">".$data."</textarea>";
								
							}
							//or else just create the field with default height and width options
							else {
								$form .= "<textArea readonly style='width:100%;height:150px'>".$data."</textarea>";
							}
								
							$form .= "</td>";
							break;
							
							default:
							$form .= "<td class='info' style='width:".$colWidth."'>".$data."</td>";
							break;
						}
						
						$dataValueIndex++;
					}
					$form .= "</tr>";
					$titleIndex++;
				}
			
				//Quick Fix: if there are no titles then do the same with the amount of data
				if(empty($this->title) && count($this->data) > 0 )
				{
					$handlerIndex = 0;
					while(count($this->data) > $dataValueIndex && !empty($this->data))
					{
						//separate odd and even rows for easier read
						if($odd == 0)
						{
							$form .= "<tr ".$mouseHandlerType."=\"".$this->eHandler[$handlerIndex]."\" class='".$filter[$handlerIndex]."'>";
							$odd = 1;
						}
						else
						{
							$form .= "<tr class=\"odd ".$filter[$handlerIndex]."\" ".$mouseHandlerType."=\"".$this->eHandler[$handlerIndex]."\" >";
							$odd = 0;
						}
						$numCols = $this->cols;
						
						//make sure the data corresponds to the column of the table
						for($i=0; $i<$numCols; $i++)
						{
					
							//if the data is an array display the whole array of data
							$data = $this->data[$dataValueIndex];
							if (is_array($data))
							{
								$curData = '';
								foreach ($data as $id => $value)
								{
									$curData .= $value . "<br/>";
								}
								$data = $curData;
							}
							
							//if it's a link mouse handler type, it will take / and divide them into ids and statistics
							if($mouseHandlerType=='link')
							{
								if(preg_match("/devices.php/i", $_SERVER['PHP_SELF']) || preg_match("/services.php/i", $_SERVER['PHP_SELF']))
								{
									$dataList = explode('//', $data, 3);
									$dataName = $dataList[0];
									$interfaceID=$dataList[1];
									$deviceID=$dataList[2];
									//"graph.php?file=deviceid".$value->get_device_id()."_".$name."&titel=".$nameTitle."---Bits%20Per%20Second&type=traffic";
									//http://localhost:8888/BCNET/statistics.php?action=showGraphDetail&ID=3&interID=4514&active=up&type=traffic
										$form .= "<td class='title' style='width:".$colWidth.";'><a href='statistics.php?action=showGraphDetail&ID=".$deviceID."&interID=".$interfaceID."&active=up&type=traffic' class='screenshot' title='Traffic' style='padding-left:30px;' icon='graphPrev' rel='".$handler[$dataValueIndex]."'>".$dataName."</a></td>";
								}
								else {$form .= "<td class='title' style='width:".$colWidth.";'><a href='#' style='cursor:default; padding-left:30px;' class='screenshot' title='Traffic' icon='graphPrev' rel='".$handler[$dataValueIndex]."'>".$data."</a></td>";}
							}
							
							//the tool tip section
							else if(preg_match("/\.tip\./", $data))
							{
								$fulldata = explode(".tip.", $data, 2);
								$data = $fulldata[0];
								$tip = $fulldata[1];
								$form .= "<td class='title'  style='width:".$colWidth.";'><a class='tooltip' title='".$tip."'><img src='icons/Info.png' width='16' height='16'>".$data."</a></td>";
							}
							else
							{
								//Quick fix for text area
								//if there is a modifier for the field type detect it
								if (!preg_match("/\\\./", $fieldType[$dataValueIndex]))
								{
									//split these modifiers from the title
									if (preg_match("/\.[a-z]+/i", $fieldType[$dataValueIndex]))
									{
										$splitField = explode(".", $fieldType[$dataValueIndex],2);
										$curFieldType = $splitField[0];
										$moreOption = $splitField[1];
										
										$options = explode(".", $moreOption);
									}
									else {
										$curFieldType = $fieldType[$dataValueIndex];
									}
									//make sure the periods that are slashed are unslashed
									$fieldType[$dataValueIndex] = str_replace("\.", ".", $fieldType[$dataValueIndex]);
								}
								
								//check what field tupes there are
								switch ($curFieldType)
								{
									//a text area field type
									case "text_area":
									$data = str_replace("<br />", "\n", $data);
									
									$form .= "<td class='info' style='width:".$colWidth."'>";
									
									//if there is a width and height modifier, detect it and set it
									if(isset($options))
									{
										foreach ($options as $id => $value)
										{
											if (preg_match("/width\:/", $value))
											{
												$tWidth = str_replace("width:", "", $value);
											}
											else if (preg_match("/height\:/", $value))
											{
												$tHeight = str_replace("height:", "", $value);
											}
										}
										
										//if not give them 100% values
										if (!isset($tWidth)) {$tWidth = "100%";}
										if (!isset($tHeight)) {$tHeight = "100%";}
										
										//create the form with the values
										$form .= "<textArea readonly style='width:".$tWidth."; height:".$tHeight."' rows=\"5\">".$data."</textarea>";
										
									}
									//or else just create the field with default height and width options
									else {
										$form .= "<textArea readonly style='width:100%;height:150px'>".$data."</textarea>";
									}
										
									$form .= "</td>";
									break;
									
									default:
									$form .= "<td class='info' style='width:".$colWidth."'>".$data."</td>";
									break;
								}
							}
							
							
							$dataValueIndex++;
						}
						$handlerIndex++;
						$form .= "</tr>";
					}
				}
				//increment everything and repeat
				$titleIndex++;
				$index++;
				$form .= "</tbody>";
			}
			
		}
		//if its not sortable do the same but make them unsortable
		else 
		{
			for ($headIndex=0; $headIndex<$numHead; $headIndex++)
			{	
				$form .= "<tr class='form'>";
				$colspan = 0;
				//foreach ($this->heading as $index => $key)
				
				$tempIndex = $index;
				
				//calculate for col span
				while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
				{	
					$index++; $colspan++;
				}
				
				$index = $tempIndex;
				$colspan = $this->cols - $colspan + 1;
				
				//put the colspan
				while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
				{
					$form .= "<th colspan=\"".$colspan."\" align=\"left\">".$this->heading[$index]."</th>";
					$index++;
				}
				$form .= "</tr>";
						
				//make the title
				//foreach ($this->title as $index => $key)
				while($this->title[$titleIndex] != "*<break>*" && count($this->title) > $titleIndex)
				{
					//if title is an array, display the whole array of title
					$title = $this->title[$titleIndex];
					if (is_array($title))
					{
						$curTitle = '';
						foreach ($title as $id => $value)
						{
							$curTitle .= $value . "<br/>";
						}
						$title = $curTitle;
					}
					
					//if there's a tooltip display it
					$form .= "<tr class='form'>";
					if(preg_match("/\.tip\./", $title))
					{
						$fullTitle = explode(".tip.", $title, 2);
						$title = $fullTitle[0];
						$tip = $fullTitle[1];
						$form .= "<td class='title'  style='width:".$titleWidth.";'><a class='tooltip' title='".$tip."'><img src='icons/Info.png' height='16' width='16'><b>".$title."</b></a></td>";
					}
					else {
						$form .= "<td class='title'  style='width:".$titleWidth.";'><b>".$title."</b></td>";
					}
					
					//display the datas
					for($dataIndex = 1; $dataIndex < $this->cols; $dataIndex++)
					{
						//if the data is an array display the array of data
						$data = $this->data[$dataValueIndex];
						if (is_array($data))
						{
							$curData = '';
							foreach ($data as $id => $value)
							{
								$curData .= $value . "<br/>";
							}
							$data = $curData;
						}
						//Quick fix for text area
						//if there is a modifier for the field type detect it
						if (!preg_match("/\\\./", $fieldType[$dataValueIndex]))
						{
							//split these modifiers from the title
							if (preg_match("/\.[a-z]+/i", $fieldType[$dataValueIndex]))
							{
								$splitField = explode(".", $fieldType[$dataValueIndex],2);
								$curFieldType = $splitField[0];
								$moreOption = $splitField[1];
										
								$options = explode(".", $moreOption);
							}
							else {
								$curFieldType = $fieldType[$dataValueIndex];								}
									
								//make sure the periods that are slashed are unslashed
								$fieldType[$dataValueIndex] = str_replace("\.", ".", $fieldType[$dataValueIndex]);
							}
								
							//check what field tupes there are
							switch ($curFieldType)
							{
								//a text area field type
								case "text_area":
								$data = str_replace("<br />", "\n", $data);
									
								$form .= "<td class='info' style='width:".$colWidth."'>";
									
								//if there is a width and height modifier, detect it and set it
								if(isset($options))
								{
									foreach ($options as $id => $value)
									{
										if (preg_match("/width\:/", $value))
										{
											$tWidth = str_replace("width:", "", $value);
										}
										else if (preg_match("/height\:/", $value))
										{
											$tHeight = str_replace("height:", "", $value);
										}
									}
									
									//if not give them 100% values
									if (!isset($tWidth)) {$tWidth = "100%";}
									if (!isset($tHeight)) {$tHeight = "100%";}
									
									//create the form with the values
									$form .= "<textArea readonly style='width:".$tWidth."; height:".$tHeight."' rows=\"5\">".$data."</textarea>";
									
								}
								//or else just create the field with default height and width options
								else {
									$form .= "<textArea readonly style='width:100%;height:150px' >".$data."</textarea>";
								}
									
								$form .= "</td>";
								break;
									
								default:
								$form .= "<td class='info' style='width:".$colWidth."'>".$data."</td>";
								break;
							}
						$dataValueIndex++;
					}
					$form .= "</tr>";
					$titleIndex++;
				}
				$titleIndex++;
				$index++;
				$form .= "</tbody>";				
			}
		}
		$form .= "</table>";
				//</form>";
		
		return $form;
	}
	
	//make an editable form
	function editForm($numHead =''){
		
		//declare the important values
		$width = $this->getTableWidth();
		$titleWidth = $this->getTitleWidth();
		$fieldType = $this->getFieldType();
		$this->typeIndex=0;

		$form = "";

		// This is to make sure we only include the 
		// Date picker stuff in js/datepicker once	
		// This includes, css and js files
		foreach ($fieldType as $key => $value) {
			if ($value == 'date_picker') {	
				#$form .= $this->include_datepicker_scripts();
			}
		}
		$form = "";
		// Adding a generic Javascript onsubmit="return validateForm()" Alvin Mar 2014
		$form .= "<form id=\"dataForm\" method=\"$this->formMethod\" ACTION=\"$this->formAction\" name=\"$this->formName\" onsubmit=\"return validateForm()\">
				<table id=\"dataTable\" style='width:".$width.";' cellspacing=\"0\" cellpadding=\"0\" border=\"1\">";
			
		if($numHead=="")
		{
			$numHead=1;
		}		
		
		//declare the indexes
		$index=0;
		$titleIndex=0;
		$dataValueIndex=0;
		
		//create the headings
		for ($headIndex=0; $headIndex<$numHead; $headIndex++)
		{
			$form .= "<tr class='form'>";
			$colspan = 0;
			//foreach ($this->heading as $index => $key)
			
			$tempIndex = $index;
			
			//get the colspan value
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{	
				$index++; $colspan++;
			}
			
			$index = $tempIndex;
			$colspan = $this->cols - $colspan + 1;
			
			//implement the colspan values
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{
				$form .= "<th colspan=\"$colspan\" align=\"left\">".$this->heading[$index]."</th>";
				$index++;
			}
			$form .= "</tr>";
			
			//create the titles
			while($this->title[$titleIndex] != "*<break>*" && count($this->title) > $titleIndex)
			{
				$form .= "<tr class='form'>";
				
				$title = $this->title[$titleIndex];
				
				//if the title is an array, display the array of title
				if (is_array($title))
				{
					$curTitle = '';
					foreach ($title as $id => $value)
					{
						$curTitle .= $value . "<br/>";
					}
					$title = $curTitle;
				}
				
				//display the tooltip if there is a tool tip
				if(preg_match("/\.tip\./", $this->title[$titleIndex]) && $fieldType[$dataValueIndex] != 'hidden')
				{
					$fullTitle = explode(".tip.", $this->title[$titleIndex], 2);
					$title = $fullTitle[0];
					$tip = $fullTitle[1];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><a class='tooltip' title='".$tip."'><img src='icons/Info.png' height='16' width='16'><b>".$title."</b></a></td>";
				}
				//do not make a row if the title is hidden, or else we can make a row
				else if ($fieldType[$dataValueIndex] != 'hidden')
				{
					$title = $this->title[$titleIndex];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><b>".$title."</b></td>";
				}
				
				//create the data
				for($dataIndex = 1; $dataIndex < $this->cols; $dataIndex++)
				{
					//if the data is an array, display the array of data
					$data = $this->data[$dataValueIndex];
					if (is_array($data))
					{
						$curData = '';
						foreach ($data as $id => $value)
						{
							$curData .= $value . "<br/>";
						}
						$data = $curData;
					}
					$lowcaseTitle = strtolower($title);
					
					//if there is a modifier for the field type detect it
					if (!preg_match("/\\\./", $fieldType[$dataValueIndex]))
					{
						//split these modifiers from the title
						if (preg_match("/\.[a-z]+/i", $fieldType[$dataValueIndex]))
						{
							$splitField = explode(".", $fieldType[$dataValueIndex],2);
							$curFieldType = $splitField[0];
							$moreOption = $splitField[1];
							
							$options = explode(".", $moreOption);
						}
						else {
							$curFieldType = $fieldType[$dataValueIndex];
						}
						//make sure the periods that are slashed are unslashed
						$fieldType[$dataValueIndex] = str_replace("\.", ".", $fieldType[$dataValueIndex]);
					}
					
					//check what field tupes there are
					switch ($curFieldType)
					{
						//a text area field type
						case "text_area":
						$data = str_replace("<br />", "\n", $data);
						
						$form .= "<td class='info'>";
						
						//if there is a width and height modifier, detect it and set it
						if(isset($options))
						{
							foreach ($options as $id => $value)
							{
								if (preg_match("/width\:/", $value))
								{
									$tWidth = str_replace("width:", "", $value);
								}
								else if (preg_match("/height\:/", $value))
								{
									$tHeight = str_replace("height:", "", $value);
								}
							}
							
							//if not give them 100% values
							if (!isset($tWidth)) {$tWidth = "100%";}
							if (!isset($tHeight)) {$tHeight = "100%";}
							
							//create the form with the values
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='width:".$tWidth."; height:".$tHeight."' rows=\"5\">".$data."</textarea>";
							
						}
						//or else just create the field with default height and width options
						else {
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='height:150px'>".$data."</textarea>";
						}
							
						$form .= "</td>";
						break;
						
						//if the value is hidden create a hidden input
						case "hidden":
						$form .= "<input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>";
						break;
						
						//if the value is static, (not editable), give a static value
						case "static":
						$form .= "<td class='info'><input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;

						case "password":
						$form .= "<td class='info'><input type='password' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;
						
						// password with autocomplete="off" so browsers don't save this
						case "password_autocomplete_off":
						$form .= "<td class='info'><input type='password' autocomplete='off' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;
						//if the value is a drop down menu
						case "drop_down":
						$form .= "<td class='info'>
							<div id='dropField'>";
							//get the drop down list
							$types = $this->getType();
							
							//detect for the handler options
							if(isset($moreOption))
							{
								//create the drop down menu with the handler options
								if (preg_match("/handler\:/", $moreOption))
								{
									$curAction = str_replace("handler:", "", $moreOption);
									$form .= $this->createDropDown($types[$this->typeIndex], $data, $this->databaseKeys[$dataValueIndex], $curAction);
								}
								//or else just create the drop down
								else {
									$form .= $this->createDropDown($types[$this->typeIndex], $data, $this->databaseKeys[$dataValueIndex]);
								}
							}
							//if there are not options create the drop down normally
							else {
								$form .= $this->createDropDown($types[$this->typeIndex], $data, $this->databaseKeys[$dataValueIndex]);
							}
							
							$form .= "</div>
							</td>";
							$this->typeIndex++;
						break;
						
						//NEEDS TO BE MODIFIED TO ACCEPT ANY VALUES
						//radio buttons
						case "radio":
						$form .= "<td class='info'>";
						if ($data == 'Yes' || $data == 'Tagged' || $data == 'True')
							{
								$form .= "<input checked type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='1'>Yes</input>
								<input type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='0'>No</input>";
								
							}
							else
							{	
								$form .= "<input type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='1'>Yes</input>
								<input checked selected type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='0'>No</input>";
							}	
						$form .= "</td>";
						break;


						//date picker 
						case "date_picker":
						$form .= "<td class='info'>";
						$datepick_id = $this->databaseKeys[$dataValueIndex];
						$form .= $this->render_datepicker($datepick_id,$data);
						$form .= "<input name='".$this->databaseKeys[$dataValueIndex]."' id='".$this->databaseKeys[$dataValueIndex]."' class=\"$datepick_id\" />";
						$form .= "</td>";
						break;
						
						
						//a custom field
						case "custom":
						$form .= "<td class='info'>";
						$form .= $data;
						$form .= "</td>";
						break;
						
						//default is a text input
						default:
						$form .= "<td class='info'><input name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"".$data."\" type=\"text\" maxChar=\"250\"></td>";
						break;						
					}

					$dataValueIndex++;
				}
				$form .= "</tr>";
				$titleIndex++;
			}
			$titleIndex++;
			$index++;
		}
		$update = $this->getUpdateValue();
		
		//check if a post updat value is here, if it is, use it if not use the default
		if(isset($update))
		{
			if (isset($_GET['portID']))
			{
				$form .= "<tr class='normal'><td class='info'><input type='hidden' name='portID' value='$_GET[portID]'/>
				<input name='$update' id='update' type='submit' value='".$this->updateText."'></td></tr>";
			}
			else{
				$form .= "<tr class='normal'><td class='info'><input name='".$update."' id='update' type='submit' value='".$this->updateText."'></td></tr>";
			}
		}
		else {
			$form .= "<tr class='normal'><td class='info'><input name='updateInfo' id='update' type='submit' value='".$this->updateText."'></td></tr>";
		}
		
		$form .= "</table>
				</form>";
		
		return $form;
	}
	
	//make a new form
	function newForm($numHead =''){
	
		//declare all important variables
		$width = $this->getTableWidth();
		$titleWidth = $this->getTitleWidth();
		$fieldType = $this->getFieldType();
		$this->typeIndex=0;
		
		$form = "";
		//create a form first
		$form .= "<form id=\"dataForm\" method=\"$this->formMethod\" ACTION=\"$this->formAction\" name=\"$this->formName\">
				<table id=\"newTable\" style='width:".$width.";' cellspacing=\"0\" cellpadding=\"0\" border=\"1\">";
			
		if($numHead=="")
		{
			$numHead=1;
		}		
		
		//declare the indexes
		$index=0;
		$titleIndex=0;
		$dataValueIndex=0;
		
		//create the headings
		for ($headIndex=0; $headIndex<$numHead; $headIndex++)
		{	
			$form .= "<tr class='form'>";
			$colspan = 0;
			//foreach ($this->heading as $index => $key)
			
			$tempIndex = $index;
			
			//get the colspan value
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{	
				$index++; $colspan++;
			}
			
			$index = $tempIndex;
			$colspan = $this->cols - $colspan + 1;
			
			//apply the colspan values
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{
				$form .= "<th colspan=\"$colspan\" align=\"left\">".$this->heading[$index]."</th>";
				$index++;
			}
			$form .= "</tr>";
			//foreach ($this->title as $index => $key)
			
			//create the titles
			while($this->title[$titleIndex] != "*<break>*" && count($this->title) > $titleIndex)
			{
				//check for tool tip, if the field type is hidden don't create the row
				$form .= "<tr class='form'>";			
				if(preg_match("/\.tip\./", $this->title[$titleIndex]) && $fieldType[$dataValueIndex] != 'hidden')
				{
					$fullTitle = explode(".tip.", $this->title[$titleIndex], 2);
					$title = $fullTitle[0];
					$tip = $fullTitle[1];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><a class='tooltip' title='".$tip."'><img src='icons/Info.png' height='16' width='16'><b>".$title."</b></a></td>";
				}
				else if ($fieldType[$dataValueIndex] != 'hidden')
				{
					$title = $this->title[$titleIndex];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><b>".$title."</b></td>";
				}
				
				//create the data
				for($dataIndex = 1; $dataIndex < $this->cols; $dataIndex++)
				{
					$lowcaseTitle = strtolower($title);

					//if the data is an array, display the array of data
					$data = $this->data[$dataValueIndex];
  					if (is_array($data)) {
						$curData = '';
						foreach ($data as $id => $value) {
							$curData .= $value . "<br/>";
						}
						$data = $curData;
					}

					
					//check for modifiers
					if (!preg_match("/\\\./", $fieldType[$dataValueIndex]))
					{
						$fieldType[$dataValueIndex] = str_replace("\.", ".", $fieldType[$dataValueIndex]);
						if (preg_match("/\.[a-z]+/i", $fieldType[$dataValueIndex]))
						{
							$splitField = explode(".", $fieldType[$dataValueIndex],2);
							$curFieldType = $splitField[0];
							$moreOption = $splitField[1];
							
							$options = explode(".", $moreOption);
						}
						else {
							$curFieldType = $fieldType[$dataValueIndex];
						}
					}
					
					//check field type
					switch ($curFieldType)
					{
						//text area field type
						case "text_area":
						$data = $this->data[$dataValueIndex];
						$data = str_replace("<br />", "\n", $data);
						
						$form .= "<td class='info'>";
						
						//insert modifiers if there are any
						if(isset($options))
						{
							foreach ($options as $id => $value)
							{
								if (preg_match("/width\:/", $value))
								{
									$tWidth = str_replace("width:", "", $value);
								}
								else if (preg_match("/height\:/", $value))
								{
									$tHeight = str_replace("height:", "", $value);
								}
							}
							
							if (!isset($tWidth)) {$tWidth = "100%";}
							if (!isset($tHeight)) {$tHeight = "100%";}
							
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='width:".$tWidth."; height:".$tHeight."' rows=\"5\">".$this->data[$dataValueIndex]."</textarea>";
							
						}
						//else become a normal field type
						else {
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='height:150px'>".$this->data[$dataValueIndex]."</textarea>";
						}
							
						$form .= "</td>";
						break;
						
						//hidden field
						case "hidden":
						$form .= "<input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>";
						break;

						//password field
						case "password":
						$form .= "<td class='info'><input type='password' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$this->data[$dataValueIndex]."</td>";
						break;
						
						// password with autocomplete="off" so browsers don't save this
						case "password_autocomplete_off":
						$form .= "<td class='info'><input type='password' autocomplete='off' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;
						
						
						//static field
						case "static":
						$form .= "<td class='info'><input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$this->data[$dataValueIndex]."</td>";
						break;
						
						//drop down menu
						case "drop_down":
						$form .= "<td class='info'>
							<div id='dropField'>";
							
							//get the list and check for modifiers and apply them if there are any
							$types = $this->getType();
							if(isset($moreOption))
							{
								if (preg_match("/handler\:/", $moreOption))
								{
									$curAction = str_replace("handler:", "", $moreOption);
									$form .= $this->createDropDown($types[$this->typeIndex], "", $this->databaseKeys[$dataValueIndex], $curAction);
								}
								else {
									$form .= $this->createDropDown($types[$this->typeIndex], "", $this->databaseKeys[$dataValueIndex]);
								}
							}
							//else make the drop down menu
							else {
								$form .= $this->createDropDown($types[$this->typeIndex], $data, $this->databaseKeys[$dataValueIndex]);
							}
							
							$form .= "</div>
							</td>";
							$this->typeIndex++;
						break;
						
						//NEEDS TO BE MODIFIED TO ACCEPT ANY VALUES
						//radio button
						case "radio":
						$form .= "<td class='info'>";
						$form .= "<input checked type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='1'>Yes</input>
						<input type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='0'>No</input>";
						$form .= "</td>";
						break;

						//date picker 
						case "date_picker":
						$form .= "<td class='info'>";
						$datepick_id = $this->databaseKeys[$dataValueIndex];
						$form .= $this->render_datepicker($datepick_id,$data);
						$form .= "<input name='".$this->databaseKeys[$dataValueIndex]."' id='".$this->databaseKeys[$dataValueIndex]."' class=\"$datepick_id\" />";
						$form .= "</td>";
						break;
						
						
						
						//custom field
						case "custom":
						$form .= "<td class='info'>";
						$form .= $this->data[$dataValueIndex];
						$form .= "</td>";
						break;
						
						//default is a text input
						default:
						$form .= "<td class='info'><input name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"\" type=\"text\" maxChar=\"250\"></td>";
						break;						
					}
					
					$dataValueIndex++;
				}
				$form .= "</tr>";
				$titleIndex++;
			}
			$titleIndex++;
			$index++;
		}
		
		//check for the post add value, if there are any, change the value to that otherwise use the default
		$add = $this->getAddValue();
		if(isset($add))
		{
			if (isset($_GET['portID']))
			{
				$form .= "<tr class='normal'><td class='info'><input type='hidden' name='portID' value='$_GET[portID]'/>
				<input name='$add' id='add' type='submit' value='".$this->addText."'></td></tr>";
			}
			else{
				$form .= "<tr class='normal'><td class='info'><input name='$add' id='add' type='submit' value='".$this->addText."'></td></tr>";
			}
		}
		else
		{
			$form .= "<tr class='normal'><td class='info'><input name='addData' id='add' type='submit' value='".$this->addText."'></td></tr>";
		}
		$form .= "</table>
				</form>";
		
		return $form;
	}
	
	/***********************************************************************MODAL FORMS*********************************************/
	
	//create a editable modal form
	function modalForm($numhead='')
	{
		//declare all the important values
		$width = $this->getTableWidth();
		$titleWidth = $this->getTitleWidth();
		$modalID = $this->getModalID();
		$fieldType = $this->getFieldType();
		$this->typeIndex=0;
		
		//create the css needed for the modal window
		if ($modalID == "")
		{
			$modalID = 'dialog';	
		}
		
		$form .= "<style>";
		$form .= "#modalBox #".$modalID;
		$form .= "{
			width:auto;
			max-width: 80%;
			min-width:40%;
			height:auto;
			padding:10px;
			padding-top:10px;
			overflow:auto;
		}";
		$form .= "</style>";
		
		//start the modal window
		$form .= "<div id='modalBox'>
				<div id='".$modalID."' class='window'>
				<a href='#'class='close' /><img src='icons/close.png'></a>";
				
		$form .= "<form id=\"dataForm\" method=\"$this->formMethod\" ACTION=\"$this->formAction\" name=\"$this->formName\">
				<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%;'>";
		
		//create a form first		
		
			
		if($numHead=="")
		{
			$numHead=1;
		}		
		
		//declare all the indexes
		$index=0;
		$titleIndex=0;
		$dataValueIndex=0;
		
		//create the headings
		for ($headIndex=0; $headIndex<$numHead; $headIndex++)
		{
			$form .= "<tr class='form'>";
			$colspan = 0;
			//foreach ($this->heading as $index => $key)
			
			$tempIndex = $index;
			
			//calculate the colspan
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{	
				$index++; $colspan++;
			}
			
			//apply the colspan
			$index = $tempIndex;
			$colspan = $this->cols - $colspan + 1;
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{
				$form .= "<th colspan=\"$colspan\" align=\"left\">".$this->heading[$index]."</th>";
				$index++;
			}
			$form .= "</tr>";
			//foreach ($this->title as $index => $key)
			
			//create the titles
			while($this->title[$titleIndex] != "*<break>*" && count($this->title) > $titleIndex)
			{
				$form .= "<tr class='form'>";
				
				$title = $this->title[$titleIndex];
				
				//if the title is an array, display the array of titles
				if (is_array($title))
				{
					$curTitle = '';
					foreach ($title as $id => $value)
					{
						$curTitle .= $value . "<br/>";
					}
					$title = $curTitle;
				}
				
				//check for tooltip and apply it if there are any, if the field type is hidden, do not make the title
				if(preg_match("/\.tip\./", $this->title[$titleIndex]) && $fieldType[$dataValueIndex] != 'hidden')
				{
					$fullTitle = explode(".tip.", $this->title[$titleIndex], 2);
					$title = $fullTitle[0];
					$tip = $fullTitle[1];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><a class='tooltip' title='".$tip."'><img src='icons/Info.png' height='16' width='16'><b>".$title."</b></a></td>";
				}
				else if ($fieldType[$dataValueIndex] != 'hidden')
				{
					$title = $this->title[$titleIndex];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><b>".$title."</b></td>";
				}
				
				//create the data
				for($dataIndex = 1; $dataIndex < $this->cols; $dataIndex++)
				{
					//if the data is an array, display the array of data
					$data = $this->data[$dataValueIndex];
					if (is_array($data))
					{
						$curData = '';
						foreach ($data as $id => $value)
						{
							$curData .= $value . "<br/>";
						}
						$data = $curData;
					}
					$lowcaseTitle = strtolower($title);
					
					//check for modifiers for the field type, split the modifier if found
					if (!preg_match("/\\\./", $fieldType[$dataValueIndex]))
					{
						$fieldType[$dataValueIndex] = str_replace("\.", ".", $fieldType[$dataValueIndex]);
						if (preg_match("/\.[a-z]+/i", $fieldType[$dataValueIndex]))
						{
							$splitField = explode(".", $fieldType[$dataValueIndex],2);
							$curFieldType = $splitField[0];
							$moreOption = $splitField[1];
							
							$options = explode(".", $moreOption);
						}
						//if not then just go on with the regular field type
						else {
							$curFieldType = $fieldType[$dataValueIndex];
						}
					}
					
					//check field type
					switch ($curFieldType)
					{
						//text area
						case "text_area":
						$data = $data;
						$data = str_replace("<br />", "\n", $data);
						
						$form .= "<td class='info'>";
						//if there is a modifier apply it
						if(isset($options))
						{
							foreach ($options as $id => $value)
							{
								if (preg_match("/width\:/", $value))
								{
									$tWidth = str_replace("width:", "", $value);
								}
								else if (preg_match("/height\:/", $value))
								{
									$tHeight = str_replace("height:", "", $value);
								}
							}
							
							//if no modifier was there, use default
							if (!isset($tWidth)) {$tWidth = "100%";}
							if (!isset($tHeight)) {$tHeight = "100%";}
							
							//apply the modifiers
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='width:".$tWidth."; height:".$tHeight."' rows=\"5\">".$data."</textarea>";
							
						}
						//or else  don't apply modifiers, and use default settings
						else {
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='height:150px'>".$data."</textarea>";
						}
							
						$form .= "</td>";
						break;
						
						//hidden input
						case "hidden":
						$form .= "<input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>";
						break;
						
						//static input
						case "static":
						$form .= "<td class='info'><input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;
						
						//password input
						case "password":
						$form .= "<td class='info'><input type='password' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;
						// password with autocomplete="off" so browsers don't save this
						case "password_autocomplete_off":
						$form .= "<td class='info'><input type='password' autocomplete='off' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;
						//drop down menu
						case "drop_down":
						$form .= "<td class='info'>
							<div id='dropField'>";
							//get the list of item and check for modifiers
							$types = $this->getType();
							//if there is a modifier identify it and apply it
							if(isset($moreOption))
							{
								if (preg_match("/handler\:/", $moreOption))
								{
									$curAction = str_replace("handler:", "", $moreOption);
									$form .= $this->createDropDown($types[$this->typeIndex], $data, $this->databaseKeys[$dataValueIndex], $curAction);
								}
								else {
									$form .= $this->createDropDown($types[$this->typeIndex], $data, $this->databaseKeys[$dataValueIndex]);
								}
							}
							//or else don't apply the modifier
							else {
								$form .= $this->createDropDown($types[$this->typeIndex], $data, $this->databaseKeys[$dataValueIndex]);
							}
							
							$form .= "</div>
							</td>";
							$this->typeIndex++;
						break;
						
						//NEEDS TO BE MODIFIED TO ACCEPT ANY VALUES
						//radio buttons
						case "radio":
						$form .= "<td class='info'>";
						if ($data == 'Yes' || $data == 'Tagged' || $data == 'True')
							{
								$form .= "<input checked type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='1'>Yes</input>
								<input type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='0'>No</input>";
								
							}
							else
							{	
								$form .= "<input type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='1'>Yes</input>
								<input checked selected type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='0'>No</input>";
							}	
						$form .= "</td>";
						break;

						//date picker 
						case "date_picker":
						$form .= "<td class='info'>";
						$datepick_id = $this->databaseKeys[$dataValueIndex];
						$form .= $this->render_datepicker($datepick_id,$data);
						$form .= "<input name='".$this->databaseKeys[$dataValueIndex]."' id='".$this->databaseKeys[$dataValueIndex]."' class=\"$datepick_id\" />";
						$form .= "</td>";
						break;
						
						
						
						//custom field
						case "custom":
						$form .= "<td class='info'>";
						$form .= $data;
						$form .= "</td>";
						break;
						
						//default is text input type
						default:
						$form .= "<td class='info'><input name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"".$data."\" type=\"text\" maxChar=\"250\"></td>";
						break;					
					}
					$dataValueIndex++;
				}
				$form .= "</tr>";
				$titleIndex++;
			}
			//increment and restart process
			$titleIndex++;
			$index++;
		}
		//check if there's a post update value
		$update = $this->getUpdateValue();
		if(isset($update))
		{
			//apply it if there is one
			if (isset($_GET['portID']))
			{
				$form .= "<tr class='normal'><td class='info'><input type='hidden' name='portID' value='$_GET[portID]'/>
				<input name='$update' id='update' type='submit' value='".$this->getUpdateText()."'></td></tr>";
			}
			else{
				$form .= "<tr class='normal'><td class='info'><input name='$update' id='update' type='submit' value='".$this->getUpdateText()."'></td></tr>";
			}
		}
		//or else use the default
		else {
			$form .= "<tr class='normal'><td class='info'><input name='updateModal' id='update' type='submit' value='".$this->getUpdateText()."'></td></tr>";
		}
		
		$form .= "</table>
				</form>
				</div>
				<div id='mask'></div>
				</div>";
				
		return $form;
	}
	
	//create a modal form with no values
	function newModalForm($numhead='')
	{
		//declare all the important values
		$width = $this->getTableWidth();
		$titleWidth = $this->getTitleWidth();
		$fieldType = $this->getFieldType();
		$modalID = $this->getNewModalID();
		$this->typeIndex=0;
		
		if ($modalID == "")
		{
			$modalID = 'newDialog';	
		}
		
		//create the css for the modal window
		$form .= "<style>";
		$form .= "#modalBox #".$modalID;
		$form .= "{
			width:auto;
			max-width: 80%;
			min-width:40%;
			height:auto;
			padding:10px;
			padding-top:10px;
			overflow:auto;
		}";
		$form .= "</style>";
		
		//create the modal window
		$form .= "<div id='modalBox'>
				<div id='".$modalID."' class='window'>
				<a href='#'class='close' /><img src='icons/close.png'></a>";
				
		$form .= "<form id=\"dataForm\" method=\"$this->formMethod\" ACTION=\"$this->formAction\" name=\"$this->formName\">
				<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%;'>";
		
		//create a form first	
		if($numHead=="")
		{
			$numHead=1;
		}		
		
		//declare all the indexes
		$index=0;
		$titleIndex=0;
		$dataValueIndex=0;
		
		//create the headings
		for ($headIndex=0; $headIndex<$numHead; $headIndex++)
		{	
			$form .= "<tr class='form'>";
			$colspan = 0;
			//foreach ($this->heading as $index => $key)
			
			$tempIndex = $index;
			
			//calculate the colspan
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{	
				$index++; $colspan++;
			}
			
			$index = $tempIndex;
			$colspan = $this->cols - $colspan + 1;
			
			//apply the colspan
			while($this->heading[$index] != "*<break>*" && count($this->heading) > $index)
			{
				$form .= "<th colspan=\"$colspan\" align=\"left\">".$this->heading[$index]."</th>";
				$index++;
			}
			$form .= "</tr>";
			//foreach ($this->title as $index => $key)
			//create the titles
			while($this->title[$titleIndex] != "*<break>*" && count($this->title) > $titleIndex)
			{
				//check for tool tips, if there is a tool tip apply it. if the field type is hidden don't make the row
				$form .= "<tr class='form'>";			
				if(preg_match("/\.tip\./", $this->title[$titleIndex]) && $fieldType[$dataValueIndex] != 'hidden')
				{
					$fullTitle = explode(".tip.", $this->title[$titleIndex], 2);
					$title = $fullTitle[0];
					$tip = $fullTitle[1];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><a class='tooltip' title='".$tip."'><img src='icons/Info.png' height='16' width='16'><b>".$title."</b></a></td>";
				}
				else if ($fieldType[$dataValueIndex] != 'hidden')
				{
					$title = $this->title[$titleIndex];
					$form .= "<td class='title'  style='width:".$titleWidth.";'><b>".$title."</b></td>";
				}
				
				//create the data
				for($dataIndex = 1; $dataIndex < $this->cols; $dataIndex++)
				{
					$lowcaseTitle = strtolower($title);
					
					//check for modifiers in the data, and split them
					if (!preg_match("/\\\./", $fieldType[$dataValueIndex]))
					{
						$fieldType[$dataValueIndex] = str_replace("\.", ".", $fieldType[$dataValueIndex]);
						if (preg_match("/\.[a-z]+/i", $fieldType[$dataValueIndex]))
						{
							$splitField = explode(".", $fieldType[$dataValueIndex],2);
							$curFieldType = $splitField[0];
							$moreOption = $splitField[1];
							
							$options = explode(".", $moreOption);
						}
						//or else use default fieldtype
						else {
							$curFieldType = $fieldType[$dataValueIndex];
						}
					}
					
					//check field type
					switch ($curFieldType)
					{
						//text_area
						case "text_area":
						$data = $this->data[$dataValueIndex];
						$data = str_replace("<br />", "\n", $data);
						
						$form .= "<td class='info'>";
						//if there's a modifier apply them
						if(isset($options))
						{
							foreach ($options as $id => $value)
							{
								if (preg_match("/width\:/", $value))
								{
									$tWidth = str_replace("width:", "", $value);
								}
								else if (preg_match("/height\:/", $value))
								{
									$tHeight = str_replace("height:", "", $value);
								}
							}
							
							//apply the default modifier if none was shown
							if (!isset($tWidth)) {$tWidth = "100%";}
							if (!isset($tHeight)) {$tHeight = "100%";}
							
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='width:".$tWidth."; height:".$tHeight."' rows=\"5\">".$this->data[$dataValueIndex]."</textarea>";
							
						}
						//or else use default settings
						else {
							$form .= "<textArea name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"$infoArray[$infoIndex]\" type=\"text\" style='height:150px'>".$this->data[$dataValueIndex]."</textarea>";
						}
							
						$form .= "</td>";
						break;
						
						//hidden field
						case "hidden":
						$form .= "<input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>";
						break;

						//hidden field
						case "password":
						$form .= "<td class='info'><input type='password' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$this->data[$dataValueIndex]."</td>";
						break;


						// password with autocomplete="off" so browsers don't save this
						case "password_autocomplete_off":
						$form .= "<td class='info'><input type='password' autocomplete='off' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$data."</td>";
						break;
						
						//static field
						case "static":
						$form .= "<td class='info'><input type='hidden' Name ='".$this->databaseKeys[$dataValueIndex]."' value='".$this->data[$dataValueIndex]."'/>".$this->data[$dataValueIndex]."</td>";
						break;
						
						//drop down menus
						case "drop_down":
						$form .= "<td class='info'>
							<div id='dropField'>";
							$types = $this->getType();
							//get the list and if there's a modifier apply it
							if(isset($moreOption))
							{
								if (preg_match("/handler\:/", $moreOption))
								{
									$curAction = str_replace("handler:", "", $moreOption);
									$form .= $this->createDropDown($types[$this->typeIndex], "", $this->databaseKeys[$dataValueIndex], $curAction);
								}
								else {
									$form .= $this->createDropDown($types[$this->typeIndex], "", $this->databaseKeys[$dataValueIndex]);
								}
							}
							//or else don't apply the modifier
							else {
								$form .= $this->createDropDown($types[$this->typeIndex], "", $this->databaseKeys[$dataValueIndex]);
							}
							
							$form .= "</div>
							</td>";
							$this->typeIndex++;
						break;
						
						//NEEDS TO BE MODIFIED TO ACCEPT ANY VALUES
						//radio buttons
						case "radio":
						$form .= "<td class='info'>";
						$form .= "<input checked type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='1'>Yes</input>
						<input type='radio' Name ='".$this->databaseKeys[$dataValueIndex]."' value='0'>No</input>";
						$form .= "</td>";
						break;


						//date picker 
						case "date_picker":
						$form .= "<td class='info'>";
						$datepick_id = $this->databaseKeys[$dataValueIndex];
						$form .= $this->render_datepicker($datepick_id,$data);
						$form .= "<input name='".$this->databaseKeys[$dataValueIndex]."' id='".$this->databaseKeys[$dataValueIndex]."' class=\"$datepick_id\" />";
						$form .= "</td>";
						break;
						
						
						
						//custom field
						case "custom":
						$form .= "<td class='info'>";
						$form .= $this->data[$dataValueIndex];
						$form .= "</td>";
						break;
						
						//default is an input text type
						default:
						$form .= "<td class='info'><input name=\"".$this->databaseKeys[$dataValueIndex]."\" id=\"".$this->databaseKeys[$dataValueIndex]."\" value=\"\" type=\"text\" maxChar=\"250\"></td>";
						break;							
					}
					
					$dataValueIndex++;
				}
				$form .= "</tr>";
				$titleIndex++;
			}
			//increment and restart
			$titleIndex++;
			$index++;
		}
		//check if there are any post add values
		$add = $this->getAddValue();
		if(isset($add))
		{
			//if there are apply them to the submit
			if (isset($_GET['portID']))
			{
				$form .= "<tr class='normal'><td class='info'><input type='hidden' name='portID' value='$_GET[portID]'/>
				<input name='$add' id='add' type='submit' value='".$this->getAddText()."'></td></tr>";
			}
			else{
				$form .= "<tr class='normal'><td class='info'><input name='$add' id='add' type='submit' value='".$this->getAddText()."'></td></tr>";
			}
		}
		//else use default
		else
		{
			$form .= "<tr class='normal'><td class='info'><input name='addData' id='add' type='submit' value='".$this->getAddText()."'></td></tr>";
		}
		
		$form .= "</table>
				</form>
				</div>
				<div id='mask'></div>
				</div>";
				
		return $form;
	}
	
	
	
	//Function fo the error message layout
	function error($msg)
	{
		//show the error message
		echo "<p style='clear:both' id='error'>$msg</p>";
	}
	
	//Function fo the error message layout
	function success($msg)
	{
		//show the error message
		echo "<p style='clear:both' id='success'>$msg</p>";
	}
	
	function warning($msg)
	{
		//show the error message
		echo "<br/><br/><p id='warning'>$msg</p>";
	}
	
	//This function makes a prompt for user's confirmation on a certain action
	// Similar as prompt, just a bit nicer
	function confirm($msg)
	{
		echo "<div style='background-color: #FFFF66; 
			width:600px; 
			padding:5px;
        		padding-left:30px;
			color:#060;
			border:thin solid #C0F2A8; border-bottom:thin solid #C0F2A8;'> 
			<p>
			<img src='icons/Warning.png'>
			<FONT FACE='Arial, Helvetica, Geneva' size='2'>
			$msg
			</font>
			<form method='$this->formMethod' ACTION='$this->formAction'
				name='$this->formName'>
				<input type='submit' value='Yes' name='confirm'>
				<input type='submit' value='No' name='confirm'>
			</form></p><br></div>";
	}
	
	//This function makes a prompt for user's confirmation on a certain action
	function prompt($msg)
	{
		echo "<p id='error'>$msg</p>";		
		echo "<form method='$this->formMethod' ACTION='$this->formAction' name='$this->formName'>
				<input type='submit' value='YES' name='deleteYes'>
				<input type='submit' value='NO' name='deleteNo'>
				</form>";
	}
	
	//creates a drop down list
	function createDropDown($list, $default, $formKey, $action='')
	{
		$drop = "";
		//start the list
		$drop .= "<select id='".$formKey."' Name ='".$formKey."' onchange=\"".$action."\">
					<option selected value='Pick an option' disabled='disabled'>Pick an option</option>";
		//check for defaults and give a default value
		foreach($list as $id => $value)
		{
			if ($value == $default)
			{
				$drop .= "<option selected value='".$id."'>".$value."</option>";
			}
			else
			{	
				$drop .= "<option value='".$id."'>".$value."</option>";
			}
		}
		$drop .= "</select>";
		
		return $drop;
	}

	private function include_datepicker_scripts() {
		// Only include once
		if ($this->included_datepicker_js > 0) {
			return;
		} else {
			$this->included_datepicker_js++;
		}
		// Downloaded jquery.min.js 1.5.2 locally as we couldn't include http in https 	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
		$date_content = '<!-- jQuery -->
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
			<!-- required plugins -->
			<script type="text/javascript" src="js/datepicker/date.js"></script>
			<!--[if IE]><script type="text/javascript" src="js/datepicker/jquery.bgiframe.js"></script><![endif]-->

			<!-- jquery.datePicker.js -->
			<script type="text/javascript" src="js/datepicker/jquery.datePicker.js"></script>';

		return $date_content;
	}
        

	private function render_datepicker($name,$date='',$null=TRUE) {
		// First Include date picker scripts JS and CSS
		$date_pick_content = $this->include_datepicker_scripts();

		$year ='';
		$month ='';
		$day ='';


		// We assume $day-$month-year
		// but we can also understand $year-$month-day
		// As this is the mysql default.
		list($day,$month,$year) = split('[/-]', $date);
		$date_string ='';
		
		if ($day > 1000) {
			$year_new = $day;
			$day_new = $year;
			$day = $day_new;
			$year = $year_new;
		}
		/*
		if ($year=='') {
			$year = date(Y);  
		}
		if ($month=='') {
			$month = date(m);
		}
		if ($day=='') {
			$day = date(d);
		}
		*/
		if ($name=='') {
			$name = "datepick-$date";
		}

		// Is there a default?
		if( (is_numeric($year)) && (is_numeric($month)) && (is_numeric($day))) {
			$date_string = "$year-$month-$day";
		} else {
			$date_string ='';
		}

                // Month is An integer between 0 and 11 
		$jsmonth = $month -1;
        
		$date_pick_content .= " 
			<script type='text/javascript' charset='utf-8'>
			$(function() {
				Date.format = 'yyyy-mm-dd';	";
				 //$date_pick_content .= "$('.$name').datePicker({startDate:'01/01/1970'}).val(d.asString()).trigger('change');
				if ($date_string !='')  {	
				 $date_pick_content .= "
					var d = new Date();
					d.setFullYear($year,$jsmonth,$day);
					$('.$name').datePicker().val(d.asString()).trigger('change');
					$('.$name').dpSetStartDate('1900-01-01');
					$('.$name').dpSetSelected('$date_string');";
				}  else {
				 $date_pick_content .= "$('.$name').datePicker();
					$('.$name').dpSetStartDate('1900-01-01');";
				}
			$date_pick_content .= "});
			</script>
			<!-- datePicker required styles -->
			<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"js/datepicker/datePicker.css\">
		";
		return $date_pick_content;
        }
	
}
?>
