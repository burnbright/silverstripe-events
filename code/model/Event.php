<?php
/**
 * An event page.
 * This represents a single event, and allows a user to register
 * via a registration form.
 *
 * @package events
 */
class Event extends Page {

	public static $db = array(
		'Description' => 'Text',
		'StartDate' => 'Date',
		'FinishDate' => 'Date',
		'StartTime' => 'Time',
		'FinishTime' => 'Time',
		'Capacity' => 'Int',
		'EventContact' => 'Varchar',
		'EventContactEmail' => 'Varchar',
		'EventStatus' => "Enum('Available,Cancelled','Available')", // "Status" is already used on SiteTree

		'OnlineBooking' => 'Boolean',
		'BookingPermission' => "Enum('Anyone,LoggedInUsers,Group','LoggedInUsers')",
		'MultipleBooking' => 'Boolean',
		'ShowDescriptions' => 'Boolean',
		'FormOnSeparatePage' => 'Boolean',

		'ReceiptContent' => 'HTMLText',
		'BCCContact' => 'Boolean'
	);

	public static $has_one = array(
		'BookingGroup' => 'Group'
	);

	public static $has_many = array(
		'Tickets' => 'EventTicket',
		'EventRegistrations' => 'EventRegistration'
	);

	public static $many_many = array();

	public static $belongs_many_many = array();

	public static $defaults = array(
		'EventStatus' => 'Available',
		'BookingPermission' => 'Anyone'
	);

	static $icon  = 'events/images/date';

