<?
switch($_GET['form'])
{
	case 'deviceType':
?>

<h1>NEW DEVICE TYPE INFO</h1>
<form id='typeForm' name='typeForm' method='post' action="javascript:LoadPOST('devices.php?action=add&mode=changeDeviceType', 'device_type', deviceType());" onSubmit="return checkName('dName')">
  <table id='dataTable'>
  <tr>
  <td>Name</td>
  <td><input type='text' name='dName' id='dName'></td>
  </tr>
  <tr>
  <td>Description</td>
  <td><textArea rows='3' name='description' id='description'></textArea></td>
  </tr>
  <tr>
  <td>Vendor</td>
  <td><input type='text' name='vendor' id='vendor'></td>
  </tr>
  <tr>
  <td>Class</td>
  <td><input type='text' name='dClass' id='dClass'></td>
  </tr>
  <tr>
  <td>Notes</td>
  <td><textArea rows='3' name='dNotes' id='dNotes'></textArea></td>
  </tr>
  </table>
  <input type='submit' name='addType' id='addType' value='Add'> | <a href=''onclick='closeMessage();return false'>Cancel</a>
</form>

<?
	break;

	case 'deviceLocation':
?>

<h1>NEW LOCATION INFO</h1>
<form id='typeForm' name='typeForm' method='post' action="javascript:LoadPOST('devices.php?action=add&mode=changeLocationType', 'location', deviceLocation());" onSubmit="return checkName('lName')">
  <table id='dataTable'>
  <tr>
  <td>Name</td>
  <td colspan="6"><input type='text' name='lName' id='lName'></td>
  </tr>
  <tr>
  <td>City</td>
  <td><input type='text' name='lCity' id='lCity'></td>
  <td>Address</td>
  <td><input type='text' name='lAddress' id='lAddress'></td>
  <td>Postal Code</td>
  <td><input type='text' name='lPostal' id='lPostal' maxlength="6"></td>
  </tr>
  <tr>
  <td>Phone</td>
  <td><input type='text' name='lPhone' id='lPhone'></td>
  <td>Email</td>
  <td><input type='text' name='lEmail' id='lEmail'></td>  
  <td>Room</td>
  <td><input type='text' name='lRoom' id='lRoom'></td>
  </tr>
  <tr>
  <td>Notes</td>
  <td colspan="6"><textArea rows='3' name='lNotes' id='lNotes'></textArea></td>
  </tr>
  </table>
  <input type='submit' name='addLocation' id='addLocation' value='Add'> | <a href=''onclick='closeMessage();return false'>Cancel</a>
</form>

<?
	break;
?>

<?
}
?>
<script language="javascript">

function checkName(str)
{
	
	if (document.getElementById(str).value=="")
	{
		alert ('Fill in the name please');
		return false;
	}
	
	closeMessage();
	return true;	
}

function deviceType()
{
	var param = "name=" + document.getElementById("dName").value +
                "&description=" + document.getElementById("description").value +
				"&vendor=" + document.getElementById("vendor").value +
				"&dClass=" + document.getElementById("dClass").value +
				"&notes=" + document.getElementById("dNotes").value +
				"&addType=" + document.getElementById("addType").value;
	return param;
}

function deviceLocation()
{
	var param2 ="name=" + document.getElementById("lName").value +
                "&city=" + document.getElementById("lCity").value +
				"&address=" + document.getElementById("lAddress").value +
				"&postal=" + document.getElementById("lPostal").value +
				"&phone=" + document.getElementById("lPhone").value +
				"&email=" + document.getElementById("lEmail").value +
				"&room=" + document.getElementById("lRoom").value +
				"&notes=" + document.getElementById("lNotes").value +
				"&addLocation=" + document.getElementById("addLocation").value;
				
	return param2;
}


</script>
