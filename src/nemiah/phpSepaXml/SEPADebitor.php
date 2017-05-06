<?php
/**
 * php-sepa-xml
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2017 Furtmeier Hard- und Software
 * @link      https://github.com/nemiah/php-sepa-xml
 *
 * @author    Nena Furtmeier <support@furtmeier.it>
 */

namespace nemiah\phpSepaXml;

class SEPADebitor extends SEPAParty {
	public $transferID = "";
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
	
	public function XML(\SimpleXMLElement $xml) {
		$this->bic = str_replace(" ", "", $this->bic);
		$this->iban = str_replace(" ", "", $this->iban);
		
		$DrctDbtTxInf = $xml->addChild('DrctDbtTxInf');
		$DrctDbtTxInf->addChild('PmtId')->addChild('EndToEndId', $this->transferID);

		$InstdAmt = $DrctDbtTxInf->addChild('InstdAmt', $this->amount);
		$InstdAmt->addAttribute('Ccy', $this->currency);

		$MndtRltdInf = $DrctDbtTxInf->addChild('DrctDbtTx')->addChild('MndtRltdInf');
		$MndtRltdInf->addChild('MndtId', $this->mandateID);
		
		if ($this->mandateDateOfSignature != '')
			$MndtRltdInf->addChild('DtOfSgntr', $this->mandateDateOfSignature);
		
		$MndtRltdInf->addChild('AmdmntInd', 'false');

		$DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$DrctDbtTxInf->addChild('Dbtr')->addChild('Nm', mb_substr(str_replace(array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß", "&"), array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss", "und"), $this->name), 0, 70));
		$DrctDbtTxInf->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));

		if ($this->ultimateDebitor != '')
			$DrctDbtTxInf->addChild('UltmtDbtr')->addChild('Nm', $this->ultimateDebitor);
		
		$DrctDbtTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}
}