<?php
/**
 * Stores status changes for EventRegistration objects.
 * @package events
 */
class RegistrationStatus extends DataObject {
	
	public static $db = array(
		'RegistrationStatus' => "Enum('New,Invited,Accepted,Registered,Declined,Pending,Complete','New')",
		'Notes' => 'Text'
	);

	public static $has_one = array(
		'EventRegistration' => 'EventRegistration',
		'Member' => 'Member',
		'Payment' => 'Payment'
	);
	
	public static $has_many = array();
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	public static $defaults = array();
	
	/**
	 * A map of payment statuses to EventRegistration statuses.
	 *  all payments should assign the status to be on of these
	 *    - Success => Complete (Payment accepeted so the EventRegistration is complete)
	 *    - Pending => Pending  (We're awaiting some sort of payment)
	 *    - Failure => Pending  (Payment Failed, so we're holding the EventRegistration for them)
	 *    - Incomplete => Accepted	(Something went wrong with the transaction, so don't change the status.)
	 * (Accepted just means the member wants to go to the event, however they dont have a place yet)
	 */
	static $statusCastingMap = array(
		'Success' => 'Complete',
		'Failure' => 'Pending',
		'Pending' => 'Pending',
		'Incomplete' => 'Accepted'
	);

	/**
	 * Changes the status of the payment based on the payment type
	 * @param status -  the result from Payment::processPayment();
	 * - 
	 */
	static function updateStatus($rsvp, $member, $RegistrationStatus, $payment = null, $notes = null) {
		// Generate the new object
		$status = new RegistrationStatus();
		$status->setField('MemberID', $member->ID);
		$status->setField('EventRegistrationID', $rsvp->ID);
		
		// They status is automatically set by the payment
		if($payment) {
			$status->setField('PaymentID', $payment->ID);
			$status->setField('RegistrationStatus', self::$statusCastingMap[$payment->Status]);
		} else {
			$status->setField('RegistrationStatus', $RegistrationStatus);
		}
		
		// If theres no notes, assume it has been set by SilverStripe.
		$notes ? $status->setField('Notes', $notes) : $status->setField('Notes', 'Status updated by SilverStripe');
		$status->write();
		
		return $status;
	}
	
}
?>