<% if EventOccurances %>
	<ul id="EventDates">
		<% control EventOccurances %>
			<li>
				<p><strong>$Date.Day $Date.Long</strong><br/>
				{$StartTime.Time}-{$FinishTime.Time}</p>
			</li>
		<% end_control %>
	</ul>
<% end_if %>
$Content
<% if EventFlyer.ID %>
	<% control EventFlyer %>
		<div id="Flyer">
			<h3>Event Flyer</h3>
			<p><a href="$Link" title="$Title" >$Title - $Size</a></p>
		</div>
	<% end_control %>
<% end_if %>