<?

if (isset($_POST['inc']))
{
	$level = $_POST['inc'];
	echo "Awesome Level ". $level;
	$file ="awesome.xml";
	$xml = simplexml_load_file($file) or die ("CANNOT LOAD XML FILE");
	$xml->level = $level;
	$status = $xml->status;
	switch ($_POST['inc'])
	{
		case 0:
		$status = "Not Very Awesome...";
		break;
		
		case 5:
		$status = "Slighty Awesome...";
		break;
		
		case 10:
		$status = "Somewhat Awesome.";
		break;
		
		case 20:
		$status = "Awesome!";
		break;
		
		case 40:
		$status = "Way Awesome!";
		break;
		
		case 80:
		$status = "Awesome beyond belief!";
		break;
		
		case 120:
		$status = "OVERPOWERED AWESOMENESS!";
		break;
		
		case 300:
		$status = "You have way too much time on your hand, find something better to do.";
		break;
	}
	
	echo "<h1>".$status."</h1>";
	$xml->status = $status;
	file_put_contents($file, $xml->asXML());
}
?>