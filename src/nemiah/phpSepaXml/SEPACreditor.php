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

use nemiah\phpSepaXml\SEPAParty;

class SEPACreditor extends SEPAParty {
	public $name = "";
	public $iban = "";
	public $bic = "";
	public $identifier = "";

	function __construct($data = null) {
		$data["name"] = str_replace(array("&", "³"), array("und", "3"), $data["name"]);
		
		parent::__construct($data);
	}
	
	public function XML(\SimpleXMLElement $xml) {
		$xml->addChild('Cdtr')->addChild('Nm', htmlentities($this->name));
		$xml->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', str_replace(" ", "", $this->iban));
		$xml->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$xml->addChild('ChrgBr', 'SLEV');
		
		$CdtrSchmeId = $xml->addChild('CdtrSchmeId');
		#$CdtrSchmeId->addChild('Nm', $this->name);
		
		$Othr = $CdtrSchmeId->addChild('Id')->addChild('PrvtId')->addChild('Othr');
		$Othr->addChild('Id', $this->identifier);
		$Othr->addChild('SchmeNm')->addChild('Prtry', 'SEPA');
	}
}