	function getCMSFields() {
		SiteTree::disableCMSFieldsExtensions();
		$fields = parent::getCMSFields();
		SiteTree::enableCMSFieldsExtensions();

		// Add content fields
		$fields->addFieldToTab('Root.Content.Main', new TextareaField('Description', 'Short Description'), 'Content');
		$fields->addFieldToTab('Root.Content.Main', new DropdownField('EventStatus', 'Event status', $this->obj('EventStatus')->enumValues(), '', null, '(Select a status)'), 'Description');

		// Add time/date fields
		$fields->addFieldToTab('Root.Content.Dates', new HeaderField('Event Start'));
		$fields->addFieldToTab('Root.Content.Dates', new CalendarDateField('StartDate', 'Date'));
		$fields->addFieldToTab('Root.Content.Dates',	new DropdownTimeField('StartTime', 'Time'));
		$fields->addFieldToTab('Root.Content.Dates', new HeaderField('Event End'));
		$fields->addFieldToTab('Root.Content.Dates', new CalendarDateField('FinishDate', 'Date'));
		$fields->addFieldToTab('Root.Content.Dates', new DropdownTimeField('FinishTime', 'Time'));

		// Add tickets table
		$fields->addFieldToTab('Root.Content.Tickets', $this->getEventTicketsTable());

		// Add booking options
		$fields->addFieldToTab('Root.Content', new Tab('Booking Options'));
		$fields->addFieldToTab('Root.Content.BookingOptions', new HeaderField('Booking Form'));
		$fields->addFieldToTab('Root.Content.BookingOptions', new CheckboxField('OnlineBooking', 'Show online booking form'));
		$fields->addFieldToTab('Root.Content.BookingOptions', new CheckboxField('MultipleBooking', 'Allow multiple bookings at once'));
		$fields->addFieldToTab('Root.Content.BookingOptions', new CheckboxField('ShowDescriptions', 'Show ticket descriptions'));
		$fields->addFieldToTab('Root.Content.BookingOptions', new CheckboxField('FormOnSeparatePage', 'Show form on a separate page'));

		$fields->addFieldToTab('Root.Content.BookingOptions', new NumericField('Capacity', 'Registration Limit', '', 3));
		$fields->addFieldToTab('Root.Content.BookingOptions', new LiteralField('CapacityHelp', '<p>Leave capacity blank for no limit. Also note that the use of ticket limits override this capacity field.</p>'));

		$fields->addFieldToTab('Root.Content.BookingOptions', new HeaderField('Who Can Book For This Event?'));
		$fields->addFieldToTab('Root.Content.BookingOptions', new OptionsetField(
			'BookingPermission',
			'',
			array(
				'Anyone' => 'Anyone <span style="color:#0074C6">( An email field will be added if the user is not logged )</span>',
				'LoggedInUsers' => 'Logged In Users',
				'Group' => 'Only The Users Of The Group'
			)
		));
		$fields->addFieldToTab('Root.Content.BookingOptions', new DropdownField('BookingGroupID', '', Group::map()));

   		//Add contact details
		$fields->addFieldToTab("Root.Content.Main", new TextField("EventContact","Event Contact"),"Description");
		$fields->addFieldToTab("Root.Content.Main", new TextField("EventContactEmail","Contact Email"),"Description");

		//Reports:
		$fields->addFieldToTab('Root.Registrations',new LiteralField('RegistrationsHelp','<p>Registrations that have been completed.</p>'));
		//$fields->addFieldToTab('Root.Registrations',new LiteralField('Enter Registration','<p><a class="addreg" href="#addreg">Add registration</a></p>'));
		$fields->addFieldToTab("Root.Registrations", $this->getEventRegistrationTable());
		$fields->addFieldToTab("Root.Registrations",new LiteralField('backendregister','<p><a href="admin/events" class="newregistraion action button">Add New Registration</a></p>'));

		//if($this->MultipleBooking)
			$fields->addFieldToTab('Root.Attendees', $this->getAttendeesTable());

		//emails
		$fields->addFieldToTab("Root.Content.Emails",new CheckboxField('BCCContact','send BCC to event contact'));
		$fields->addFieldToTab("Root.Content.Emails",new HtmlEditorField('ReceiptContent','Receipt Content'));

		//summary

		///TODO: add summary tab that shows: paid/unpaid , total income, registration numbers - by status and a total, attendee numbers

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	public function getEventRegistrationTable() {

		$where = '`EventID` = ' . $this->ID;
		$sort = 'Created';

		$fieldList = array(
				'ID' => 'ID',
				'FirstName' => 'First Name',
				'Surname' => 'Surname',
				'NiceDate' => 'Date',
				'Email' => 'Email',
				'Places' => 'Places',
				'Status' => 'Reg Status',
		);

		if($this->Tickets()->Count() > 0){
			$fieldList['TotalCost'] = 'Cost';
			$fieldList['Payment.ClassName'] = 'Payment Type'; //TODO: change to use Payment::get_supported_methods() for nice name;
			$fieldList['Payment.Status'] = 'Payment Status';
		}

		$table = new ComplexTableField(
			$this,
			'EventRegistrationReport',
			'EventRegistration',
			$fieldList,
			'getCMSFields',
			$where,
			$sort
		);
		$table->setPermissions(array('show', 'edit', 'export', 'delete'));
		return $table;
	}

	public function getAttendeesTable() {

		$where = 'EventRegistration.EventID = ' . $this->ID;
		$sort = 'Surname ASC, FirstName ASC';
		$join = 'INNER JOIN EventRegistration ON `EventRegistrationID` = `EventRegistration`.ID INNER JOIN EventTicket ON EventAttendee.TicketID = EventTicket.ID';

		$table = new ComplexTableField(
			$this,
			'AttendeesField',
			'EventAttendee',
			array(
				'EventRegistration.ID' => 'RegID',
				'FirstName' => 'First Name',
				'Surname' => 'Surname',
				'Ticket.Type' => 'Ticket',
				'Cost' => 'Cost',
				'EventRegistration.Status' => 'Status'
			),
			'getCMSFields',
			$where,
			$sort,
			$join
		);
		$table->setPermissions(array('show','edit', 'delete','export'));
		//$table->setParentClass('EventRegistration');
		return $table;
	}

	public function getEventTicketsTable() {
		$where = "`EventID` = 0 OR `EventID` = '$this->ID'";
		$table = new HasManyComplexTableField(
			$this,
			'Tickets',
			'EventTicket',
			array(
				'Type' => 'Type',
				'NumberPerMember' => 'Member Limit',
				'TotalNumber' => 'Overall Limit',
				'Price' => 'Price'
			),
			'getPopupFields',
			$where
		);
		$table->setAddTitle('A Ticket');
		return $table;
	}

	/**
	 * Gets all the available upcoming events.
	 * ie: it will exclude cancelled events.
	 * @param $interval allows you to specify how many days back from now to include. eg: all events occuring after 3 days ago.
	 */
	static function current_events($interval = null){
		$interval = ($interval) ? " - INTERVAL $interval DAY" : "";
		return DataObject::get("Event","\"StartDate\" >= DATE(NOW()) $interval AND STATUS != 'Cancelled'");
	}

	/**
	 * If event contact is a website, provide a link to it, else just provide the event contact name.
	 */
	function EventContact() {
		if(preg_match('/^(http:)/i', $this->EventContact)) {
			return <<<HTML
				<a href="$this->EventContact" title="Please visit">$this->EventContact</a>
HTML;
		}else{
			return $this->EventContact;
		}
	}

	function removeAllEventRegistration() {
		$where = "EventID = $this->ID";
		$regs = DataObject::get("EventRegistration", $where);
		if($regs) {
			foreach($regs as $reg) {
				$reg->delete();
			}
		}
	}

	/**
	 * Get an event calendar for this event.
	 */
	function CalendarHTML() {
		$dataObjectSet = new DataObjectSet();
		$dataObjectSet->push($this);
		$month = EventHolder_Controller::getMonthsByEvents($dataObjectSet, $events);
		$eventCalendar = new EventCalendar($month, $events);
		return $eventCalendar;
	}



	/**
	 * Get the last date of this event (the finish date if it exists, otherwise the start date)
	 * @return Date
	 */
	function EndDate() {
		return $this->FinishDate ? $this->obj('FinishDate') : $this->obj('StartDate');
	}

	/**
	 * Checks if this event has been cancelled.
	 * @return boolean
	 */
	function IsCancelled() {
		return $this->EventStatus == "Cancelled";
	}

	/**
	 * Checks if this event has already passed.
	 * @return boolean
	 */
	function IsPast() {
		return $this->EndDate()->InPast();
	}

	/**
	 * Checks if this event is upcoming (that is, it hasn't started yet).
	 * @return boolean
	 */
	function IsUpcoming() {
		return $this->StartDate()->InFuture();
	}

	/**
	 * Checks if this event is currently running.
	 * @return boolean
	 */
	function IsRunning() {
		$isPast = $this->IsPast();
		$isFuture = $this->IsUpcoming();
		return !$isPast && !$isFuture;
	}

	function HasSparePlaces(){
		if($this->Tickets()->Count() > 0 && (!$this->AvailableTickets() || $this->AvailableTickets()->Count() <= 0))
			return false;
		return (!$this->Capacity || !$this->AllAttendees() || ($this->AllAttendees()->Count() < $this->Capacity));
	}

	/**
	 * Checks if this event only runs for one day
	 * @return boolean
	 */
	function IsOneDayEvent() {
		return $this->StartDate() === $this->EndDate();
	}

	function AllAttendees(){
		return DataObject::get('EventAttendee','EventRegistration.EventID = ' . $this->ID,'','INNER JOIN EventRegistration ON `EventRegistrationID` = `EventRegistration`.ID');
	}

	function NumberOfEventRegistrationS() {

		$where = "EventID = {$this->ID} AND Status = 'Accepted'";
		if($this->ID > 0) {
			$rsvps = DataObject::get('EventRegistration', $where);
			if($rsvps) return $rsvps->Count();
		}
		return 0;
	}

	/**
	 * Get a short description of this event (no more than 50 words).
	 * @return string
	 */
	function ShortDescription() {
		if($this->Description) {
			$shortDes = new Text("ShortDes");
			$shortDes->setValue($this->Description);
			return $shortDes->LimitWordCount(50);
		 }elseif($this->Content) {
			$shortDes = new HTMLText("Content");
			$shortDes->setValue($this->Content);
			return strip_tags($shortDes->Summary(50));
		}
	}

	/**
	 * Check if this event can be booked.
	 * @return boolean
	 */
	function checkCanBook() {
		if(!$this->OnlineBooking) return false;
		if($this->BookingPermission == "LoggedInUsers" || $this->BookingPermission == "Group"){
			if(!Member::currentUser())
				return false;
		}
		if($this->BookingPermission == "Group" && !Member::currentUser()->inGroup($this->BookingGroup())) return false;
		$isPast = $this->IsPast();
		$isCancelled = $this->IsCancelled();

		return (!$isPast && !$isCancelled);
	}


	/**
	 * Check if member is already booked for the event
	 */
	function checkAlreadyBooked($member){
		//TODO: look up member id / Member::get_unique_identifier_field() in list of attendees for this event

		if(!$member) return false;
		$allattendees = $this->AllAttendees();
		if(!$allattendees) return false;
		return (bool)($allattendees->find('MemberID',$member->ID));
	}

	/**
	 * Check if tickets are available.
	 * Input is an array TicketID => NumberRequested.
	 * @return boolean
	 */
	function checkTicketsAvailable($requestedTickets) {
		foreach($requestedTickets as $ticketType => $numRequested) {
			$ticket = DataObject::get_by_id('EventTicket', $ticketType);

			// Check there are enough free tickets
			if($ticket->TotalNumber && ($ticket->Attendees()->Count() + $numRequested) > $ticket->TotalNumber) {
				return false;
			}
			// Check member isn't trying to book too many tickets
			if($ticket->NumberPerMember && $numRequested > $ticket->NumberPerMember) {
				return false;
			}
		}

		// If we get this far then all tickets are available
		return true;
	}

	function AvailableTickets(){

		$sqlQuery = new SQLQuery(
			 $select = "*, Count(EventTicket.ID) as Count",
			 $from = array('`EventTicket` INNER JOIN `EventAttendee` ON `EventAttendee`.`TicketID` = `EventTicket`.`ID`'),
			 $where = "`EventTicket`.`EventID` = ".$this->ID,
			 $orderby = "",
			 $groupby = "`EventTicket`.`ID`",
			 $having = "Count >= `EventTicket`.`TotalNumber` AND `EventTicket`.`TotalNumber` > 0",
			 $limit = ""
		);

		//TODO: join with registration too...otherwise there might be rouge attendees with no reg

		$result = $sqlQuery->execute();

		$ids = array();
		foreach($result as $ticket){
			$ids[] = $ticket['TicketID'];
		}

		$filter = (count($ids) > 0) ? " AND ID NOT IN(".implode(',',$ids).")" : "";
		$tickets = DataObject::get('EventTicket','EventID = '.$this->ID.$filter);

		return $tickets;
	}

	/**
	 * Get successful EventRegistrations.
	 * @return DataObjectSet
	 */
	public function successfulBookings() {
		$successfulBookings = new DataObjectSet();
		$rsvps = $this->EventRegistrations();
		if($rsvps) {
			foreach($rsvps as $rsvp){
				if($rsvp->Success == 'Success'){
					$successfulBookings->push($rsvp);
				}
			}
			return $successfulBookings;
		}
	}

	/**
	 * Get the number of places left.
	 * @return int
	 */
	public function getPlacesLeft(){
		return $this->Capacity - $this->getBookedPlaces();
	}

	/**
	 * Get the number of booked places.
	 * @return int
	 */
	public function getBookedPlaces(){
		$successfulBookings = $this->successfulBookings();

		if($successfulBookings){
			foreach($successfulBookings as $successfulBooking){
				$bookedPlaces += $successfulBooking->getTotalPlaces();
			}
		}
		return $bookedPlaces;
	}

	public function getDateQuerystring() {
		return isset($_GET['calendardate']) ? $_GET['calendardate'] : "";
	}

	public function sendBookingConfirmedEmail(){
		$email = new Email(); //TODO: finish this
		$email->send();
		//TODO: send attendees notification email? (requires attendees to have email)
	}

	/**
	 * Get all attendees with 'Accepted' registrations
	 */
	public function getAllAttendees($fil = null,$sort = null){
		$filter = ($fil) ? ' AND '.$fil: '';
		$where = 'EventRegistration.EventID = ' . $this->ID . ' AND Status = "Accepted"'.$filter;
		$sort = ($sort) ? $sort :'Surname ASC, FirstName ASC';
		$join = 'INNER JOIN EventRegistration ON `EventRegistrationID` = `EventRegistration`.ID INNER JOIN EventTicket ON EventAttendee.TicketID = EventTicket.ID';
		$attendees =  DataObject::get('EventAttendee',$where,$sort,$join);
		return $attendees;
	}

	/**
	 * Get all successful registrations
	 */
	public function getSuccessfulRegistrations(){
		$registrations = DataObject::get('EventRegistration','EventRegistration.EventID = ' . $this->ID . ' AND Status = "Accepted"');
		return $registrations;
	}

	/**
	 * Link to the modify page
	 */
	function ModifyLink(){
		return ($this->FormOnSeparatePage) ? $this->Link('register') : $this->Link();
	}

	function onBeforeWrite(){
		parent::onBeforeWrite();

		if(strtotime($this->FinishDate) < strtotime($this->StartDate)){ //empty finish date if it is before start
			$this->FinishDate = null;
			$this->FinishTime = null;
		}
	}

	/**
	 * Stub for extending in sub classes
	 */
	function updateAttendeeCost($cost, $attendee){
		$this->extend('updateAttendeeCost',&$cost,&$attendee);
		return $cost;
	}

}

/**
 * Controller for an Event.
 */
class Event_Controller extends Page_Controller {

