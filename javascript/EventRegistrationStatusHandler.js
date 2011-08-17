/**
 * This javascript handles the inline form on 
 * a EventRegistration complextable field popup.
 */
 
RegistrationStatusHandler = Class.create();
RegistrationStatusHandler.prototype = {
	onclick : function() {
		// get values for new status and notes fields.
		var RegistrationStatus = $("ComplexTableField_Popup_DetailForm_RegistrationStatus");
		var Notes = $("ComplexTableField_Popup_DetailForm_Notes");
		var EventRegistrationID = $("ComplexTableField_Popup_DetailForm_EventRegistrationID");			

		var url = baseHref() + 'EventRegistration/saveStatus/?EventRegistrationID=' + EventRegistrationID.value + '&RegistrationStatus=' + RegistrationStatus.value + '&Notes=' + Notes.value;
		new Ajax.Request( url, {
			onSuccess: Ajax.Evaluator,
			onFailure: Ajax.Evaluator
		});
		return false;
	}
}
RegistrationStatusHandler.applyTo("form div.inlineformaction input");