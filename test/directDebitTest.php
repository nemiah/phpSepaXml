<?php

#require_once '../src/php-sepa-xml';
use nemiah\phpSepaXml\SEPABasisLastschrift;
use nemiah\phpSepaXml\SEPACreditor;
use nemiah\phpSepaXml\SEPADebitor;
use nemiah\phpSepaXml\SEPAParty;

class directDebitTest extends PHPUnit_Framework_TestCase {
	
	public function test() {
		$dt = new \DateTime();
		$dt->add(new \DateInterval("P8D"));

		$sepaDD = new SEPABasisLastschrift(array(
			'messageID' => time(),
			'paymentID' => 'TRF-INVOICE-130904',
			'requestedCollectionDate' => $dt
		));

		$sepaDD->setCreditor(new SEPACreditor(array(
			'name' => 'My Company',
			'iban' => 'DE68210501700012345678',
			'bic' => 'DEUTDEDB400',
			'identifier' => 'DE98ZZZ09999999999'
		)));
		

		$sepaDD->addDebitor(new SEPADebitor(array(
			'transferID' => 'Invoice 130904-131',
			'mandateID' => '37294',
			'mandateDateOfSignature' => '2013-07-14',
			'name' => 'Max Mustermann',
			'iban' => 'CH9300762011623852957',
			'bic' => 'GENODEF1P15',
			'amount' => 0.01,
			'currency' => 'EUR',
			'info' => 'Info text. Invoice 130904-131',
			'requestedCollectionDate' => $dt,
			'sequenceType' => "OOFF"
		)));
		
		
		try {
			#echo $sepaDD->toXML();
			return true;
		} catch (Exception $e){
			echo "<p>".$sepaDD->errors()."</p>";
		}
	}
}