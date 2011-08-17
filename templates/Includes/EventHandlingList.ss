<% control EventsToHandle %>
<div id="EventHandlingList">
	<% if Children %>
	<h2> The following <% if Plural %>events are <% else %>event is<% end_if %>waiting for approval</h2>
	<ul class="border">
		<% control Children %>
			<li class="$ClassStatus">
				<a href="$HandlingLink" title="To handle this event" >Title: $Title<br />
				Created: $Created.Date<br />
				Last Edited: $LastEdited.Date<br />
				<% if StartDate %>Date: $StartDate.Nice<br /><% end_if %>
				<% if LiteralStatus %>Status: $LiteralStatus<br /><% end_if %>
				<% if CreatedBy %>Owner: $CreatedBy.FirstName<% end_if %>
				</a>
			</li>
		<% end_control %>
	</ul>
	<% else %>
		<% if Message %>
			<h2> $Message </h2>
		<% end_if %>
	<% end_if %>
</div>
<% end_control %>
