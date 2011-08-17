Behaviour.register({
	'#Tickets input' : {
		onblur : EventCostChanged
	}
});
function EventCostChanged(){ 
	//Get the associated price quantities
	quantities =  $("Tickets").getElementsByTagName("input");
	
	//get the number of rsvp's per ticket
	rsvps = $("Rsvps").getElementsByTagName("input");
	
	price = calcTotalPrice(quantities);

	totalNumTickets = totalTickets(quantities,rsvps);
	
	if(price != 0) 	//Update the Amount
		setOrderAmount(price,totalNumTickets);	
}

function calcTotalPrice(quantities){
	//Calculate the totals from the prices 
	if(quantities.length > 0){
		price=0;
		for(i = 0; i < quantities.length; i++){
			if(quantities[i].value){
				priceid = quantities[i].id.replace("Tickets","Price");
				price += ($(priceid).value) * (quantities[i].value);
			}
		}
	}
	return price;
}
function totalTickets(selectedQuantites,numTickets){
	if(numTickets.length > 0){
		totalNumTickets = 0;
		for(i = 0; i < numTickets.length ; i++){		
				
			if(numTickets[i].value && selectedQuantites[i].value >= 1 ){
				totalNumTickets = totalNumTickets +  (numTickets[i].value * selectedQuantites[i].value);
			}		
		}
	}
	return totalNumTickets;
}

function setOrderAmount(price,totalNumTickets){
	$('EventRegistrationform_EventRegistrationform_Amount').innerHTML = currency(price,"$");
	amount = $('Amount');
	amount.getElementsByTagName("input")[0].value = currency(price,"$");
	amount.getElementsByTagName("label")[0].innerHTML = "Price for " + totalNumTickets + " attending:";

}
function currency(val, glyph) {
	if(glyph == null) glyph = '$'
	if(val == 0) return glyph + "0.00";
	val = Math.round(val * 100).toString();
	return glyph + val.substr(0, val.length - 2) + '.' + val.substr(val.length - 2);
}
function show(element){
	Element.removeClassName(element,"hide");
}
function hide(element){
	Element.addClassName(element,"hide");
}

