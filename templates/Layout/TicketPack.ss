<table id="tickpack">
	<tr>
		<td class="label">
			Your selection:
		</td>
		<td>
			<% control Items %>
				<% if IsZero %><% else %>$Quantity $Title<br /><% end_if %>
			<% end_control %>
		</td>
	</tr>
	<tr>
		<td class="label">
			Date:
		</td>
		<td>
			$Date
		</td>
	</tr>
	<tr>
		<td class="label">
			Time:
		</td>
		<td>
			$StartTime
		</td>
	</tr>
	<tr>
		<td class="label">
			Wheelchairs #:
		</td>
		<td>
			$Wheelchairs
		</td>
	</tr>	
</table>

$ChangeButton
