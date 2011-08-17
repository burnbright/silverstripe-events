Behaviour.register({			
	'#ComplexTableField_Popup_DetailForm_Payment' : {
		initialise : function() {
			var price = document.getElementById( 'ComplexTableField_Popup_DetailForm_Price' );
			if( ( this.className == 'checkbox' && ! this.checked ) || ( this.className == 'readonly' && this.innerHTML == 'no' ) )
				price.style.display = 'none';
		},
		onchange : function() {
			var price = document.getElementById( 'ComplexTableField_Popup_DetailForm_Price' );
			if( this.checked )
				price.style.display = 'block';
			else
				price.style.display = 'none';
		}
	},
	'#ComplexTableField_Popup_DetailForm_TotalNumberLimited' : {
		initialise : function() {
			var number = document.getElementById( 'ComplexTableField_Popup_DetailForm_TotalNumber' );
			if( ( this.className == 'checkbox' && ! this.checked ) || ( this.className == 'readonly' && this.innerHTML == 'no' ) )
				number.style.display = 'none';
		},
		onchange : function() {
			var number = document.getElementById( 'ComplexTableField_Popup_DetailForm_TotalNumber' );
			if( this.checked )
				number.style.display = 'block';
			else
				number.style.display = 'none';
		}
	},
	'#ComplexTableField_Popup_DetailForm_NumberPerMemberLimited' : {
		initialise : function() {
			var number = document.getElementById( 'ComplexTableField_Popup_DetailForm_NumberPerMember' );
			if( ( this.className == 'checkbox' && ! this.checked ) || ( this.className == 'readonly' && this.innerHTML == 'no' ) )
				number.style.display = 'none';
		},
		onchange : function() {
			var number = document.getElementById( 'ComplexTableField_Popup_DetailForm_NumberPerMember' );
			if( this.checked )
				number.style.display = 'block';
			else
				number.style.display = 'none';
		}
	}
});
