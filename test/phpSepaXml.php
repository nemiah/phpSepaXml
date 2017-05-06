<?php

require_once "directDebitTest.php";

class phpSepaXml extends PHPUnit_Framework_TestSuite {
    public static function suite() {

		/*spl_autoload_register(function ($class) {

			$ex = explode("\\", $class);
			var_dump($ex);

			$file = __DIR__."/../src/php-sepa-xml/".$ex[2].'.php';

			#if (file_exists($file)) 
				require $file;

		});*/
		
		$suite = new PHPUnit_Framework_TestSuite('directDebitTest');

		return $suite;
    }
}
