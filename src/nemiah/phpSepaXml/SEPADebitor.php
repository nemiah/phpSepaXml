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
	public $transferID = "";
	public $mandateID = "";
	public $mandateDateOfSignature = "";
	public $name = "";
	
	public $addressLine1 = "";
	public $addressLine2 = "";
	public $street = "";
	public $buildingNumber = "";
	public $postalCode = "";
	public $city = "";
	public $country = "";
	public $group = "";
	
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
	
	public function XMLTransfer(\SimpleXMLElement $xml) {
		$xml->addChild('Dbtr')->addChild('Nm', $this->fixNm($this->name));
		$xml->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));
		$xml->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$xml->addChild('ChrgBr', 'SLEV');
	}
	
	public function XMLDirectDebit(\SimpleXMLElement $xml) {
		$this->bic = str_replace(" ", "", $this->bic);
		$this->iban = str_replace(" ", "", $this->iban);
		
		$DrctDbtTxInf = $xml->addChild('DrctDbtTxInf');
		#$DrctDbtTxInf->addChild('PmtId')->addChild('EndToEndId', $this->transferID);

		$DrctDbtTxInf->addChild("PmtId")->addChild('EndToEndId', $this->endToEndId);
		
		
		$InstdAmt = $DrctDbtTxInf->addChild('InstdAmt', $this->amount);
		$InstdAmt->addAttribute('Ccy', $this->currency);

		$MndtRltdInf = $DrctDbtTxInf->addChild('DrctDbtTx')->addChild('MndtRltdInf');
		$MndtRltdInf->addChild('MndtId', $this->mandateID);
		
		if ($this->mandateDateOfSignature != '')
			$MndtRltdInf->addChild('DtOfSgntr', $this->mandateDateOfSignature);
		
		$MndtRltdInf->addChild('AmdmntInd', 'false');

		$DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		
		$Dbtr = $DrctDbtTxInf->addChild('Dbtr');
		$Dbtr->addChild('Nm', $this->fixNm($this->name));
		
		if(trim($this->addressLine1.$this->postalCode.$this->city.$this->country.$this->street) != ""){
			$PstlAdr = $Dbtr->addChild("PstlAdr");
			
			if($this->addressLine1 != "")
				$PstlAdr->addChild ("AdrLine", $this->fixNm($this->addressLine1));
			
			if($this->addressLine2 != "")
				$PstlAdr->addChild ("AdrLine", $this->fixNm($this->addressLine2));
			
			if($this->postalCode != "")
				$PstlAdr->addChild("PstCd", $this->postalCode);

			if($this->city != "")
			$PstlAdr->addChild("TwnNm", $this->city);

			if($this->country != "")
			$PstlAdr->addChild("Ctry", $this->country);

			if($this->street != "")
				$PstlAdr->addChild("StrtNm", $this->fixNm($this->street));

			if($this->buildingNumber != "")
				$PstlAdr->addChild("BldgNb", $this->buildingNumber);
		}
		
		$DrctDbtTxInf->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));

		if ($this->ultimateDebitor != '')
			$DrctDbtTxInf->addChild('UltmtDbtr')->addChild('Nm', $this->ultimateDebitor);
		
		$DrctDbtTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}
}