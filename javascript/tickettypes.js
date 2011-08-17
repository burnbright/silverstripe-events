Behaviour.register({
	'#TicketType select' : {
		initialize: ticketChanged,
		onchange: ticketChanged
	}
});

function ticketChanged() {
	descriptions = $('TicketDescriptions').getElementsByTagName('p');
	for(i = 0; i < descriptions.length; i++) {
		descriptions[i].style.display = 'none';
	}
	
	branch = 'TicketDescription' + $('EventRegistrationForm_BookingForm_TicketType').value;
	$(branch).style.display = 'block';
}

