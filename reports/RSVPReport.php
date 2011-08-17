<?

/**
 * This is effectivly a report to show member EventRegistration information in a table,
 * to be edited as required.
 * 
 * Its inefficient for large numbers of EventRegistration's, but for now will suffice 
 */
class EventRegistrationReport extends ComplexTableField{
	public $popupClass = "EventRegistrationReport_popup";
	
	
	function __construct($name, $sourceClass, $eventID ){
				
		$fieldList = array(
			
		);
		
		$detailFormFields = new Fieldset(
			new ReadonlyField("ID"),
			new ReadonlyField("Places"),
			new ReadonlyField("TotalCost"),
			new ReadonlyField("Acknowledged")
		);
		
		$sourceFilter = "EventID = $eventID AND Successful = 1";
		$sourceSort = "ID DESC";
		$sourceJoin =  "";		
		
		parent::__construct(
			null,
			$name,
			$sourceClass,
			$fieldList,
			$detailFormFields,
			$sourceFilter,
			$sourceSort,
			$sourceJoin = ""
		);
		
		//Set the defaults
		$this->pageSize = "20";
		$this->permissions = array(
			"add",
			"edit",
			"show"
		);
	}
}

/**
 * EventRegistration's Popup uses a special implementation which calls 
 * getCMSfields on the EventRegistration object as this is only used within
 * the CMS.
 */
class EventRegistrationreport_popup extends ComplexTableField_Popup{
	function  __construct($controller, $name, $fields, $sourceClass, $readonly=false) {
		$overloadedfields = $sourceClass->getCMSFields();
		if(!$overloadedfields){
			$overloadedfields = $fields;
		}
		parent::__constuct($controller,$name,$overloadedfields,$sourceClass, $readonly=false);
	}

}
