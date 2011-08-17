<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
		<title>$Subject</title>
	</head>
	<body style="font-family:Tahoma,Arial,sans-serif;font-size:12px;">
		<table width="600" border="0" style="border-collapse:collapse;border:none;margin:0 auto;" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<td>
						<h1>$Subject</h1>
					</td>
				</tr>
			</thead>
			<tbody>
				<% if Event.ReceiptContent %>
				<tr>
					<td class="typography">$Event.ReceiptContent</td>
				</tr>
				<% end_if %>
				<tr>
					<% control Registration %>
					<td colspan="2">
						<% include RegistrationInformation %>
					</td>
					<% end_if %>
				</tr>
				
			</tbody>
			<% include EmailFooter %>
		</table>
	</body>
</html>