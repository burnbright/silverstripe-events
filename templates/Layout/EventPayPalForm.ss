<form target="paypal" id="TicketPackPurchase" action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<!-- a buy now button is represented by the command _xclick -->
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="business" value="$Business" />
	<input type="hidden" name="return" value="$SuccessfulPaymentLink" />
	<input type="hidden" name="notify_url" value="$PaymentNotifyLink" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="currency_code" value="NZD" />
	<input type="hidden" name="rm" value="2" />
	<input type="hidden" name="invoice" value="$ID" />
	<input type="hidden" name="image_url" value="$Logo" />
	<!-- add the item to the PayPal-hosted shopping cart -->
	<% control ContinueCountItems %>
	<% if IsNotZero %>
	<input type="hidden" name="item_name_$CountID" value="$Title" />
	<input type="hidden" name="quantity_$CountID"  value="$Quantity" />
	<input type="hidden" name="amount_$CountID" value="$Price" />
	<input type="hidden" name="on0_$CountID" value="$Type" />
	<input type="hidden" name="os0_$CountID" value="$Explanation" />
	<% end_if %>
	<% end_control %>

		<div id="SubmitButtons">
			<input class="submit" type="submit" value="PURCHASE" />
		</div>
</form>