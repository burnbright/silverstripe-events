_ALL_PAYMENT_METHODS = [];

function PaymentMethodChanged() {
	var i, divEl;
	for(i = 0; i < _ALL_PAYMENT_METHODS.length; i++) {
		divEl = $('MethodFields_' + _ALL_PAYMENT_METHODS[i]);
		if(divEl) {
			divEl.style.display = (_ALL_PAYMENT_METHODS[i] == this.value) ? 'block' : 'none';
		} 
	}
}

Behaviour.register({
	'#PaymentMethod input[type=radio]' : {
		initialise: function() {
			_ALL_PAYMENT_METHODS[_ALL_PAYMENT_METHODS.length] = this.value;

			var i, divEl;
			for(i = 0; i < _ALL_PAYMENT_METHODS.length; i++) {
				divEl = $('MethodFields_' + _ALL_PAYMENT_METHODS[i]);
				if(i == 0) {
					divEl.style.display = 'block';
				} else {
					divEl.style.display = 'none';
				}
			}
		},
		onclick: PaymentMethodChanged
	}
});
