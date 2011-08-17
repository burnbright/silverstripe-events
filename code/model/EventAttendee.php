<?php
/**
 * Represents one person attending an event.
 * Each EventRegistration may be registering more than one attendee
 * 
 * @package events
 */
class EventAttendee extends DataObject {
	
	public static $db = array(
		'FirstName' => 'Varchar',
		'Surname' => 'Varchar',
		'Email' => 'Varchar',
		'Cost' => 'Currency'
	);
	
	public static $has_one = array(
		'EventRegistration' => 'EventRegistration',
		'Ticket' => 'EventTicket',
		'Member' => 'Member'
	);
	
	public static $has_many = array();
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	public static $many_many_extraFields = array();
	
	public static $defaults = array();
	
	public static $default_sort = "Surname ASC, FirstName ASC";
	
	
	static $searchable_fields = array(
		"FirstName",
		"Surname",
		"Email"
	 );
	 
	 static $summary_fields = array(
	 	'FirstName',
	 	'Surname',
	 	'Email',
	 );
	
	
	/**
	 * Calculate the cost of the ticket for this attendee.
	 * This is stored separate from the cost in EventTicket so we know
	 * how much the ticket cost at the time of booking, and also allows
	 * decorators to add additional costs. The cost is stored in the Cost field
	 * of this object, and returned from the method.
	 *
	 * Can be extended in a decorator with a method with the following declaration:
	 *    function updateCost(&$cost)
	 *
	 * @return float
	 */
	function calculateCost($event = null) {
		if(!$event) $event = $this->EventRegistration()->Event();
		$cost = $this->Ticket()->Price;	
		$cost = $event->updateAttendeeCost($cost,$this);
		
		$this->Cost = $cost;
		return $cost;
	}
	
	function getCMSFields() {
		
		$members = DataObject::get('Member');
		
		$fields = new FieldSet(
			$memberfield = new DropdownField('MemberID','Member',$members->map('ID','Name')),
			new TextField('FirstName', 'First Name'),
			new TextField('Surname'),
			new EmailField('Email'),
			new DropdownField('TicketID', 'Ticket', $this->EventRegistration()->Event()->Tickets()->map('ID', 'NamePrice'))
			//new CurrencyField('Cost')
		);
		
		$memberfield->setHasEmptyDefault(true);
		$this->extend('updateCMSFields', $fields);
		return $fields;
	}
	
	function delete(){
		return true;
	}
	
	
	public function getEmail(){
		if($this->MemberID)
			return $this->Member()->Email;
		return (string) $this->getField('Email');
	}
	
	public function getFirstName(){
		if($this->MemberID)
			return $this->Member()->FirstName;
		return (string) $this->getField('FirstName');
	}
	
	public function getSurname(){
		if($this->MemberID)
			return $this->Member()->Surname;
		return (string) $this->getField('Surname');
	}
}
?>