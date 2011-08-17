<div id="EventList">
	<h2>
	<% if ListTitle %>
	$ListTitle
	<% end_if %>
	</h2>
	<% if Children %>
	<ul class="border">
		<% control Children %>
			<li id="$StartDate">
				<h4><a href="$Link" title="View $Title Event Details" >$Title</a></h4>
				
					<div class="info">
						<% if IsOneDayEvent %>
							<span class="date"><a name="$StartDate">$StartDate.Day $StartDate.Long</a></span>
							<% control EventOccurances %>
							<% if StartTime %>
								<br />$StartTime.Nice
							<% end_if %>
							<% if FinishTime %>
								- $FinishTime.Nice
							<% end_if %>
							<% end_control %>
						<% else %>
						<span class="date"><a name="$StartDate">$StartDate.Day $StartDate.Long</a> - <a>$FinishDate.Day $FinishDate.Long</a></span>
						<% end_if %>
					</div>

				<% if ShortDescription %><div>$ShortDescription</div><% end_if %>
				<div class="detailInfo">
					<% if EventContact %>
					<strong>Further information: </strong>$EventContact<br />				
					<% end_if %>
				</div>
			</li>
		<% end_control %>
	</ul>
	<% else %>
	<h4>Sorry, no event meets the search the search criteria.</h4>
	<% end_if %>
</div>
