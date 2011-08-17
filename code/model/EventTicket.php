<?php
/**
 * An EventTicket is a specific kind of ticket for an Event.
 *
 * @package events
 */
class EventTicket extends DataObject {
	
	public static $db = array(
		'Type' => 'Text',
		'Description' => 'Text',		
		'Price' => 'Currency',
		'TotalNumber' => 'Int',
		'NumberPerMember' => 'Int'
	);
	
	public static $has_one = array(
		'Event' => 'Event'
	);
	
	public static $has_many = array(
		'Attendees' => 'EventAttendee'
	);
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	public static $casting = array();
	
	
	/**
	 * Get fields for CMS popup.
	 * These fields can be modified in an Extension with a updatePopupFields() method.
	 * @return FieldSet
	 */
	function getPopupFields() {
		$fields = new FieldSet(
			new TextField('Type', 'Type'),
			new TextareaField('Description', 'Description'),
			new NumericField('Price', 'Price'),
			new NumericField('TotalNumber', 'Limit Total Number (0 for unlimited)'),
			new NumericField('NumberPerMember', 'Limit Number per Member (0 for unlimited)')
		);
		$this->extend('updatePopupFields', $fields);
		return $fields;
	}
	
	/**
	 * Get the price of this ticket.
	 * @return float|string Either the price or 'Free'
	 */
	function getPriceForTemplate() {
		return ($this->Price > 0) ? DBField::create('Currency',$this->Price)->Nice() : 'Free';
	}
	
	/**
	 * Get the number each member is allowed to book.
	 * @return int|string Either the limited number, or 'Unlimited'
	 */
	function getNumberPerMemberForBooking() {
		return $this->NumberPerMemberLimited ? $this->NumberPerMember : 'Unlimited';
	}
	
	/**
	 * Get the total number of tickets of this type left for booking.
	 * @todo where is the MemberTickets component?
	 * @return int|string Either the limited number, or 'Unlimited'
	 */
	function getRemainingPlaces() {
		if($this->TotalNumber) {
			$memberTickets = $this->getComponents('Attendees');
			return $this->TotalNumber - $memberTickets->Count();
		}
		return 'Unlimited';
	}
	
	function getNamePrice(){
		$price = ($this->Price)? DBField::create('Currency',$this->Price)->Nice(): "free" ;
		return $this->Type." - ".$price;
	}
	
}
?>