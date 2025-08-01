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

    public $department = "";
    public $subDepartment = "";
    public $buildingName = "";
    public $floor = "";
    public $postBox = "";
    public $room = "";
    public $townLocationName = "";
    public $disctrictName = "";
    public $countrySubDivision = "";

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
	
	public function XMLTransfer(\SimpleXMLElement $xml, $format='') {
		$xml->addChild('Dbtr')->addChild('Nm', $this->fixNm($this->name));
		$xml->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));
		$xml->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$xml->addChild('ChrgBr', 'SLEV');
	}

	public function XMLDirectDebit(\SimpleXMLElement $xml, $format) {
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

        if($format=='pain.008.001.08') {
            $DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BICFI', $this->bic);
        } else {
            $DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
        }
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

        /*
        Dept -> Abteilung/Bereich
        SubDept -> Unterabteilung/bereich
        BldgNm -> Gebäudename
        Flr -> Stockwerk/Etage
        PstBx -> Postfach
        Room -> Raumnummer
        TwnLctnNm -> Spezifischer Ortsname innerhalb einer Stadt
        DstrctNm -> Unterteilung innerhalb einer Region
        CtrySubDvsn -> Region
        */
        if($format=='pain.008.001.08') {
            if ($this->department != "")
                $PstlAdr->addChild("Dept", $this->fixNm($this->department));

            if ($this->subDepartment != "")
                $PstlAdr->addChild("SubDept", $this->fixNm($this->subDepartment));

            if ($this->buildingName != "")
                $PstlAdr->addChild("BldgNm", $this->fixNm($this->buildingName));

            if ($this->floor != "")
                $PstlAdr->addChild("Flr", $this->fixNm($this->floor));

            if ($this->postBox != "")
                $PstlAdr->addChild("PstBx", $this->fixNm($this->postBox));

            if ($this->room != "")
                $PstlAdr->addChild("Room", $this->fixNm($this->room));

            if ($this->townLocationName != "")
                $PstlAdr->addChild("TwnLctnNm", $this->fixNm($this->townLocationName));

            if ($this->disctrictName != "")
                $PstlAdr->addChild("DstrctNm", $this->fixNm($this->disctrictName));

            if ($this->countrySubDivision != "")
                $PstlAdr->addChild("CtrySubDvsn", $this->fixNm($this->countrySubDivision));
        }


        $DrctDbtTxInf->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));

		if ($this->ultimateDebitor != '')
			$DrctDbtTxInf->addChild('UltmtDbtr')->addChild('Nm', $this->ultimateDebitor);
		
		$DrctDbtTxInf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}
}