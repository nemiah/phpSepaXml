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
	
	function __construct($data = null) {
		if(!is_array($data))
			return;
		
		foreach($data AS $k => $v)
			if(property_exists($this, $k))
				$this->$k = $v;
		
	}
	
	function fixNm($name){
		return mb_substr(str_replace(array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß", "&", "³", "-", "|", "é"), array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss", "und", "3", " ", "", "e"), $name), 0, 70);
	}
	
	public static function fixNmS($name){
		$P = new SEPAParty();
		return $P->fixNm($name);
	}
}
