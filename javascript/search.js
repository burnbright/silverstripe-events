Behaviour.register({
	'#EventSearchForm_SearchForm_action_getResults' : {
		onclick : Filter_OnSubmit
	}
});


function Filter_OnSubmit(){
	new Ajax.SubmitForm(
		"EventSearchForm_SearchForm", 
		"action_getResults", 
		{
			onSuccess : Ajax.Evaluator,
			onFailure : function(response) {
				errorMessage(response.responseText);
			}
		}
	);
	return false;
}

Behaviour.addLoader(Filter_OnSubmit);

/**
 * Called when something goes wrong
 */
function errorMessage(msg, fullMessage) {
	// More complex error for developers
	if(fullMessage && window.location.href.indexOf('//dev') != -1) {
		// Get the message from an Ajax response object
		try {
			if(typeof fullMessage == 'object') fullMessage = fullMessage.status + '//' + fullMessage.responseText;
		} catch(er) {
			fullMessage = "";
		}
		msg = msg + '<br>' + fullMessage.replace(/\n/g,'<br>');
	}
	
	$('statusMessage').showMessage(msg,'bad',60);
}

Behaviour.register({
	'#statusMessage' : {
		showMessage : function(message, type, waitTime) {
			if(this.fadeTimer) {
				clearTimeout(this.fadeTimer);
				this.fadeTimer = null;
			}
			if(this.currentEffect) {
				this.currentEffect.cancel();
				this.currentEffect = null;
			}
			
			this.innerHTML = message;
			this.className = type;
			Element.setOpacity(this, 1); 
			
			this.style.position = 'absolute';
			this.style.display = '';
			this.style.visibility = '';
			
			
			this.fade(0.5,waitTime ? waitTime : 5);
		},
		clearMessage : function(waitTime) {
			this.fade(0.5, waitTime);
		},
		fade: function(fadeTime, waitTime) {
			if(!fadeTime) fadeTime = 0.5;
			
			// Wait a bit before fading			
			if(waitTime) {
				this.fadeTimer = setTimeout((function() {
					this.fade(fadeTime);
				}).bind(this), waitTime * 1000);
			
			// Fade straight away
			} else {
			 	this.currentEffect = new Effect.Opacity(this,
				    { duration: 0.5, 
				      transition: Effect.Transitions.linear, 
				      from: 1.0, to: 0.0,
				      afterFinish : this.afterFade.bind(this) });
			}
		},
		afterFade : function() {
			this.style.visibility = 'hidden';
			this.style.display = 'none';
			this.innerHTML = '';
		}
	}
});