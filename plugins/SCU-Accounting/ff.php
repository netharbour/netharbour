<?
include_once("../../sessionCheck.php");

show_img();

function show_img() {
	$report_id = $_GET['report_id'];
        $query = "select img_file from accounting_reports
        	WHERE id = '$report_id'";
	$result =  mysql_query($query) ;
	if (!$result)  {
		print "failed to execute query $query<br>";
		return false;
	}

	$obj = mysql_fetch_object($result);
	$db_img = $obj->img_file;

	$db_img = base64_decode($db_img); //print_r($db_img );
	$db_img = imagecreatefromstring($db_img);
	if ($db_img !== false) {
		header("Content-Type: image/png");
		imagepng($db_img);
		#echo $db_img;
	}
	imagedestroy($db_img);
}
?>