	static $allowed_actions = array(
		'BookingForm',
		'PaymentForm',
		'register',
		'payment',
		'makepayment',
		'paymentcomplete',
		'summary',
		'review',
		'book',
		'modify'
	);

	/**
	 * Include styling for the events section.
	 */
	function init() {
		Requirements::themedCSS('events');

		// If ticket descriptions are enabled, include the necessary javascript
		if($this->ShowDescriptions) {
			Requirements::javascript('jsparty/prototype.js');
			Requirements::javascript('jsparty/behaviour.js');
			Requirements::javascript('events/javascript/tickettypes.js');
		}

		parent::init();
	}


	/**
	 * Session tools.
	 */

	function clearSession(){
		Session::clear('EventFormData.'.$this->ID); //clear session data
		Session::save('EventFormData.'.$this->ID);
	}

	function getSessionData(){
		return Session::get('EventFormData.'.$this->ID);
	}

	function setSessionData($data){
		$this->clearSession(); //seems to be necessary, otherwise session data gets ammended, and not replaced
		Session::set('EventFormData.'.$this->ID,$data);
	}

	function clearform(){
		$this->clearSession();
		Director::redirect($this->ModifyLink());
	}


	/**
	 * Set the form for index.
	 */
	function index(){
		return array(
			"Form" => $this->initForm()
		);
	}

