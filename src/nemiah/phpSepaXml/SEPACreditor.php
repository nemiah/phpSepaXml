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

		$Cdtr = $xml->addChild('Cdtr');
		$Cdtr->addChild('Nm', $this->fixNm($this->name));

        if($format=='pain.008.001.08' && trim($this->postalCode.$this->city.$this->country) != "") {
            if (!isset($PstlAdr))
                $PstlAdr = $Cdtr->addChild("PstlAdr");

            if ($this->department != "")
                $PstlAdr->addChild("Dept", $this->fixNm($this->department));

            if ($this->subDepartment != "")
                $PstlAdr->addChild("SubDept", $this->fixNm($this->subDepartment));

            if ($this->street != "")
                $PstlAdr->addChild("StrtNm", $this->fixNm($this->street));

            if ($this->buildingNumber != "")
                $PstlAdr->addChild("BldgNb", $this->buildingNumber);

            if ($this->buildingName != "")
                $PstlAdr->addChild("BldgNm", $this->fixNm($this->buildingName));

            if ($this->floor != "")
                $PstlAdr->addChild("Flr", $this->fixNm($this->floor));

            if ($this->postBox != "")
                $PstlAdr->addChild("PstBx", $this->fixNm($this->postBox));

            if ($this->room != "")
                $PstlAdr->addChild("Room", $this->fixNm($this->room));

            if ($this->postalCode != "")
                $PstlAdr->addChild("PstCd", $this->postalCode);

            if ($this->city != "")
                $PstlAdr->addChild("TwnNm", $this->fixNm($this->city));

            if ($this->townLocationName != "")
                $PstlAdr->addChild("TwnLctnNm", $this->fixNm($this->townLocationName));

            if ($this->disctrictName != "")
                $PstlAdr->addChild("DstrctNm", $this->fixNm($this->disctrictName));

            if ($this->countrySubDivision != "")
                $PstlAdr->addChild("CtrySubDvsn", $this->fixNm($this->countrySubDivision));

            if ($this->country != "")
                $PstlAdr->addChild("Ctry", $this->country);

        }

        //Hybrid Adresse
        if(trim($this->addressLine1.$this->addressLine2) != ""){
            if (!isset($PstlAdr))
                $PstlAdr = $Cdtr->addChild("PstlAdr");

            if($this->addressLine1 != "")
                $PstlAdr->addChild("AdrLine", $this->fixNm($this->addressLine1));

            if ($this->addressLine2 != "")
                $PstlAdr->addChild("AdrLine", $this->fixNm($this->addressLine2));
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
		$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));

		$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}
}