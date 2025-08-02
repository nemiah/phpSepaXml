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
	public $endToEndId = "NOTPROVIDED";

    public $street = "";
    public $buildingNumber = "";
    public $postalCode = "";
    public $city = "";
    public $country = "";
    public $department = "";
    public $subDepartment = "";
    public $buildingName = "";
    public $floor = "";
    public $postBox = "";
    public $room = "";
    public $townLocationName = "";
    public $disctrictName = "";
    public $countrySubDivision = "";
    public $addressLine1 = "";
    public $addressLine2 = "";
	
	public function XMLDirectDebit(\SimpleXMLElement $xml, $format) {

        $this->bic = $this->fixNm(str_replace(" ", "", $this->bic),11);
        $this->iban = $this->fixNm(str_replace(" ", "", $this->iban),34);

        $Cdtr = $xml->addChild('Cdtr');
		$Cdtr->addChild('Nm', $this->fixNm($this->name));
        $this->addPostalAddress($Cdtr, $format);
		
		$xml->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', $this->iban);

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

    public function validateRequiredFields($context = null) {
        parent::validateRequiredFields($context);

        $errors = [];
        if (empty($this->identifier)) $errors[] = "Identifier fehlt";

        if (!empty($errors)) {
            throw new \Exception("Fehlende oder ungültige Pflichtfelder (Debitor): " . implode(", ", $errors));
        }
    }

	public function XMLTransfer(\SimpleXMLElement $xml) {
		$this->bic = str_replace(" ", "", $this->bic);
		$this->iban = str_replace(" ", "", $this->iban);
		
		$CdtTrfTxInf = $xml->addChild('CdtTrfTxInf');

		$CdtTrfTxInf->addChild("PmtId")->addChild('EndToEndId', $this->endToEndId);
		
		
		$Amt = $CdtTrfTxInf->addChild("Amt");
		
		$InstdAmt = $Amt->addChild('InstdAmt', $this->amount);
		$InstdAmt->addAttribute('Ccy', $this->currency);


		$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$CdtTrfTxInf->addChild('Cdtr')->addChild('Nm', $this->fixNm($this->name));
		$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', $this->iban);

		$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}
}