	/**
	 * Separate register page if desired. Can be chosen in CMS.
	 */
	function register(){
		return array(
			'Content' => '', //TODO: Make this customisable
			"Form" => $this->initForm()
		);
	}

	/**
	 * Handle the various scenarios where tickets run out, event has passed, members only etc...
	 */
	protected function initForm(){

		if(!$this->OnlineBooking) return null;

		$form = ($this->checkCanBook()) ? $this->BookingForm() : "<p class=\"message\">"._t('Event.REGISTERPERMISSONMESSAGE','You do not have permission to register for this event. You may need to log in.')."</p>" ;
		$form = ($this->FormOnSeparatePage && Director::urlParam('Action') != 'register') ? "<a href=\"".$this->Link('register')."\" class=\"registerlink button\">Register</a>" : $form;

		if(!$this->HasSparePlaces())
			$form = "<p class=\"message bad\">The event is now full sorry.</p>";
		if($this->checkAlreadyBooked(Member::currentUser()) && !$this->MultipleBooking)
			$form = "<p class=\"message warning notice\">You are already registered for this event.</p>";
		if($this->IsPast())
			$form = "<p class=\"message warning notice\">This event occured in the past.</p>";
		if($this->IsCancelled())
			$form = "<p class=\"message bad\">The event has been cancelled.</p>";
		return $form;
	}


