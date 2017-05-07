<?php

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

		$sepaT->addCreditor(new SEPACreditor(array( //this is who you want to send money to
			#'paymentID' => '20170403652',
			'info' => '20170403652',
			'name' => 'Max Mustermann',
			'iban' => 'CH9300762011623852957',
			'bic' => 'GENODEF1P15',
			'amount' => 48.78,
			'currency' => 'EUR',
			'reqestedExecutionDate' => $dt
		)));
		
		file_put_contents(__DIR__."/output/transferTest.xml", $sepaT->toXML());
	}
}