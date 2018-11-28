#!/usr/bin/env php
<?php

include_once "config/opendb.php";
include_once "classes/Event.php";
include_once "classes/Check.php";

if (!is_numeric($argv['1'])) {
	print "Error: expecting event id as argument\n";
	exit;
}

$event_obj = New Event($argv[1]);

// If you need access to more Check details, just create a Check object
// $check_obj = New Check($event_obj->get_check_id());
// print_r($check_obj);

/*
 Below goes your custom notification code
 All event info can be access using the Event object
 Have Fun!
*/

$to      = $event_obj->notify_email;
$from    = $event_obj->notify_email_from;
$subject = 'Events State change';
$message = "A new event was detected\n\n" . print_r($event_obj,true);
$headers = "From: $from \r\n" .
	"Reply-To: $from \r\n" .
	"X-Mailer: PHP/" . phpversion();

mail($to, $subject, $message, $headers);

?>