	function BookingForm() {
		$form = new EventRegistrationForm($this, 'BookingForm');
		return $form;
	}


	/**
	 * Do the actual booking
	 */
	function book(){

		if(!$this->getSessionData()){ //don't allow viewing page if session data isn't available
			Director::redirect($this->ModifyLink());
			return;
		}
		//TODO: final ticket availability check

		//create real registration object
		$registration = $this->generateRegistration(true,true);
		$registration->sendReceipt();
		$registration->Status = 'Accepted'; //TODO: payment
		$registration->write();

		$this->clearSession();
		//TODO: redirect to event home, or just display success message?
		return array(
			'Content' => '<p>'._t('Event.BOOKINGSUCCESSMESSAGE','Booking made successfully. You have been sent a confirmation email.').'</p>'
		);
	}


	/**
	 * Action for redirecting away from Form based action.
	 */
	function summarydirect($data,$form){
		$this->setSessionData($data); //store session data
		Director::redirect($this->Link('summary'));
	}

	/**
	 * Display a summary that includes entered registration details, along with a price, if appropriate.
	 *
	 * A payment form will show if needed.
	 *
	 * Submitting the form on this page will write everything to the database.
	 *
	 */

	function summary() {

		$data = $this->getSessionData();
		$form = $this->BookingForm();
		$form->loadDataFrom($data);

		//create temp registration for calculating costs etc
		$registration = $this->generateRegistration(); //false = don't write to DB
		$totalcost = $registration->TotalCost;

		if($this->Tickets() && $this->Tickets()->exists()){
			//TODO: make sure a ticket id has been selected, & make sure selected ticket belongs to this event
		}

		if($totalcost > 0) {
			//redirect to payment page
			Session::set('EventFormData.'.$this->ID.'.totalcost',$totalcost);
			Director::redirect($this->Link('payment'));
			return false;
		}

		$summaryform = unserialize(serialize($form));
		$summaryform->makeReadonly();

		$summaryform->setActions(new FieldSet(
			$modifyaction = new FormAction('modify','Modify'),
			$bookaction = new FormAction('book','Book')
		));

		$registration->extend('updateBookingSummaryForm',$summaryform);

		return array(
			'Title' => 'Summary',
			'Form' => $summaryform,
			'Content' => ''
		);
	}

