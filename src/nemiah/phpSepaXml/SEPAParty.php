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

class SEPAParty {
	protected $propList = array();

    public $addressLine1 = "";
    public $addressLine2 = "";
    public $street = "";
    public $buildingNumber = "";
    public $postalCode = "";
    public $city = "";
    public $country = "";

    // Erweiterte Felder für strukturierte Adresse
    public $department = "";
    public $subDepartment = "";
    public $buildingName = "";
    public $floor = "";
    public $postBox = "";
    public $room = "";
    public $townLocationName = "";
    public $disctrictName = "";
    public $countrySubDivision = "";

	function __construct($data = null) {
		if(!is_array($data))
			return;
		
		foreach($data AS $k => $v)
			if(property_exists($this, $k))
				$this->$k = $v;
		
	}
	
	function fixNm($name, $length=70){
		return mb_substr(str_replace(array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß", "&", "³", "-", "|", "é", "[", "]"), array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss", "und", "3", " ", "", "e", "", ""), $name), 0, $length);
	}
	
	public static function fixNmS($name){
		$P = new SEPAParty();
		return $P->fixNm($name);
	}

    // In SEPAParty.php
    public function addPostalAddress(\SimpleXMLElement $parent, $format = null) {
        // Für pain.008.001.08 und pain.001.001.09 strukturierte Adresse bevorzugen
        $useStructured = ($format === 'pain.008.001.08');

        // Prüfe, ob genug Felder für strukturierte Adresse vorhanden sind
        if ($useStructured && $this->street && $this->buildingNumber && $this->postalCode && $this->city && $this->country) {
            $PstlAdr = $parent->addChild("PstlAdr");
            if ($this->department) $PstlAdr->addChild("Dept", $this->fixNm($this->department));
            if ($this->subDepartment) $PstlAdr->addChild("SubDept", $this->fixNm($this->subDepartment));
            $PstlAdr->addChild("StrtNm", $this->fixNm($this->street));
            $PstlAdr->addChild("BldgNb", $this->buildingNumber);
            if ($this->buildingName) $PstlAdr->addChild("BldgNm", $this->fixNm($this->buildingName));
            if ($this->floor) $PstlAdr->addChild("Flr", $this->fixNm($this->floor));
            if ($this->postBox) $PstlAdr->addChild("PstBx", $this->fixNm($this->postBox));
            if ($this->room) $PstlAdr->addChild("Room", $this->fixNm($this->room));
            $PstlAdr->addChild("PstCd", $this->postalCode);
            $PstlAdr->addChild("TwnNm", $this->fixNm($this->city));
            if ($this->townLocationName) $PstlAdr->addChild("TwnLctnNm", $this->fixNm($this->townLocationName));
            if ($this->disctrictName) $PstlAdr->addChild("DstrctNm", $this->fixNm($this->disctrictName));
            if ($this->countrySubDivision) $PstlAdr->addChild("CtrySubDvsn", $this->fixNm($this->countrySubDivision));
            $PstlAdr->addChild("Ctry", $this->country);
        } elseif (trim($this->addressLine1 . $this->addressLine2) != "") {
            // Fallback: Unstrukturierte Adresse
            $PstlAdr = $parent->addChild("PstlAdr");
            if ($this->addressLine1) $PstlAdr->addChild("AdrLine", $this->fixNm($this->addressLine1));
            if ($this->addressLine2) $PstlAdr->addChild("AdrLine", $this->fixNm($this->addressLine2));
        }
    }

    public function validateRequiredFields($context = null) {
        $errors = [];

        // Allgemeine Felder
        if (empty($this->name)) $errors[] = "Name fehlt";
        if (empty($this->iban)) $errors[] = "IBAN fehlt";

        // BIC ist je nach Format Pflicht oder optional
        // $context kann z.B. 'pain.008.001.02', 'pain.008.001.08', 'pain.001.003.03' sein
        if ($context === 'pain.008.001.02' || $context === 'pain.001.003.03') {
            if (empty($this->bic)) $errors[] = "BIC fehlt";
        }

        // IBAN-Format grob prüfen
        if (!empty($this->iban) && !preg_match('/^[A-Z]{2}[0-9A-Z]{13,32}$/', $this->iban)) {
            $errors[] = "IBAN-Format ungültig";
        }

        // BIC-Format grob prüfen (wenn vorhanden)
        if (!empty($this->bic) && !preg_match('/^[A-Z0-9]{8,11}$/', $this->bic)) {
            $errors[] = "BIC-Format ungültig";
        }

        if (!empty($errors)) {
            throw new \Exception("Fehlende oder ungültige Pflichtfelder: " . implode(", ", $errors));
        }
    }


}
