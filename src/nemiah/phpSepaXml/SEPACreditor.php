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

use nemiah\phpSepaXml\SEPAParty;

class SEPACreditor extends SEPAParty {
	public $name = "";
	public $iban = "";
	public $bic = "";
	public $identifier = "";
	public $reqestedExecutionDate = "";
	public $amount = 0;
	public $currency = "";
	public $info = "";
	public $paymentID = "NOTPROVIDED";
	public $endToEndId = "NOTPROVIDED";

	public $addressLine1 = "";
	public $addressLine2 = "";
	public $street = "";
	public $buildingNumber = "";
	public $postalCode = "";
	public $city = "";
	public $country = "";
	
	function __construct($data = null) {
		$data["name"] = str_replace(array("&", "³", "|"), array("und", "3", ""), $data["name"]);
		$data["name"] = str_replace(array("ö", "ä", "ü", "é"), array("oe", "ae", "ue", "e"), $data["name"]);
		$data["name"] = str_replace(array("Ö", "Ä", "Ü"), array("Oe", "Ae", "Ue"), $data["name"]);
		$data["name"] = str_replace(array("ß"), array("ss"), $data["name"]);
		
		parent::__construct($data);
	}
	
	public function XMLDirectDebit(\SimpleXMLElement $xml, $format) {
		#$xml->addChild('Cdtr')->addChild('Nm', htmlentities($this->name));
		
		$Cdtr = $xml->addChild('Cdtr');
		$Cdtr->addChild('Nm', $this->fixNm($this->name));
		
		if(trim($this->addressLine1.$this->postalCode.$this->city.$this->country.$this->street) != ""){
			$PstlAdr = $Cdtr->addChild("PstlAdr");
			
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
		
		$xml->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));

        if($format=='pain.008.001.08') {
            if($this->bic!='') {
                $xml->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BICFI', $this->bic);
            } else {
                $xml->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('Othr')->addChild('Id', 'NOTPROVIDED');
            }
        } else {
            $xml->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
        }

        $xml->addChild('ChrgBr', 'SLEV');
		
		$CdtrSchmeId = $xml->addChild('CdtrSchmeId');
		
		$Othr = $CdtrSchmeId->addChild('Id')->addChild('PrvtId')->addChild('Othr');
		$Othr->addChild('Id', $this->identifier);
		$Othr->addChild('SchmeNm')->addChild('Prtry', 'SEPA');
	}
	
	public function XMLTransfer(\SimpleXMLElement $xml, $format='') {
		$this->bic = str_replace(" ", "", $this->bic);
		$this->iban = str_replace(" ", "", $this->iban);
		
		$CdtTrfTxInf = $xml->addChild('CdtTrfTxInf');
		#$CdtTrfTxInf->addChild('PmtId')->addChild('EndToEndId', $this->paymentID);
		
		$CdtTrfTxInf->addChild("PmtId")->addChild('EndToEndId', $this->endToEndId);
		
		
		$Amt = $CdtTrfTxInf->addChild("Amt");
		
		$InstdAmt = $Amt->addChild('InstdAmt', $this->amount);
		$InstdAmt->addAttribute('Ccy', $this->currency);


		$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$CdtTrfTxInf->addChild('Cdtr')->addChild('Nm', $this->fixNm($this->name));
		$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));

		$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}
}