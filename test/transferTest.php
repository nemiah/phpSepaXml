<?php

#require_once '../src/php-sepa-xml';
use nemiah\phpSepaXml\SEPATransfer;
use nemiah\phpSepaXml\SEPACreditor;
use nemiah\phpSepaXml\SEPADebitor;

class transferTest extends PHPUnit_Framework_TestCase {
	
	public function test() {
		
		$dt = new \DateTime();
		$dt->add(new \DateInterval("P1D"));

		$sepaT = new SEPATransfer(array(
			'messageID' => time(),
			'paymentID' => time()
		));
		
		$sepaT->setDebitor(new SEPADebitor(array( //this is you
			'name' => 'My Company',
			'iban' => 'DE68210501700012345678',
			'bic' => 'DEUTDEDB400',
			'identifier' => 'DE98ZZZ09999999999'
		)));

		$sepaT->addCreditor(new SEPACreditor(array(
			'paymentID' => 'Invoice 130904-131',
			'name' => 'Max Mustermann',
			'iban' => 'CH9300762011623852957',
			'bic' => 'GENODEF1P15',
			'amount' => 0.01,
			'currency' => 'EUR',
			'reqestedExecutionDate' => $dt
		)));
		
		$sepaT->toXML();
	}
}