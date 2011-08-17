<?php

/**
 * This is legacy code, but it's the only implementation of payment.
 * We'll need to cherry-pick from here
 * @package events
 */
class EventBooking extends Page_Controller {

	function __construct() {
		parent::__construct(null);
	}
	
	function init() {
		if(Director::fileExists(project()."/css/events.css")) Requirements::css(project()."/css/events.css");
		else Requirements::css("events/css/events.css");
		parent::init();
	}
	
	public function Link($action = null) {
		if($action == "index") $action = "";
		return Director::baseURL() . "EventBooking/$action";
	}
	
	public function BookingForm(){
		if(class_exists('EventSessionBookingForm')){
			$f = new EventSessionBookingForm($this, "BookingForm");
			return $f;
		}else{
			echo "Here you should have you own customised eventsession booking form<br />";
			echo "Please define this form class and save under your own project folder";
			die();
		}
	}

	public function SignupForm(){
		
		if(class_exists('EventMemberSignupForm')){
			if($this->urlParams[Action]=='confirm'){
				$form = new EventMemberSignupForm($this, "SignupForm", 'confirm');
				$form->makeReadonly();
				$this->signupformReadonly = 1;
				if($_SESSION[PackInfo][PaymentFail]){
					$form->setMessage("Sorry, you payment has been failed, please validate your payment account and try again!", 'bad');
					$_SESSION[PackInfo][PaymentFail] = null;
				}
				$form->onAddChangeButton();
			}else{
				$form = new EventMemberSignupForm($this, "SignupForm");
				$this->signupformReadonly = 0;
			}
			
			if($member=Member::currentUser()){
				$form->loadDataFrom($member);
			}
			return $form;
		}else{
			echo "Here you should have you own customised event member signup form<br />";
			echo "Please define this form class and save under your own project folder";
			die();
		}
	}
	
	protected $signupformReadonly;
	function ChangeButton(){
		if($this->signupformReadonly){
			$link = Director::BaseURL()."EventBooking/yourdetails";
			
			return <<<HTML
				<form id="changebutton_Member" action="$link" method="post">
<input type="submit" title="change your details" class="button" value="Change" />
</form>
HTML;
		}
	}
	
	function TotalPrice(){
		$ticketPack = new TicketPack();
		$totalPrice = $ticketPack->TotalPrice();
		return "Total price: $$totalPrice <span class=\"small\">incl. GST</span>";
	}
	
	public function signupEventMember($data, $form){

		//save member
		$member = Object::create("EventMember");
		$form->saveInto($member);
		$member->write();
		$member->login();
		
		//save the member to SessionEventRegistration
		$ticketPack = new TicketPack();
		$sessionEventRegistration = DataObject::get_by_id("SessionEventRegistration", $ticketPack->getID());
		$sessionEventRegistration->setField("MemberID", $member->ID);
		$sessionEventRegistration->write();
		
		
		//If checked :: add this member to Group for newsletter "Site-specific productions"
		$newsletterType = DataObject::get_one("NewsletterType", "Title = 'Site-specific productioins'");
		if($data['Site-specificInfo']&&!DB::query("SELECT ID FROM `Group_Members` WHERE `GroupID` = $newsletterType->GroupID AND `MemberID` = $member->ID")->record()){
			DB::query("INSERT INTO `Group_Members` (`MemberID`, `GroupID`) VALUES($member->ID, $newsletterType->GroupID)");
		}

		//direct to 
		Director::redirect("confirm?flush=1");
	}
	
	public function PaymentForm(){
		$ticketPack = new TicketPack();
		return $ticketPack->renderWith("EventPayPalForm");
	}
	
