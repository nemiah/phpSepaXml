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

	function __construct($data = null) {
		$data["name"] = str_replace(array("&", "³"), array("und", "3"), $data["name"]);
		
		parent::__construct($data);
	}
	
	public function XMLDirectDebit(\SimpleXMLElement $xml) {
		$xml->addChild('Cdtr')->addChild('Nm', htmlentities($this->name));
		$xml->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));
		$xml->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$xml->addChild('ChrgBr', 'SLEV');
		
		$CdtrSchmeId = $xml->addChild('CdtrSchmeId');
		
		$Othr = $CdtrSchmeId->addChild('Id')->addChild('PrvtId')->addChild('Othr');
		$Othr->addChild('Id', $this->identifier);
		$Othr->addChild('SchmeNm')->addChild('Prtry', 'SEPA');
	}
	
	public function XMLTransfer(\SimpleXMLElement $xml) {
		$this->bic = str_replace(" ", "", $this->bic);
		$this->iban = str_replace(" ", "", $this->iban);
		
		$CdtTrfTxInf = $xml->addChild('CdtTrfTxInf');
		$CdtTrfTxInf->addChild('PmtId')->addChild('EndToEndId', $this->paymentID);
		
		$Amt = $CdtTrfTxInf->addChild("Amt");
		
		$InstdAmt = $Amt->addChild('InstdAmt', $this->amount);
		$InstdAmt->addAttribute('Ccy', $this->currency);


		$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$CdtTrfTxInf->addChild('Cdtr')->addChild('Nm', $this->fixNm($this->name));
		$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));

		$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}
}