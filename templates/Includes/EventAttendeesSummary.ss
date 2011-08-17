<table id="EventAttendeesSummary">
	<thead>
		<tr><th>Name</th><th>Email</th><% if Tickets %><th>Ticket</th><% end_if %><th>Cost</th></tr>
	</thead>
	<% control Attendees %>
		<tr><td>$FirstName $Surname</td><td>$Email</td><% if Ticket %><td>$Ticket.Type</td><% end_if %><td>$Cost.Nice</td></tr>
	<% end_control %>
</table>