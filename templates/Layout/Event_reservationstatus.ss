<div id="RightContent">
	<% if RegistrationStatus %>
		<% control RegistrationStatus %>
			<% include EventRegistrationInformation %>			
		<% end_control %>
	<% else %>
		<h2>Sorry, to view this page, you must be logged in</h2>
		$LoginForm
	<% end_if %>
</div>
<div id="Content" class="event">
	<div class="typography">
		$Content
	</div>
	<% if Form %>$Form<% end_if %>
	<% if PageComments %>$PageComments<% end_if %>
</div>
