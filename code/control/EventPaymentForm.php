<?php

/**
 * @package events
 */

/**
 * Form for processing payments for events.
 * @package events
 */
class EventPaymentForm extends Form {

	function __construct($controller, $name) {
		
		$fields = new FieldSet();
		
		if($data = $controller->getSessionData()){
			
			$registration = $controller->generateRegistration(false);
			
			$summaryform = $controller->BookingForm();		
			$summaryform->loadDataFrom($data);
			
			$summaryform->makeReadonly();
			$fields = $summaryform->Fields();

			$fields->merge(Payment::combined_form_fields($registration->dbObject('TotalCost')->Nice()));
			
			$data = $controller->customise(array(
				'Attendees' => $registration->Attendees
			));
			$fields->replaceField('Attendees',new LiteralField('Attendees',$data->renderWith($controller->attendeessummarytemplate())));
			
		}else{
			//do redirect if form has been called directly via url
			if($controller instanceof HTTPRequest)
				Director::redirect($controller->ModifyLink());			
		}
		
		$actions = new FieldSet(
			new FormAction('modify','Modify'),
			new FormAction('processpayment', 'Pay')
		);
		//$validator = new CustomRequiredFields(Payment::combined_form_requirements());
		
		$controller->data()->extend('updatePaymentFields', $fields, $actions);
		
		parent::__construct($controller, $name, $fields,  $actions);//, $validator);
		Requirements::javascript('events/javascript/RsvpPaymentForm.js');
	}
	
}