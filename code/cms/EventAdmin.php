<?php

class EventAdmin extends ModelAdmin{

	static $url_segment = 'events';
	static $menu_title = 'Events';

	public static $collection_controller_class = "EventAdmin_CollectionController";

	public static $managed_models = array(
		'EventRegistration',
		'Event',
		'EventAttendee',
		'EventTicket'
	);

}

class EventAdmin_CollectionController extends ModelAdmin_CollectionController{

	/**
	 * Adds event selection to 'Create registration' model admin form.
	 * @see ModelAdmin_CollectionController::CreateForm()
	 */
	function CreateForm(){
		$form = parent::CreateForm();
		if(!$form) return; //there may be no form
		if($this->modelClass == 'EventRegistration'){
			if($events = Event::current_events(5))
				$form->Fields()->push(new DropdownField('EventID','Event',$events->toDropdownMap()));
		}
		$this->extend('updateCreateForm',$form,$this->modelClass);
		return $form;
	}

	/**
	 * Loads any data passed by the create form.
	 */
	function AddForm(){
		if($form = parent::AddForm()){
			$form->loadDataFrom($_POST);
			$this->extend('updateAddForm',$form);
			return $form;
		}
	}

}