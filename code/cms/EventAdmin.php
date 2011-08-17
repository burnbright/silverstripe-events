<?php

class EventAdmin extends ModelAdmin{
	
	public static $managed_models = array(
		'EventRegistration',
		'Event',
		'EventAttendee',
		'EventTicket'
	);
	
	static $url_segment = 'events';
	static $menu_title = 'Events'; 
	
}