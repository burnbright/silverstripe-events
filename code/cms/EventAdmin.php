<?php

class EventAdmin extends ModelAdmin{

	static $url_segment = 'events';
	static $menu_title = 'Events';
	static $menu_priority = 5;
	static $allowed_actions = array();

	public static $managed_models = array(
		'EventRegistration' => array(
			'collection_controller' => "EventAdmin_EventRegistration_CollectionController",
			'record_controller' => "EventAdmin_EventRegistration_RecordController"
		),
		'Event',
		//'EventAttendee',
		//'EventTicket'
	);
}

class EventAdmin_EventRegistration_CollectionController extends ModelAdmin_CollectionController{

	/**
	 * Adds event selection to 'Create registration' model admin form.
	 * @see ModelAdmin_CollectionController::CreateForm()
	 */
	function CreateForm(){
		$form = parent::CreateForm();
		if(!$form) return; //there may be no form
		if($events = Event::current_events(5))
			$form->Fields()->push(new DropdownField('EventID','Event',$events->toDropdownMap()));

		$this->extend('updateCreateForm',$form,$this->modelClass);
		return $form;
	}

	/**
	 * Loads any data passed by the create form.
	 */
	function AddForm(){
		if($form = parent::AddForm()){
			$form->loadDataFrom($_POST);
			$this->extend('updateAddForm',$form,$this->modelClass);
			return $form;
		}
	}

}

class EventAdmin_EventRegistration_RecordController extends ModelAdmin_RecordController{


}