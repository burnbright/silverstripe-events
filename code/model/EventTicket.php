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
		'NumberPerMember' => 'Int',
		'TicketFor' => "Enum('everybody,members,nonmembers','everybody')"
	);

	public static $has_one = array(
		'Event' => 'Event',
		'Group' => 'Group'
	);

	public static $has_many = array(
		'Attendees' => 'EventAttendee'
	);

	public static $many_many = array();
	public static $belongs_to = array();
	public static $belongs_many_many = array();

	public static $casting = array();

	static $summary_fields = array(
		'Type' => 'Name',
		'TicketFor' => 'Applies To',
		'NumberPerMember' => 'Member Limit',
		'TotalNumber' => 'Overall Limit',
		'RemainingPlaces' => 'Remaining Places',
		'Price' => 'Price'
	);

	static $field_labels = array(
		'Type' => 'Name'
	);

	/**
	 * Get fields for CMS popup.
	 * These fields can be modified in an Extension with a updatePopupFields() method.
	 * @return FieldSet
	 */
	function getCMSFields() {
		$fields = new FieldSet(
			new TabSet('Root',
				$ticketstab = new Tab('Details',
					new TextField('Type', 'Name'),
					new TextareaField('Description', 'Description'),
					new NumericField('Price', 'Price')
				),
				$restrictionstab = new Tab('Restrictions',
					new NumericField('TotalNumber', 'Limit Total Number (0 for unlimited)'),
					new NumericField('NumberPerMember', 'Limit Number per Member (0 for unlimited)')
				)
			)
		);
		$values = $this->dbObject('TicketFor')->enumValues();
		$i18nvalues = array();
		foreach($values as $key => $value){
			$i18nvalues[$value] = _t("EventTicket.".strtoupper($value),$value);
		}
		$restrictionstab->push(new OptionsetField("TicketFor",_t("EventTicket.TICKETFOR","Who is this ticket for:"),$i18nvalues));
		$this->extend('updateCMSFields', $fields);
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

	function forTemplate(){
		return $this->Type;
	}

	function canPurchase($member = null){
		$member = ($member) ? $member : Member::currentUser();
		//check if sold out
		if(!$this->getRemainingPlaces())
			return false;

		switch($this->TicketFor){
			case "members":
				return (bool)$member;
			case "nonmembers":
				return !(bool)$member;
		}
		return true;
	}

}