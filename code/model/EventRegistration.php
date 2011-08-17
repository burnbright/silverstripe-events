<?php 
/**
 * A registration for an event.
 *
 * @package events
 */
class EventRegistration extends DataObject {
	
	public static $db = array(
		'Places' => 'Int',
		'TotalCost' => 'Currency',
		'Success' => 'Boolean',
		'HiddenFromEventRegistrationList' => 'Boolean',
		'Status' => "Enum(array('Accepted', 'Pending', 'Declined', 'Unsubmitted'), 'Unsubmitted')",
		'FirstName' => 'Varchar',
		'Surname' => 'Varchar',
		'Email' => 'Varchar',
		'Notes' => 'Text',
		
		'SessionID' => 'Varchar'
	);
	
	public static $has_one = array(
		'Member' => 'Member',
		'Event' => 'Event',
		'Payment' => 'Payment' //TODO: allow payment via other means if necessary / multiple payments
	);
	
	public static $has_many = array(
		'Attendees' => 'EventAttendee',
		'StatusHistory' => 'RegistrationStatus'
	);
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	public static $casting = array();
	
	public static $defaults = array(
		'HiddenFromEventRegistrationList' => 0,
		'Status' => 'Unsubmitted'
	);

	public static $singular_name = 'Registration';

	public static $plural_name = 'Registrations';
	
	static $searchable_fields = array(
		'EventID' => array(
			'title' => 'Event'
		),
		"FirstName",
		"Surname",
		"Email",
	 );
	 
	 static $summary_fields = array(
	 	'Event' => 'Event.Title',
	 	'FirstName',
	 	'Surname',
	 	'Email',
	 	'Attendees' => 'Attendees.Count'
	 );
	
	
	/** Possible statuses */
	static $statuses = array(
		'WaitingForApproval' => 'Waiting For Approval',
		'WaitingForPayment' => 'Waiting For Payment',
		'Accepted' => 'Accepted',
		'AcceptedPaymentLater' => 'Accepted With Payment Later',
		'Declined' => 'Declined'
	);
	
	/**
	 * Return the entire history for this EventRegistration.
	 */
	public function getHistory() {
		$where = "EventRegistrationID = $this->ID";
		$sort = "ID DESC";
		return DataObject::get("RegistrationStatus", $where, $sort);
	}
	
	/**
	 * Allows for email to be on associated member, or provided.
	 */
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
	
	public function getNiceDate(){
		$date = DBField::create('Date',$this->Created);
		return $date->Nice();
	}
	
	public function getName(){
		return $this->getFirstName()." ".$this->getSurname();
	}
	
	public function getPlaces(){
		return $this->Attendees()->Count();
	}
	
