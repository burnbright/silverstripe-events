<?php
/**
 * Event holder is a class which handles the event administration. 
 * Although you can have more than one event holder, each can have many 
 * Event's beneath it.
 *
 * @package events
 */
class EventHolder extends Page {

	public static $db = array();
	
	public static $has_one = array();
	
	public static $has_many = array();
	
	public static $belongs_many_many = array();
	
	public static $defaults = array();
	
	static $default_child = 'Event';
	
}
class EventHolder_Controller extends Page_Controller {

	function rss() {
		$events = DataObject::get('Event', 'ParentID = ' . $this->ID, 'StartDate DESC', '', 20);
		if($events) {
			$rss = new RSSFeed($events, $this->Link(), $this->Title, "", "Title", "Content");
			$rss->outputToBrowser();
		}
	}

	function Calendar() {
		//return new Calendar($this, 'Calendar', new EventsCalendarProvider());
	}
	
}
?>
