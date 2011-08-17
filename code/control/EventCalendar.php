<?php

/**
 * @package events
 */

class EventCalendar extends ViewableData {
	
	private $eventMonths;
	private $validEvents;
	
	public function __construct($months, $validEvents){
		Requirements::javascript("events/javascript/EventCalendar.js");
		$this->eventMonths = new DataObjectSet();
		
		if($months) foreach($months as $month => $eventdates){
			$emonth = new EventCalendar_Month();
			$emonth->setDates($month, $eventdates, $validEvents[$month]);
			$this->eventMonths->push($emonth);
		}
	}
	
	public function Months(){
		return $this->eventMonths;
	}
	
	public function forTemplate(){
		Global $project;
		if(Director::fileExists("$project/css/eventcalendar.css")){
			Requirements::css("$project/css/eventcalendar.css");	
		}else{
			Requirements::css("events/css/eventcalendar.css");			
		}
		return $this->renderWith("EventCalendar");
	}
}

class EventCalendar_Month extends DataObject {
	
	protected $month;
	protected $eventDates, $events;
	protected $firstDayStamp, $lastDayStamp;
	
	
	public function setDates($month, $eventdates, $events){
		$this->month = $month;
		$matches = array();
		if(preg_match('/^([0-9]{1,2})\s([0-9]{4})$/', $month, $matches)){
			$this->firstDayStamp = mktime(0,0,0,$matches[1],1,$matches[2]);
			$this->lastDayStamp = mktime(0,0,0, $matches[1], $this->numDates(), $matches[2]);
		}
		
		$this->eventDates = $eventdates;
		$i=0;
		if(is_array($eventdates)){
			foreach($eventdates as $eventdate){
				$this->events[$eventdate] = $events[$i];
				$i++;
			}
		}
	}
	
	public function databaseFields(){
		return null;
	}
	
	function Caption(){
		return date("F Y", $this->firstDayStamp);
	}
	
	function LeadingGhostDates(){
		$firstDay = date("w", $this->firstDayStamp);
		$leadingGhostDates = new DataObjectSet();
		for($i=0; $i<$firstDay; $i++){
			$leadingGhostDates -> push(new EventCalendar_GhostDate());
		}
		return $leadingGhostDates;
	}
	
	function Dates() {
		$numDates = $this->numDates();
		$dates = new DataObjectSet();
		$whichMonth = date("m", $this->firstDayStamp);
		$whichYear = date("Y", $this->firstDayStamp);
		
		for($i=1; $i<=$numDates; $i++){
			$whichDate = $i<10?"0".$i:$i;
			$date = new EventCalendar_Date();
			$date->setDate("$whichYear-$whichMonth-$whichDate");
			if(in_array($date->Val(), $this->eventDates)){
				$date->setHasEvent(true);
				$date->setEvent($this->events["$whichYear-$whichMonth-$whichDate"]);
			}
			$dates->push($date);

		}
		return $dates;
	}
	
	function TailingGhostDates() {
		$lastDay = date("w", $this->lastDayStamp);
		$tailingGhostDates = new DataObjectSet();
		for($i = $lastDay; $i < 6; $i++){
			$tailingGhostDates ->push(new EventCalendar_GhostDate());
		}
		return $tailingGhostDates;
	}
	
	function numDates(){
		return date("t", $this->firstDayStamp);
	}
	
	function ID(){
		return "month_".date("F-Y",$this->firstDayStamp);
	}
}

class EventCalendar_Date extends DataObject {
	
	private $date;
	private $event;
	private $hasEvent;
	
	public function setDate($date){
		$this->date = $date;	
	}
	
	public function Link(){
		return Director::currentURLSegment();
	}
	
	public function setHasEvent($bool){
		$this->hasEvent = $bool;
	}
	
	public function HasEvent(){
		return $this->hasEvent;
	}
	
	public function setEvent($event){
		$this->event = $event;
	}
	
	public function Event(){
		return $this->event;
	}
	
	public function Val(){
		return $this->date;
	}
	
	protected function StampVal(){
		if(preg_match('/^([\d]{4})-([\d]{2})-([\d]{1,2})$/', $this->date, $matches)){
			return mktime(0,0,0,$matches[2], $matches[3], $matches[1]);
		}
	}
	
	public function IsFirstDay(){
		return date("w", $this->StampVal())==0;
	}
	
	public function IsLastDay(){
		return date("w", $this->StampVal())==6;
	}
	
	public function IsToday(){
		return date("Y-m-d", time())==$this->date;
	}
	
	public function NumVal(){
		if(preg_match('/^([\d]{4})-([\d]{2})-([\d]{1,2})$/', $this->date, $matches)){
			return $matches[3];
		}
	}
	
	public function databaseFields(){
		return null;
	}
}

class EventCalendar_GhostDate extends EventCalendar_Date {
	public function __construct(){
		parent::__construct(null);
	}
}
?>
