<?
include_once '../../plugins/IP Manager/Netblock.php';

if ((isset($_POST['net'])) && (isset($_POST['length'])) && (is_numeric($_POST['length'])) && ($_POST['net'] != '')) {
	$net = $_POST['net'];
	$length = $_POST['length'];
	$netblock = new Netblock("$net/$length");
	if (($netblock->get_family() != 4) && ($netblock->get_family() != 6)) {
		//print "Invalid address<br>";
		return;
	} else {
		echo "<p><hr>";

		echo $netblock->print_all();
	}
}
?>
