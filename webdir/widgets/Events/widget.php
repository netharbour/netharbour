<script type="text/javascript" src="js/Ajax.js"></script>
<?
/////////////////////////////////////////////////////
// Widget for recent events
// Andree Toonk, Aug 2010
// 
/////////////////////////////////////////////////////

include_once 'classes/Event.php';
include_once 'classes/Check.php';
include_once 'classes/Form.php';

class EventWidget {
	
	var $status_collors=array(
		0 => "Green",
		1 => "Orange",
		2 => "Red",
		3 => "Blue"
	);
	var $status_array=array(
		0 => "Ok",
		1 => "Warning",
		2 => "Critical",
		3 => "Unknown",
	);

	function get_content() {
		$last_events = Event::get_last_events(10);
		$form = new Form();
		$keyData = array();
		$keyHandlers = array();
		foreach ($last_events as $event_id => $status) {
			$event = new Event($event_id);
			$status = 3;
			$status = $event->get_status();
			$status_name = $this->status_array[$status];

			array_push($keyData, "<font color=".$this->status_collors[$status]."> $status_name </font>");
			array_push($keyData, $event->get_hostname() );

			array_push($keyData, $event->get_check_name() );
			//array_push($keyData, $event->get_check_name() .".tip.". $event->get_key1() ." ". $event->get_key2());

			array_push($keyData, $this->getHowLongAgo($event->get_insert_date()) );
		//	array_push($keyData, $event->get_last_updated());
			$insert_time = (strtotime($event->get_insert_date()));
			$last_time = (strtotime($event->get_last_updated()));
			$diff = $this->strTime($last_time - $insert_time);
			array_push($keyData, $diff);
		//	array_push($keyData, $event->get_info_msg() );
			$check_id = $event->get_check_id();
			$check = new Check($check_id);
			if (is_null($check_id)) {
				array_push($keyHandlers, "");
			} else {
				array_push($keyHandlers, "handleEvent('monitor.php?action=showCheck&checkid=$check_id')");
			}


		}
		$headings = array("Status","Host", "Service", "Date",  "Duration" );
		$form->setCols(5);
		$form->setTableWidth("100%");
		$form->setData($keyData);
		$form->setEventHandler($keyHandlers);
		$form->setHeadings($headings);
		$form->setSortable(true);
			
		return $form->showForm() ." <div style='clear:both'></div>";

	}

	function strTime($s) {
		if ($s == 0) {
			return "0s";
		}

		$d = intval($s/86400);
		$s -= $d*86400;

		$h = intval($s/3600);
		$s -= $h*3600;

		$m = intval($s/60);
		$s -= $m*60;

		if ($d) $str = $d . 'd ';
		if ($h) $str .= $h . 'h ';
		if ($m) $str .= $m . 'm ';
		if ($s) $str .= $s . 's';

		return $str;
	}

	public static function getHowLongAgo($date, $display = array('Year', 'Month', 'Day', 'Hour', 'Minute', 'Second'), $ago = 'Ago') {
		$date = getdate(strtotime($date));
		$current = getdate();
		$p = array('year', 'mon', 'mday', 'hours', 'minutes', 'seconds');
		$factor = array(0, 12, 30, 24, 60, 60);
		
		for ($i = 0; $i < 6; $i++) {
			if ($i > 0) {
				$current[$p[$i]] += $current[$p[$i - 1]] * $factor[$i];
				$date[$p[$i]] += $date[$p[$i - 1]] * $factor[$i];
			}
			if ($current[$p[$i]] - $date[$p[$i]] > 1) {
				$value = $current[$p[$i]] - $date[$p[$i]];
				return $value . ' ' . $display[$i] . (($value != 1) ? 's' : '') . ' ' . $ago;
			}
		}
		return '';
	}



}
?>
