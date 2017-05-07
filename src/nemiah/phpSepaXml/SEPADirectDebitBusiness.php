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

class SEPADirectDebitBusiness extends SEPADirectDebitBasic {
	function __construct($data = null) {
		parent::__construct($data);
		
		$this->type = "B2B";
	}
}