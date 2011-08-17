<h2>Registration #{$ID}</h2>
<% if Attendees %>
	<table width="100%" border="1" style="border-collapse:collapse;">
		<tr><th colspan="4"  align="left" bgcolor="#eeeeee">Event Attendees</th></tr>
		<tr>
			<tr>
				<th>Name</th>
				<th>Email</th>
				<% if Event.Tickets %><th>Ticket</th><% end_if %>
				<th>Cost</th>
			</tr>
		</tr>
		<% control Attendees %>
			<tr>
				<td>$FirstName $Surname</td>
				<td>$Email</td>
				<% if Ticket %><td>$Ticket.Type</td><% end_if %>
				<td>$Cost.Nice</td>
			</tr>
		<% end_control %>
		<tr><td colspan="3" align="right">Total:</td><td>$TotalCost.Nice</td></tr>
	</table>
	<br/>
<% end_if %>

<% if Notes %>
<h4>Registration Notes</h4>
<p>$Notes</p>
<% end_if %>

<% if Payment %>
<table width="100%" border="1" style="border-collapse:collapse;">
	<tr class="gap">
		<th colspan="2" scope="row" class="corner" align="left" bgcolor="#eeeeee">Payment Information</th>
	</tr>
	<% control Payment %>
		<tr class="summary">
			<td  scope="row" class="right">Payment ID</td><td class="price">#{$ID}</td>
		</tr> 
		<tr class="summary">
			<td scope="row" class="right">Method</td><td class="price">$PaymentMethod ($LastEdited.Date)</td>
		</tr>
		<tr class="summary">
			<td scope="row" class="right" <% if Status == Pending %>style="color:blue;"<% end_if %>>Payment Status</td>
			<td class="price">$Status</td>
		</tr>
		<% if Message %>
			<tr class="summary">
				<td scope="row" class="right" colspan="2">Details:</td>
			</tr>
			<tr class="summary">
				<td class="price" colspan="2">$Message</td>
			</tr>
		<% end_if %>
		<tr class="gap Total">
			<td scope="row" class="right">Total Cost</td><td class="price"><strong>$Amount.Nice</strong></td>
		</tr>
	<% end_control %>
</table>
<% end_if %>