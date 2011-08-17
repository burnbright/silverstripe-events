<div id="Layout">
	<div class="LeftPanel">
	</div>
	<div class="RightPanel">
		<div id="UserProfile">
			<% control Member %>
				<h2><% if Nickname %>$Nickname<% else %>Anon<% end_if %>&#39;s Profile</h2>
				
				<div><label class="left">Nickname:</label> <p><% if Nickname %>$Nickname<% else %>Anon<% end_if %></p></div>
				
				<% if FirstName %>
				<div><label class="left">First Name:</label> <p>$FirstName</p></div>
				<% end_if %>
		
				<% if Surname %>
				<div><label class="left">Surname:</label> <p>$Surname</p></div>
				<% end_if %>
		
				<% if Email %>
				<div><label class="left">Email:</label> <p><a href="mailto:$Email">$Email</a></p></div>
				<% end_if %>
		
				<% if Occupation %>
				<div><label class="left">Occupation:</label> <p>$Occupation</p></div>
				<% end_if %>
		
				<% if Country %>
				<div><label class="left">Country:</label> <p>$Country</p></div>
				<% end_if %>
			<% end_control %>
				
			<% if NumAdvertised %>
			<div><label class="left">Number of Events Advertised:</label> <p>$NumAdvertised</p></div>
			<% end_if %>
			
			<% if NumRegistered %>
			<div><label class="left">Number of Events Registered:</label> <p>$NumRegistered</p></div>
			<% end_if %>
			
			<% control Member %>
				<% if Avatar %>
					<div><label class="left">Avatar:</label> <p>
					<% control Avatar.SetWidth(80) %>
						<img class="userAvatar" src="$URL" alt="avatar" />
					<% end_control %> </p></div>
				<% else %>
					<div><label class="left">Avatar:</label> <p><img class="userAvatar" src="forum/images/forummember_holder.gif" width="80" alt="<% if Nickname %>$Nickname<% else %>Anon<% end_if %>&#39;s avatar" /></p></div>
				<% end_if %>
			<% end_control %>
		</div>

	</div>
</div>