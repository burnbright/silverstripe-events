<?php

class EventTest extends SapphireTest{

	static $fixture_file = 'events/tests/EventsTesting.yml';

	function setUp(){
		parent::setUp();
	}

	//Unit Tests

	function testAllBasicFunctions(){

		$event = $this->objFromFixture('Event', 'upcomingconcert');

		$this->assertFalse($event->InPast());
		$this->assertTrue($event->InFuture());
		$this->assertTrue($event->IsAvailable());
		$this->assertFalse($event->IsCancelled());
		$this->assertTrue($event->IsRunning());
		$this->assertTrue($event->HasSparePlaces());
		$this->assertEquals($event->getTotalPlaces(),5);
		$this->assertEquals($event->getPlacesLeft(),4);
		$this->assertTrue($event->canRegister()); //anyone can register
		$this->assertFalse($event->checkAlreadyBooked()); //no member passed

		//$this->assertFalse($event->IsOneDayEvent());

		//TODO: test cannot register cases
			//event was cancelled
			//event has passed
			//member already registered
			//no places left / registrations closed
			//not a member
			//incorrect member group
	}

	//Functional Tests

	function testRegister(){

	}

	//TODO: add existing member, and registrations/attendees

	//write tests for

	//registration
		//multi-member
	//emails
	//payment

}