	//Payment page
	function payment(){
		return array(
			'Content' => '', //Make this optional, customisable or remove
			"Form" => $this->PaymentForm()
		);
	}

	function PaymentForm(){
		$form = new EventPaymentForm($this,'PaymentForm');
		$this->data()->extend('updatePaymentForm', $form);
		return $form;
	}

	/**
	 * This function allows the payment attendees summary template to be modified in sub-classes
	 */
	function attendeessummarytemplate(){
		return "EventAttendeesSummary";
	}


	/**
	 * Do the actual booking with payment.
	 */
	function processpayment($data, $form) {

		if(!$this->getSessionData()){
			Director::redirect($this->ModifyLink());
			return false;
		}

		$registration = $this->generateRegistration(true,true);

		//create payment
		$payment = Object::create($data['PaymentMethod']);
		if(!($payment instanceof Payment)) {
			user_error(get_class($payment) . ' is not a Payment object!', E_USER_ERROR);
		}
		$form->saveInto($payment);

		$payment->EventRegistrationID = $registration->ID;
		$payment->Amount = $registration->TotalCost;
		$payment->write();

		$this->data()->extend('onBeforePayment', &$registration, &$payment, &$data, &$form);

		$result = $payment->processPayment($data, $form);

		$this->clearSession();

		if($result->isProcessing()) {
			$registration->Status = 'Pending';
			$registration->PaymentID = $payment->ID;
			$registration->write();
			return $result->getValue();
		}


		if($result->isSuccess()) {
			if($payment->Status == 'Pending') {
				$registration->Status = 'Pending';
				$registration->PaymentID = $payment->ID;
				$registration->write();
				$registration->sendReceipt();

				return array(
					'Content' => '<p>Thanks, your registration will be processed after your payment has been received. An email has been sent with your receipt.</p>', //TODO: make customisable
					'Form' => ' '
				);
			} else {
				$registration->Status = 'Accepted';
				$registration->PaymentID = $payment->ID;
				$registration->write();
				$registration->sendReceipt();

				return array(
					'Content' => '<p>Thanks, your payment has been processed and your reservation has been accepted. An email has been sent with your receipt.</p>', //TODO: make this custom
					'Form' => ' '
				);
			}
		} else {
			return array(
				'Content' => '<p class="error">Sorry, your payment was not successful: ' . $result->getValue() . '.</p>',  //TODO: make customisable
				'Form' => ' '
			);
		}

	}

