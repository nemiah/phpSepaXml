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
    protected $paymentInitiation = 'pain.001.003.03';

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
		
		$date = $reqestedExecutionDate->format('Y-m-d');
		
		if(!isset($this->creditoren[$date]))
			$this->creditoren[$date] = array();
		
		$this->creditoren[$date][] = $creditor;
	}

	public function setDebitor(SEPADebitor $debitor, $update = true){
		$this->debitor = $debitor;
		
		if ($update == true)
			$this->initiator = $this->debitor->name;
	}

	private function CtrlSum($sequence = null) {
		$sum = 0;
		
		if($sequence == null){
			foreach($this->creditoren AS $creditoren)
				foreach ($creditoren AS $Creditor)
					$sum += $Creditor->amount;

			return $sum;
		}
		
		foreach ($this->creditoren[$sequence] AS $Creditor)
			$sum += $Creditor->amount;

		return $sum;
	}

	public function toXML() {
		#print_r($this->creditoren);
		
		$xml = $this->start($this->paymentInitiation);

		if ($this->messageID == '')
			$this->messageID = time();

		$count = 0;
		foreach($this->creditoren AS $type)
			$count += count($type);
		
		$GrpHdr = $xml->addChild('CstmrCdtTrfInitn')->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageID);
		$GrpHdr->addChild('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s'));
		$GrpHdr->addChild('NbOfTxs', $count);
		$GrpHdr->addChild('CtrlSum', $this->CtrlSum());
		$GrpHdr->addChild('InitgPty');
		$GrpHdr->InitgPty->addChild('Nm', htmlentities($this->initiator));

		
		
		foreach($this->creditoren AS $sequence => $creditoren){
			$PmtInf = $xml->CstmrCdtTrfInitn->addChild('PmtInf');
			if ($this->paymentID != '')
				$PmtInf->addChild('PmtInfId', $this->paymentID);

			$PmtInf->addChild('PmtMtd', 'TRF');

			$PmtInf->addChild('NbOfTxs', count($creditoren));
			$PmtInf->addChild('CtrlSum', $this->CtrlSum($sequence));

			$PmtTpInf = $PmtInf->addChild('PmtTpInf');
			$PmtTpInf->addChild('SvcLvl')->addChild('Cd', 'SEPA');
			
			$PmtTpInf = $PmtInf->addChild('ReqdExctnDt', $sequence); //OK
			
			$this->debitor->XMLTransfer($PmtInf);


			foreach($creditoren AS $Creditor)
				$Creditor->XMLTransfer($PmtInf);
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
