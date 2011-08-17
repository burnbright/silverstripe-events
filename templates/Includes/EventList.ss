<div id="EventList">
	<h2>Current Events</h2>
		<% if Children %>
			<ul class="border">
				<% control Children %>
					<li>
						<h4><a href="$Link" title="View $Title Event Details" >$Title</a></h4>
						<% control EventOccurances %>
						  <div class="info"><span class="date"><a name="$Date" title="Use this date as a filter" >$Date.Day $Date.Long</a></span>
						  <span class="time">{$StartTime.Time} - {$FinishTime.Time}</span></div>
						<% end_control %>
						<% if Description %><div>$Description</div><% end_if %>
					</li>
				<% end_control %>
			</ul>
			<% else %>
			<ul><li><h4>Sorry, no events are coming up shortly in your region.</h4></li></ul>
		<% end_if %>
</div>