	public function getCMSFields() {
		$fields = new FieldSet(
			new HiddenField('ID', 'ID'),
			new TabSet('Root')
		);
		
		if(!$this->ID && $events = DataObject::get('Event')){
			$events = $events->map('ID','MenuTitle');
			$fields->addFieldsToTab('Root.EventRegistration',new DropdownField('EventID','Event',$events));
		}else{
			$statuses = $this->dbObject('Status')->enumValues();					
			$fields->addFieldToTab('Root.EventRegistration', new DropdownField('Status', 'Status', $statuses));
			$fields->addFieldToTab('Root.EventRegistration', new ReadonlyField('Created', 'Date Created'));
		}
		
		$fields->addFieldToTab('Root.EventRegistration', new HeaderField("RegistrantDetials","Registrant Details"));		
		$fields->addFieldToTab('Root.EventRegistration', new TextField('FirstName', 'First Name'));
		$fields->addFieldToTab('Root.EventRegistration', new TextField('Surname', 'Surname'));		
		$fields->addFieldToTab('Root.EventRegistration', new EmailField('Email', 'Email'));
		//$fields->addFieldToTab('Root.EventRegistration', new CurrencyField('TotalCost', 'Cost'));
		
		$fields->addFieldToTab('Root.EventRegistration', new HeaderField("AttendeeDetails","Attendees"));
		$fields->addFieldToTab('Root.EventRegistration', $attendeestable = $this->getAttendeesTable());
			
		if(!$this->ID){
			
			$fields->addFieldToTab('Root.EventRegistration',new LiteralField('ADMINNEWREGISTRATIONMESSAGE','<p class="message">'._t("Event.ADMINNEWREGISTRATIONMESSAGE","You will be able to add attendees after you save the registration.").'</p>'));
		}
		
		//FIXME: payment is has_one.. no need for a table
		$paymentTable = new TableField(
			'Payments',
			'Payment',
			array(
				'ID' => 'ID',
				'PaymentMethod' => 'Payment Method',
				'Amount' => 'Amount',
				'Status' => 'Status',
				'Message' => 'Message',
				'SentInvoice' => 'Invoice sent?',
				'SentReceipt' => 'Receipt sent?'
			),
			array(
				'ID' => 'NumericField',
				'PaymentMethod' => 'TextField',
				'Amount' => 'CurrencyField',
				'Status' => 'TextField',
				'Message' => 'TextField',
				'SentInvoice' => 'ReadonlyField',
				'SentReceipt' => 'ReadonlyField'
			),
			null,
			"EventRegistrationID = $this->ID"
		);
		
		$paymentTable->setExtraData(array(
			'EventRegistrationID' => $this->ID
		));
		$paymentTable->setPermissions(array("show"));
		
		//$fields->addFieldToTab('Root.Payments', $paymentTable);
		
		//TODO: provide mechanism for resending email receipt
		if($this->PaymentID){
			$fields->addFieldToTab('Root.Payment', new ReadonlyField('PaymentID', 'ID',$this->PaymentID));
			$fields->addFieldToTab('Root.Payment', new ReadonlyField('PaymentMethod', 'Amount',$this->Payment()->Amount));
		
			//TODO: this array should come from Payment
			$statuses = array(
				'Incomplete' => 'Incomplete',
				'Success' => 'Success',
				'Failure' => 'Failure',
				'Pending' => 'Pending'
			);
			
			$fields->addFieldToTab('Root.Payment', new DropdownField('PaymentStatus', 'Status',$statuses,$this->Payment()->Status));
			$fields->addFieldToTab('Root.Payment', new ReadonlyField('PaymentMessage', 'Message',$this->Payment()->Message));
		}
		
		$this->extend('updateCMSFields', $fields);
		return $fields;
	}

	public function getAttendeesTable() {

		$where = "EventRegistrationID = $this->ID";
		$sort = 'Surname';
		
		$relationship = 'Attendees';
		$component = 'EventAttendee';
		
		$relationshipFields = singleton($component)->summaryFields();
		$foreignKey = $this->getRemoteJoinField($relationship);
		$ctf = new ComplexTableField(
				$this,
				$relationship,
				$component,
				$relationshipFields,
				"getCMSFields", 
				"\"$foreignKey\" = " . $this->ID
			);
		$ctf->setPermissions(TableListField::permissions_for_object($component));
		
		return $ctf;
	}
	
	/**
	 * Returns the javascript for CMS popup fields (specialised requirement for inline formfield)
	 */
	function getRequirementsForPopup(){
		Requirements::javascript('events/javascript/EventRegistration_iframe.js');
		Requirements::javascript('events/javascript/RegistrationStatusHandler.js');
	}	
	
	/**
	 * Get the name of the attached member.
	 */
	public function getMemberName(){
		$member = $this->Member();
		if($member->hasMethod("getCreditCardName")){
			return $member->getCreditCardName();
		}else{
			return $member->FirstName .  " " . $member->Surname;
		}
	}
	
	/**
	 * Returns the Members email 
	 */
	public function MemberEmail() {
		$member = $this->Member();
		return $member->Email;
	}
	
	public function sendReceipt() {
		$this->sendEmail();
	}
	
