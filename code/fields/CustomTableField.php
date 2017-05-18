<?php

/**
* This is a hacked up extension of TableField for the purpose of getting a non-database-saved dataobject set.
*/

class CustomTableField extends TableField{
		
		function getDataObjectSet($class = 'DataObject',$write = false){
				$dos = new DataObjectSet();
				$data = $this->value;
				if(isset($data['new']) && $data['new']) {
						$newFields = $this->sortData($data['new'], null);
						$dos = $this->saveDataCustom($newFields, false, $write);
				}
				return $dos;
		}
		
		function saveDataCustom($dataObjects,$ExistingValues = true, $write = true) {
				$savedObj = array();
				$fieldset = $this->FieldSetForRow();
				$outputset = new DataObjectSet();
				
				// add hiddenfields
				if($this->extraData) {
						foreach($this->extraData as $fieldName => $fieldValue) {
								$fieldset->push(new HiddenField($fieldName));
						}
				}
				
				$form = new Form($this, null, $fieldset, new FieldSet());
				
				if($dataObjects) {
						foreach ($dataObjects as $objectid => $fieldValues) {
								// we have to "sort" new data first, and process it in a seperate saveData-call (see setValue())
								if($objectid === "new") continue;
								
								// extra data was creating fields, but
								if($this->extraData) {
										$fieldValues = array_merge( $this->extraData, $fieldValues );
								}
								
								$hasData = false;
								$obj = new $this->sourceClass();
								
								if($ExistingValues) {
										$obj = DataObject::get_by_id($this->sourceClass, $objectid);
								}
								
								// Legacy: Use the filter as a predefined relationship-ID
								if($this->filterField && $this->filterValue) {
										$filterField = $this->filterField;
										$obj->$filterField = $this->filterValue;
								}
								
								// Determine if there is changed data for saving
								$dataFields = array();
								
								foreach($fieldValues as $type => $value) {
										if(is_array($this->extraData)){ // if the field is an actual datafield (not a preset hiddenfield)
												if(!in_array($type, array_keys($this->extraData))) {
														$dataFields[$type] = $value;
												}
										} else { // all fields are real
												$dataFields[$type] = $value;
										}
								}
								
								$dataValues = ArrayLib::array_values_recursive($dataFields);
								
								foreach($dataValues as $value) {
										if(!empty($value)) {
												$hasData = true;
										}
								}
								
								// save
								if($hasData) {
										$form->loadDataFrom($fieldValues, true);
										$form->saveInto($obj);
										
										if($write)
										//$objectid = $obj->write();
										
										$savedObj[$objectid] = "Updated";
										
										$outputset->push($obj);
								}
								
						}
						return $outputset;
				}
				return null;
		}
		
}
