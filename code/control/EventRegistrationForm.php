<?php

/**
 * EventRegistration form
 * Generates a form to allow a member to Sign up for an event 
 * @package events
 */

class EventRegistrationForm extends Form {

	/**
	 * Generating a new instance of this class returns the form.
	 * This form also handles the EventRegistration managment.
	 * 
	 * @usage 
	 *	function EventRegistrationForm(){
	 *		return new EventRegistrationForm($this,"EventRegistrationForm");
	 *	}
	 *
	 * @param controller typically the event you're running this form from
	 * @param name the form name
	 * @param rsvp The EventRegistration this form is editing
	 */
	function __construct($controller, $name) {
				
		$fields = ($controller->ID) ? self::get_registration_fields($controller->ID) : new FieldSet();
		
		$actions = new FieldSet( 
			$registeraction = new FormAction('summarydirect', 'Register'),
			new FormAction('clearform', 'Clear Form')
		);
		
		$validator = self::get_registration_required_fields();

		//Allow decorators to add modify fields
		if($controller->dataRecord)
			$controller->data()->extend('updateRegistrationFields', $fields,$actions,$validator);
		
		parent::__construct($controller, $name, $fields, $actions, $validator);
		
		if($controller->dataRecord)
			$this->controller->data()->extend('updateBookingForm', $this);
		
		//Load session data
		if($formdata = Session::get('EventFormData.'.$this->controller->ID)){
			$this->loadDataFrom($formdata);
		}
	}
	
	
	/**
	 * Get the fields for the registration form.
	 */
	static function get_registration_fields($id) {
		$event = null;
		if($e = DataObject::get_by_id('Event',$id)){
			$event = $e;
		}else{
			return new FieldSet();
		}
		// Build a list of tickets and ticket descriptions
		$tickets = $event->AvailableTickets();
		
		if($tickets){ 
			
			$ticketTypes = array(); // store name & price against ticket id
			$ticketDescriptions = '';
			
			foreach($tickets as $ticket) {
				$ticketTypes[$ticket->ID] = $ticket->Type ." - ".$ticket->getPriceForTemplate();
			}
			$ticketDescriptions = $event->renderWith('TicketDescription');
		}
	
		$regfields = new CompositeField();
		$member = Member::currentUser();
		
		//TODO: allow preventing creation of readonly fields
		if($member && $member->FirstName) $regfields->push(new ReadonlyField('FirstName', 'First Name', $member->FirstName));
		else $regfields->push(new TextField('FirstName', 'First Name'));
		if($member && $member->Surname) $regfields->push(new ReadonlyField('Surname', 'Surname', $member->Surname));
		else $regfields->push(new TextField('Surname', 'Surname'));
		if($member && $member->Email) $regfields->push(new ReadonlyField('Email', 'Email', $member->Email));
		else $regfields->push(new EmailField('Email', 'Email'));
		
		if($member)	$regfields->push(new HiddenField('MemberID',$member->ID));
		
		$regfields->push(new LiteralField('EndFiller',""));
		$regfields->setName('RegistrationDetails');		

		// Basic fields
		$fields = new FieldSet(
			new HiddenField('ID'),
			new HeaderField('RegistrationDetailsHeader','Registration Details'),
			$regfields
		);
		
		// If we want to show descriptions, use a dropdown, else use radio buttons
		if($tickets && $tickets->Count() > 0 && $event->ShowDescriptions){
			$fields->insertBefore(new LiteralField('TicketDescriptions', $ticketDescriptions),'RegistrationDetailsHeader');
		}
	
		if($event->MultipleBooking) {
			
			//TODO: if logged then provide list of members to add, based on some dataset (eg: organisation members)
			
			$fieldlist = array(
				'FirstName' => 'First Name',
				'Surname' => 'Surname'
			);
			
			$fieldtypes = array(
				'FirstName' => 'TextField',
				'Surname' => 'TextField'
			);
			
			singleton('EventRegistration')->extend('updateAttendeesFieldNamesAndTypes',$fieldlist,$fieldtypes);
			
			if($tickets && $tickets->Count() > 0){
				$fieldlist['TicketID'] = 'Ticket';
				$fieldtypes['TicketID'] = new DropdownField('TicketID', 'Ticket', $ticketTypes);
				//TODO: just show ticket price if count == 1
			}
				
			// We want to be able to make multiple bookings at once, so use a table field
			$table = new CustomTableField(
				'Attendees',
				'EventAttendee',
				$fieldlist,
				$fieldtypes,
				null,
				"ID = -1", //keep it empty
				false
			);

			$fields->push(new HeaderField('AttendeesHeader','Event Attendees'));
			$fields->push($table);
			
		}else{
			if($tickets && $tickets->Count() > 0){
				if($tickets->Count() == 1){
					$fields->insertBefore(new HiddenField('TicketID','Ticket ID',$event->Tickets()->First()->ID),'EndFiller');
				}elseif($tickets->Count() > 1){
					$fields->insertBefore(new DropdownField('TicketID','Ticket Type',$ticketTypes),'EndFiller');
				}
			}
		}

		return $fields;
	}
	
	
		/**
	 * Get required fields for getRegistrationFields()
	 *
	 * @return Validator
	 */
	function get_registration_required_fields() {
		$requiredFields = new RequiredFields(
			'FirstName',
			'Surname',
			'Email'
		);
		
		//TODO: don't add ticket field when multiple attendees allowed
		//if(!$event->MultipleBooking)
			$requiredFields->addRequiredField('Ticket');
				
		// Allow decorators to add extra required fields
		//$this->extend('updateRegistrationRequiredFields', $requiredFields);
		
		return $requiredFields;
	}
	
}