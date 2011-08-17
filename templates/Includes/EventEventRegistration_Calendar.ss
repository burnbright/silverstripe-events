<% control Months %>
<table class="eventsTable $FirstLast">
<thead>
	<tr>
		<th colspan="2">$Literal</th>
		<% control Sessions %>
		<th>$Header</th>
		<% end_control %>
	</tr>
</thead>

<tbody>
	<% control Dates %>
	<tr class="$WeekdayWeekend">
		<td>$Literal</td>
		<td>$OrdinalNum</td>
		<% control Sessions %>
		<td class="ticketAmount">
			<% if IsGhost %>
			&nbsp;
			<% else %>
			<a href="$Link" title="Click here to book">$PlacesLeft</a>
			<% end_if %>
		</td>
		<% end_control %>
	</tr>
	<% end_control %>
</tbody>
</table>
<% end_control %>

