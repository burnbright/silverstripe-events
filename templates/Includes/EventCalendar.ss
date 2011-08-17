<% control Months %>
	<table id="$ID" class="month" cellspacing="0">
	
		<thead>
			<tr class="caption"><td class="caption" colspan="7">$Caption</td></tr>
			<tr class="week">
				<td>Sun</td><td>Mon</td><td>Tue</td><td>Wed</td><td>Thu</td><td>Fri</td><td>Sat</td>
			</tr>
		</thead>
		
		<tbody>
			<% if LeadingGhostDates %>
			<tr>
			<% control LeadingGhostDates %>
			<td></td>
			<% end_control %>
			<% end_if %>
			
			<% control Dates %>
				<% if IsFirstDay %>
				<tr>
				<% end_if %>
					<td <% if IsToday %>id="calendar_today" <% if HasEvent %>class="event_link today"<% else %>class="today"<% end_if %><% else %><% if HasEvent %>class="event_link"<% end_if %><% end_if %>>
					<% if HasEvent %>
					<a href="$Link#$Val" title="$Event.Title">$NumVal</a>
					<% else %>
					$NumVal
					<% end_if %>
					</td>
				<% if IsLastDay %>
				</tr>
				<% end_if %>
			<% end_control %>
			
			<% if TailingGhostDates %>
			<% control TailingGhostDates %>
			<td></td>
			<% end_control %>
			</tr>
			<% end_if %>
		</tbody>
		
	</table>
<% end_control %>