	/**
	 * Send booking receipt, along with the status of payment, if there was a cost.
	 * 
	 */
	protected function sendEmail($emailClass = 'Email',$template = null) {
		
 		$to = $this->getEmail(); //TODO: replace with cast of $this->Email ??
 		$from = Email::getAdminEmail(); //TODO: allow custom from address
 		
 		$bcc = ($this->EventID && $this->Event()->BCCContact && $this->Event()->EventContactEmail) ? $this->Event()->EventContactEmail :"";
 		
 		$e = new $emailClass($from,$to,'Event Registration Receipt: '.$this->Event()->Title,null,null,null,$bcc);
 		
 		if(!$template && $this->Event()){
			//allow Event subclasses to have an email template
			$anc = array_reverse(ClassInfo::ancestry($this->Event()->ClassName));
			$template = array();
			foreach($anc as $key => $classname){
				$template[] = $classname."_receiptEmail";
				if($classname == "Event") break;
			}
		}else{
			$template = "Event_receiptEmail";	
		}

 		$e->setTemplate($template);
 		
		$e->populateTemplate(
			array(
				"Registration" => $this,
				"Event" => $this->Event(),
				"Member" => $this->Member(),
				"Payment" => $this->Payment(),
				"Attendees" => $this->Attendees()
			)
		);

		$e->send();
	}
  	
  	/**
  	 * This returns the last payment object made on the EventRegistration.
  	 * (others are avaliable with a more intensive query)
  	 */
	function PaymentInformation(){
		$member = Member::currentUser();
		if(is_numeric($member->ID) && $this->Member->ID == $member->ID){
			$status = $this->Status;
			return $status->Payment;
		}
	}	
	
	/**
	 * Check if tickets available
	 * Set registration cost and number of places. 
	 */
	function makeBooking() {
		
		//TODO: allow optionally saving updated member details
		
		$requestedTickets = array();
		foreach($this->Attendees() as $attendee) {
			if(array_key_exists($attendee->TicketID, $requestedTickets)) {
				$requestedTickets[$attendee->TicketID] += 1;
			} else {
				$requestedTickets[$attendee->TicketID] = 1;
			}
		}
		
		if($this->Event()->checkTicketsAvailable($requestedTickets) === true) {
			$this->Places = $this->Attendees()->Count();
			$this->calculateTotalCost();
			return true;
		}
		
		return false;
	}
	
	/**
	 * Calculates the total cost of this EventRegistration, and stores it
	 * in TotalCost. Also returns the cost.
	 *
	 * This can be extended in a decorator by a method with the following declaration:
	 *    function updateTotalCost(&$cost)
	 *
	 * @param write - choose whether to write cost for each attendee
	 * @param attendees - Supply a dataset of attendees to calculate from. Useful for calculating total cost when attendees havn't been saved to db yet.
	 *
	 * @return float
	 */
	function calculateTotalCost($write = true, $attendees = null) {
		$cost = 0;
		$attendees = ($attendees) ? $attendees : $this->Attendees();
				
		foreach($attendees as $attendee) {
			$cost += $attendee->calculateCost($this->Event());
			if($write)$attendee->write();
		}		
		
		 // Allow this method to be modified by other classes and decorators
		$this->extend('updateTotalCost', $cost);
		$this->Event()->extend('updateTotalCost', $cost);
		
		$this->TotalCost = $cost;
		
		return $cost;
	}
	
	/**
	 * Update status based on payment
	 */
	function onBeforeWrite(){
		parent::onBeforeWrite();
		
		if($this->PaymentStatus){
			$payment = $this->Payment();
			$payment->Status = $this->PaymentStatus;
			$payment->write();
		}	
	}
	
	/**
	 * Delete associated attendees
	 */
	function onBeforeDelete(){
		
		if($attendees = $this->Attendees()){
			$ids = $attendees->getIdList();
			if(count($ids) > 0){
				$tableName = 'EventAttendee';	
				$idlist = implode(',',$ids);
				DB::query( "DELETE FROM `$tableName` WHERE EventRegistrationID = {$this->ID} AND ID IN($idlist)" );
			}
		}
		parent::onBeforeDelete();		
	}
	
}