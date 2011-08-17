<% if Tickets %>
<h2>Ticket Options</h2>
<table class="ticketdescriptions">
	<tr>
		<th>Ticket</th><th>Price</th><th>Places Left</th>
	</tr>
	<% control Tickets %>
		<tr>
			<td><strong>$Type</strong></td><td>$Price.Nice</td><td>$RemainingPlaces</td>
		</tr>
		<% if Description %>
		<tr>
			<td colspan="5">$Description</td>
		</tr>
		<% end_if %>
	<% end_control %>
</table>
<% end_if %>