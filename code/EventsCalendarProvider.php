<?php

class EventsCalendarProvider extends Object implements CalendarProvider {
	function getCalendarItems($year, $month, $day) {
		$sqldate = "STR_TO_DATE('{$year} {$month} {$day}','%Y %m %d')";
		
		// date == startdate
		// or enddate not null and date between startdate and enddate
		return DataObject::get('Event', "StartDate = $sqldate OR (FinishDate IS NOT NULL AND $sqldate BETWEEN StartDate AND FinishDate)");
	}
}

?>