	public function notified(){
		BasicAuth::protect_entire_site(false);
		
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		
		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
		
		// assign posted variables to local variables
		$payment_status = $_POST['payment_status'];
		$txn_id = $_POST['txn_id'];
		$EventRegistrationID = $_POST['invoice'];
		$receiver_email = $_POST['receiver_email'];
		
		if (!$fp) {
			user_error($errno.": ".$errstr, E_USER_ERROR);
		} else {
			fputs ($fp, $header . $req);

			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				
				if (strcmp ($res, "VERIFIED") == 0) {
					$sessionEventRegistration = DataObject::get_by_id("SessionEventRegistration",  $EventRegistrationID);
					if($payment_status == 'Completed'){
						$sessionEventRegistration->Success = 'Success';
						$sessionEventRegistration->PaymentReference = $txn_id;
		
						// if not send notifying Email, sent here
						if(!$sessionEventRegistration->Notifed){
							$_REQUEST[sessionEventRegistration] =  $EventRegistrationID;
							if(SSViewer::hasTemplate("EmailHeader")) {
								$emailHeader = $this->renderWith("EmailHeader");
							}
							$receipt = $this->renderWith("EventSessionEventRegistrationReceipt");
						
							$event = $this->getEvent();
							$from = $event->EventContactEmail;
							$to = $this->getSessionEventRegistrationMember()->Email;
							$subject = "Event EventRegistration Receipt";
							$body = $emailHeader.$receipt;
						
							$email = new Email($from, $to, $subject, $body);
							if($email->send()){
								$sessionEventRegistration->Notifed = 1;
							}
						}
					}else{
						$sessionEventRegistration->Success = 'Pending';
					}
					$sessionEventRegistration->write();
				}else if (strcmp ($res, "INVALID") == 0) {
					user_error("The Instant Payment Notification has been failed to verify.", E_USER_ERROR);
				}
			}
			fclose ($fp);
		}
	}
	
	public function paid(){
		$req = 'cmd=_notify-synch';

		$tx_token = $_GET['tx'];
		$auth_token = "Xh0SmuprGkJdKfTVaeMVtHAx6ntCyWTEzd0lqOuSZqAVuvLoHf2K5ZbgEpy";
		$req .= "&tx=$tx_token&at=$auth_token";

		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

		if (!$fp) {
			user_error($errno.": ".$errstr, E_USER_ERROR);
		} else {
			fputs ($fp, $header . $req);
			// read the body data
			$res = '';
			$headerdone = false;
			while (!feof($fp)) {
				$line = fgets ($fp, 1024);
				if (strcmp($line, "\r\n") == 0) {
					// read the header
					$headerdone = true;
				}else if ($headerdone){
					// header has been read. now read the contents
					$res .= $line;
				}
			}
			fclose ($fp);

			// parse the data
			$lines = explode("\n", $res);
			$keyarray = array();
			if (strcmp ($lines[0], "SUCCESS") == 0) {
				for ($i=1; $i<count($lines);$i++){
					list($key,$val) = explode("=", $lines[$i]);
					$keyarray[urldecode($key)] = urldecode($val);
				}
				$sessionEventRegistration = DataObject::get_by_id("SessionEventRegistration", $keyarray[invoice]);
				
				if($keyarray[payment_status] == 'Completed'){
					$sessionEventRegistration->Success = 'Success';
					$sessionEventRegistration->PaymentReference = $keyarray[txn_id];
					
					// if not send notifying Email, sent here
					if(!$sessionEventRegistration->Notifed){
						$_REQUEST[sessionEventRegistration] =  $keyarray[invoice];
						if(SSViewer::hasTemplate("EmailHeader")) {
							$emailHeader = $this->renderWith("EmailHeader");
						}
						$receipt = $this->renderWith("EventSessionEventRegistrationReceipt");
					
						$event = $this->getEvent();
						$from = $event->EventContactEmail;
						$to = $this->getSessionEventRegistrationMember()->Email;
						$subject = "Event EventRegistration Receipt";
						$body = $emailHeader.$receipt;
					
						$email = new Email($from, $to, $subject, $body);
						if($email->send()){
							$sessionEventRegistration->Notifed = 1;
						}
					}
					
					$sessionEventRegistration->write();
					Director::redirect("receipt?sessionEventRegistration=".$keyarray[invoice]);
				}else{
					$sessionEventRegistration->Success = 'Pending';
					$sessionEventRegistration->write();
					$_SESSION[PackInfo][PaymentFail] = 1;
					Director::redirect("confirm");
				}
				
			}else if (strcmp ($lines[0], "FAIL") == 0) {
				user_error("The Payment Data Transfer Synchronization has been failed.", E_USER_ERROR);
			}
		}
	}
	
	public function PrintReceiptButton(){
		$id = $_REQUEST[sessionEventRegistration];
		$link = Director::BaseURL()."EventBooking/printreceipt?sessionEventRegistration=$id&amp;flush=1";

		return <<<HTML
				<form id="printbutton_receipt" action="$link" method="post">
<input type="submit" title="print receipt" onclick="window.print();return false;" class="button" value="Print receipt" />
</form>
HTML;
	}
	
	public function TaxReceipt(){
		$rsvpID = $_REQUEST[sessionEventRegistration];
		$sessionEventRegistration = DataObject::get_by_id("SessionEventRegistration", $rsvpID);
		return <<<HTML
		<h3 class="receiptHeader">Tax receipt</h3>
<p id="orderReference">Order reference: <span class="orderNumber">$sessionEventRegistration->PaymentReference</span></p>
HTML;
	}
	
	public function getSessionEventRegistrationMember(){
		$rsvpID = $_REQUEST[sessionEventRegistration];
		$sessionEventRegistration = DataObject::get_by_id("SessionEventRegistration", $rsvpID);
		$member = Object::create("EventMember");
		$memberClass = get_class($member);
		return $customMember = DataObject::get_by_id($memberClass, $sessionEventRegistration->MemberID);
	}
	
	public function getEvent(){
		$rsvpID = $_REQUEST[sessionEventRegistration];
		$sessionEventRegistration = DataObject::get_by_id("SessionEventRegistration", $rsvpID);
		$event = $sessionEventRegistration->EventOccurance()->Event();
		return $event;
		
	}
	
	public function BuyerFirstName(){
		$customMember = $this->getSessionEventRegistrationMember();
		if($customMember->FirstName){
			return $customMember->FirstName;
		}else if($customMember->FullName){
			list($firstname) = split(" ", $customMember->FullName);
			return $firstname;
		}else{
			return $customMember->Surname;
		}
	}
	
	public function SessionEventRegistration(){
		$rsvpID = $_REQUEST[sessionEventRegistration];
		return DataObject::get_by_id("SessionEventRegistration", $rsvpID);
	}
}

?>