	/**
	 *  Landing page for payment gateways
	 */
	function paymentcomplete(){
		$this->ID = -1;
		$this->Title = "Payment Complete";

		//TODO: swap urlPram ID for session::get ID
		if(is_numeric(Director::urlParam('ID')) && $registration = DataObject::get_one('EventRegistration','PaymentID = '.Director::urlParam('ID'))){

			//if there's no associated member and session id is wrong, or there is a current member and member id is wrong, fail.
			if($registration->MemberID){
				if(Member::currentUser() && ($registration->MemberID != Member::currentUser()->ID)){
					$this->Title = "Payment Error";
					$this->Content = '<p class="error">Inconsistent member information.</p>';
					return array();
				}
			}elseif((!$registration->MemberID && ($registration->SessionID != session_id()))){
				$this->Title = "Payment Error";
				$this->Content = '<p class="error">Inconsistent session information.</p>';
				return array();
			}

			if($payment = DataObject::get_by_id('Payment',Director::urlParam('ID'))){
				if($payment->Status == 'Success'){
					$registration->Success = true;
					$registration->Status = 'Accepted';
					$this->Content = '<p>Payment Was Successful. An email has been sent with your receipt.</p>'; //TODO: make customisable
					$registration->sendReceipt();
				}else{
					$this->Title = "Payment Status: ".$payment->Status;
					$this->Content = $payment->Message;
					$registration->Status = 'Declined';
				}

			}else{
				$this->Title = "Payment Error";
			}

			$registration->write();

		}else{
			$this->Title = "Payment Error";
			$this->Content = '<p class="warning">Could not recognise payment ID.</p>';
		}

		$this->clearSession();
		return array();
	}

	/**
	 * Redirector to modify page
	 */
	function modify($data, $form){
		Director::redirect($this->ModifyLink());
		return false;
	}

	/**
	 * Helper function to set up initial registration from saved session data.
	 * Session data is deleted at this point.
	 *
	 * $write - choose if regitration should be persisted to DB.
	 * $writeattendees - chose if attendees should be written to DB.
	 */
	public function generateRegistration($write = false, $writeattendees = false){

		$dummyform = $this->BookingForm();
		$regdata = $this->getSessionData(); //get stored session data

		$dummyform->loadDataFrom($regdata);

		$attendees = $this->generateAttendees($dummyform,$writeattendees);

		$registration = new EventRegistration();
		$dummyform->saveInto($registration);
		if($write) $registration->write();

		$registration->SessionID = session_id(); //used to check against after payment gateway redirect
		$registration->EventID = $this->ID;
		if(Member::currentUser()) $registration->MemberID = Member::currentUser()->ID;

		if($write){
			$registration->Attendees()->addMany($attendees);
			$registration->calculateTotalCost();
			$registration->write();
		}else{
			$registration->Attendees = $attendees;
			$registration->calculateTotalCost(false,$attendees);
		}
		return $registration;
	}


	/**
	 * Helper function to create attendees DataObjectSet.
	 * It will be populated with one entry if the event does not allow multiple bookings.
	 */
	function generateAttendees($form,$write = false){
		$attendees = new DataObjectSet();
		if($this->MultipleBooking){
			$afield = $form->Fields()->fieldByName('Attendees');
			$attendees = $afield->getDataObjectSet('EventAttendee',$write);	//custom method on custom class
		}else{
			$attendee = new EventAttendee();
			$form->saveInto($attendee);
			if(Member::currentUser())
				$attendee->MemberID = Member::currentUser()->ID;
			$attendees->push($attendee);
		}
		return $attendees;
	}


	function complete(){

		$content = '<p>Booking complete</p>';
		$title = 'Booking complete';

		return array(
			'Title' => $title,
			'Content' => $content,
			'Form' => ' '
		);
	}

}


class EventAdminController extends Controller{

	function init(){
		parent::init();
	}

	function Title(){
		return _t('Event'.'ADMINADDTITLE',"Create Registration");
	}

	function Form(){

		$this->ID = $this->currentPageID();

		//choose members
		$fields = new FieldSet(
			new DropdownField('Organisation','Organisation',DataObject::get('Orga'))
		);

		$actions = new FieldSet(
			new FormAction('doregistration','Submit')
		);

		$form = new Form($this,'Form',$fields,$actions);

		return $form;
	}

	public function summarydirect(){

		Director::redirect(Director::baseURL().'admin');
	}

	public function currentPageID() {

		$class = "CMSMain";

		if(isset($_REQUEST['ID']) && is_numeric($_REQUEST['ID']))	{
			return $_REQUEST['ID'];
		} elseif (isset($this->urlParams['ID']) && is_numeric($this->urlParams['ID'])) {
			return $this->urlParams['ID'];
		} elseif(Session::get("{$class}.currentPage")) {
			return Session::get("{$class}.currentPage");
		} else {
			return null;
		}
	}

	public function Link($action = null, $id = null){
		if($action){
			$action = "/$action";
			if($id) $action .= "/$id";

		}else{
			$action = "";
		}
		return $this->class.$action;
	}

}

?>
