<?
include_once('plugins/IP Manager/Vlan_manager.php');

$vlan_manager = new Vlan_database();


/*if (!$vlan_manager->reset()) {
       print "failed to reset vlan manager ";
       print $vlan_manager->get_error() . "\n";
}*/

$all_vlans = $vlan_manager->get_all_vlans();

//set the default column
$form = new Form("auto", 5);

//create the headings for these
$heading = array("VLAN ID", "Name", "Status", "Location", "Assigned To");

//all the food
$titles = array();

//their calories and locations correspondingly
$data = array();

// Loop through all vlans and set the name and status
foreach ($all_vlans as $vlan_id =>$vlan_name) {
       $my_vlan = new Vlan_database($vlan_id);
	   
	   array_push($data, $my_vlan->get_vlan_id());
	   array_push($data, $my_vlan->get_name());
	   array_push($data, $my_vlan->get_status());
	   array_push($data, $my_vlan->get_location_name());
	   array_push($data, $my_vlan->get_assigned_to_name());
	   
       /*print "vlan info for vlanid ". $my_vlan->get_vlan_id() ;
       print " vlan name ". $my_vlan->get_name() ;
       print " vlan location ". $my_vlan->get_location_name() ."\n";
       print "Asssinged to " . $my_vlan->get_assigned_to() ." name is ". $my_vlan->get_assigned_to_name()  ."\n";
       print "these are the tags: ";
       foreach ( $my_vlan->get_tags() as $tag) {
               print "$tag ";
       }
       #print "\n Now just dump the object\n";
       #print_r($my_vlan);
       $my_vlan->set_name("vlan $vlan_id");
       $my_vlan->set_notes("notes for vlan $vlan_id");
       $my_vlan->set_assigned_to(42);
       $my_vlan->set_location(4);
       $tags = array("tag1","tag2","tag3");
       $my_vlan->set_tags($tags);
       if ($my_vlan->update()) {
               print "vlan $vlan_id updated\n";
       } else {
               print "failed to update vlan $vlan_id\n";
               print $my_vlan->get_error() ."\n";
       }*/
}

$form->setSortable(true); // or false for not sortable
$form->setHeadings($heading);
$form->setData($data);

//set the table size
$form->setTableWidth("1024px");
$form->setTitleWidth("20%");
echo $form->showForm();

?>