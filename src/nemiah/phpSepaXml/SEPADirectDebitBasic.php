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

class SEPADirectDebitBasic extends SEPAFile {
	protected $messageID = "";
	protected $paymentID = "";
	protected $initiator = "";
	protected $creditor;
	protected $btchBookg = null;
	protected $debitoren = array();
	#protected $sequenceType = 'OOFF'; //FNAL, FRST, OOFF, RCUR
	protected $creationDateTime;
	#protected $requestedCollectionDate;
	#protected $type = "COR1";

	function __construct($data = null) {
		$this->creationDateTime = new \DateTime();
		#$this->requestedCollectionDate = new DateTime();
		#$this->requestedCollectionDate->add(new DateInterval("P5D"));

		if(!is_array($data))
			return;
		
		foreach($data AS $k => $v)
			if(property_exists($this, $k))
				$this->$k = $v;
	}

	public function setCreditor(SEPACreditor $creditor, $update = true) {
		$this->creditor = $creditor;
		
		if ($update == true)
			$this->initiator = $this->creditor->name;
	}

	public function addDebitor(SEPADebitor $debitor){#, DateTime $requestedCollectionDate, $sequenceType = "OOFF") {
		$requestedCollectionDate = $debitor->requestedCollectionDate;
		#$sequenceType = $debitor->sequenceType;
		
		$date = $requestedCollectionDate->format('Ymd');
		$group =  $date.$debitor->group.$debitor->type;
		
		if(!isset($this->debitoren[$group]))
			$this->debitoren[$group] = array();
		
		$this->debitoren[$group][] = $debitor;
	}

	private function CtrlSum($sequence = null) {
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
	}

	public function toXML($format = "pain.008.003.02") {
		$xml = $this->start($format);

		if ($this->messageID == '')
			$this->messageID = time();

		$count = 0;
		foreach($this->debitoren AS $type)
			$count += count($type);
		
		$GrpHdr = $xml->addChild('CstmrDrctDbtInitn')->addChild('GrpHdr');
		if($this->btchBookg !== null)
			$GrpHdr->addChild('BtchBookg', $this->btchBookg);
		$GrpHdr->addChild('MsgId', $this->messageID);
		$GrpHdr->addChild('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s'));
		$GrpHdr->addChild('NbOfTxs', $count);
		$GrpHdr->addChild('CtrlSum', $this->CtrlSum());
		$GrpHdr->addChild('InitgPty');
		$GrpHdr->InitgPty->addChild('Nm', SEPAParty::fixNmS($this->initiator));

		
		
		foreach($this->debitoren AS $sequence => $debitoren){
			$PmtInf = $xml->CstmrDrctDbtInitn->addChild('PmtInf');
			if ($this->paymentID != '')
				$PmtInf->addChild('PmtInfId', $this->paymentID);

			$PmtInf->addChild('PmtMtd', 'DD');

			$PmtInf->addChild('NbOfTxs', count($debitoren));
			$PmtInf->addChild('CtrlSum', $this->CtrlSum($sequence));

			$PmtTpInf = $PmtInf->addChild('PmtTpInf');
			$PmtTpInf->addChild('SvcLvl')->addChild('Cd', 'SEPA');
			$PmtTpInf->addChild('LclInstrm')->addChild('Cd', $debitoren[0]->type);
			$PmtTpInf->addChild('SeqTp', $debitoren[0]->sequenceType);

			$PmtInf->addChild('ReqdColltnDt', $debitoren[0]->requestedCollectionDate->format('Y-m-d'));
			
			$this->creditor->XMLDirectDebit($PmtInf);


			foreach($debitoren AS $Debitor)
				$Debitor->XMLDirectDebit($PmtInf);
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
		
		return $xml;
	}
}