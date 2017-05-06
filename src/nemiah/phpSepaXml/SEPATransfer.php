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

class SEPATransfer extends SEPAFile {
	protected $messageID = "";
	protected $paymentID = "";
	protected $initiator = "";
	protected $creditoren = array();
	protected $debitor;
	#protected $sequenceType = 'OOFF'; //FNAL, FRST, OOFF, RCUR
	protected $creationDateTime;
	#protected $requestedCollectionDate;
	protected $type = "COR1";

	function __construct($data = null) {
		$this->creationDateTime = new \DateTime();
		if(!is_array($data))
			return;
		
		foreach($data AS $k => $v)
			if(property_exists($this, $k))
				$this->$k = $v;
	}

	public function addCreditor(SEPACreditor $creditor) {
		$reqestedExecutionDate = $creditor->reqestedExecutionDate;
		
		$date = $reqestedExecutionDate->format('Ymd');
		
		if(!isset($this->creditoren[$date]))
			$this->creditoren[$date] = array();
		
		$this->creditoren[] = $creditor;
	}

	public function setDebitor(SEPADebitor $debitor){#, DateTime $requestedCollectionDate, $sequenceType = "OOFF") {
		$this->debitor = $debitor;
	}

	/*private function CtrlSum($sequence = null) {
		$sum = 0;

		if($sequence == null){
			foreach($this->debitoren AS $debitoren)
				foreach ($debitoren AS $Debitor)
					$sum += $Debitor->amount;

			return $sum;
		}
		
		foreach ($this->debitoren[$sequence] AS $Debitor)
			$sum += $Debitor->amount;

		return $sum;
	}*/

	public function toXML() {
		/*$xml = $this->start("pain");

		if ($this->messageID == '')
			$this->messageID = time();

		$count = 0;
		foreach($this->debitoren AS $type)
			$count += count($type);
		
		$GrpHdr = $xml->addChild('CstmrDrctDbtInitn')->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageID);
		$GrpHdr->addChild('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s'));
		$GrpHdr->addChild('NbOfTxs', $count);
		$GrpHdr->addChild('CtrlSum', $this->CtrlSum());
		$GrpHdr->addChild('InitgPty');
		$GrpHdr->InitgPty->addChild('Nm', htmlentities($this->initiator));

		
		
		foreach($this->debitoren AS $sequence => $debitoren){
			$PmtInf = $xml->CstmrDrctDbtInitn->addChild('PmtInf');
			if ($this->paymentID != '')
				$PmtInf->addChild('PmtInfId', $this->paymentID);

			$PmtInf->addChild('PmtMtd', 'DD');

			$PmtInf->addChild('NbOfTxs', count($debitoren));
			$PmtInf->addChild('CtrlSum', $this->CtrlSum($sequence));

			$PmtTpInf = $PmtInf->addChild('PmtTpInf');
			$PmtTpInf->addChild('SvcLvl')->addChild('Cd', 'SEPA');
			$PmtTpInf->addChild('LclInstrm')->addChild('Cd', $this->type);
			$PmtTpInf->addChild('SeqTp', $debitoren[0]->sequenceType);

			$PmtInf->addChild('ReqdColltnDt', $debitoren[0]->requestedCollectionDate->format('Y-m-d'));
			
			$this->creditor->XML($PmtInf);


			foreach($debitoren AS $Debitor)
				$Debitor->XML($PmtInf);
		}

		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($xml->asXML());
		#if(!$dom->schemaValidate(dirname(__FILE__)."/ISO.pain.008.001.02.austrian.002.xsd"))
		#	throw new Exception("SEPA direct debit format error");
		
		$dom->formatOutput = TRUE;
		
		$xml = $dom->saveXml();
		
		$last = substr(trim($xml), -1);
		if($last != ">")
			$xml = trim($xml.">");
		
		return $xml;*/
	}
}