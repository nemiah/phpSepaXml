<?php
/**
 * phpSepaXml
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2017 Furtmeier Hard- und Software
 * @link      https://github.com/nemiah/phpSepaXml
 *
 * @author    Nena Furtmeier <support@furtmeier.it>
 */

namespace nemiah\phpSepaXml;

class SEPADebitor extends SEPAParty {
	public $mandateID = "";
	public $mandateDateOfSignature = "";
	public $name = "";
	public $iban = "";
	public $bic = "";
	public $amount = 0;
	public $currency = 'EUR';
	public $info = "";
	public $ultimateDebitor = "";
	public $requestedCollectionDate = "";
	public $sequenceType = "OOFF";
	public $type = "COR1";
	public $endToEndId = "NOTPROVIDED";
	public $group = "";

	public function XMLDirectDebit(\SimpleXMLElement $xml, $format) {
		$this->bic = $this->fixNm($this->bic, 11);
		$this->iban = $this->fixNm($this->iban, 34);
		
		$DrctDbtTxInf = $xml->addChild('DrctDbtTxInf');
		$DrctDbtTxInf->addChild("PmtId")->addChild('EndToEndId', $this->endToEndId);
		
		$InstdAmt = $DrctDbtTxInf->addChild('InstdAmt', $this->amount);
		$InstdAmt->addAttribute('Ccy', $this->currency);

		$MndtRltdInf = $DrctDbtTxInf->addChild('DrctDbtTx')->addChild('MndtRltdInf');
		$MndtRltdInf->addChild('MndtId', $this->mandateID);
		
		if ($this->mandateDateOfSignature != '')
			$MndtRltdInf->addChild('DtOfSgntr', $this->mandateDateOfSignature);
		
		$MndtRltdInf->addChild('AmdmntInd', 'false');

		if($format=='pain.008.001.08' || $format === 'pain.001.001.09') {
			if ($this->bic != '')
				$DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BICFI', $this->bic);
			else
				$DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('Othr')->addChild('Id', 'NOTPROVIDED');
			
		} else 
			$DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		
		$Dbtr = $DrctDbtTxInf->addChild('Dbtr');
		$Dbtr->addChild('Nm', $this->fixNm($this->name));
		$this->addPostalAddress($Dbtr, $format);

		$DrctDbtTxInf->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', $this->iban);

		if ($this->ultimateDebitor != '')
			$DrctDbtTxInf->addChild('UltmtDbtr')->addChild('Nm', $this->ultimateDebitor);
		
		$DrctDbtTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}

	// In SEPADebitor.php
	public function validateRequiredFields($context = null) {
		parent::validateRequiredFields($context);

		$errors = [];

		if (empty($this->mandateID))
			$errors[] = "Mandatsreferenz fehlt";
		
		if (empty($this->mandateDateOfSignature))
			$errors[] = "Mandatsdatum fehlt";
		
		if (empty($this->amount) || $this->amount <= 0)
			$errors[] = "Betrag fehlt oder ungültig";
		
		if (empty($this->currency))
			$errors[] = "Währung fehlt";

		if (!empty($errors))
			throw new \Exception("Fehlende oder ungültige Pflichtfelder (Debitor): " . implode(", ", $errors));
	}


	public function XMLTransfer(\SimpleXMLElement $xml) {
		$xml->addChild('Dbtr')->addChild('Nm', $this->fixNm($this->name));
		$xml->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', $this->iban);
		$xml->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$xml->addChild('ChrgBr', 'SLEV');
	}

}