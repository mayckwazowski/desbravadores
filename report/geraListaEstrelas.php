<?php
@require_once('../include/functions.php');
@require_once('../include/_core/lib/tcpdf/tcpdf.php');

class LISTAESTRELAS extends TCPDF {
	
	//lines styles
	private $stLine;
	private $stLine2;
	public $lineAlt;
	public $posY;
	
	function __construct() {
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		$this->stLine = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->stLine2 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->stLine3 = array(
		    'position' => '',
		    'align' => 'C',
		    'stretch' => false,
		    'fitwidth' => true,
		    'cellfitalign' => '',
		    'border' => false,
		    'hpadding' => 'auto',
		    'vpadding' => 'auto',
		    'fgcolor' => array(0,0,0),
		    'bgcolor' => false, //array(255,255,255),
		    'text' => true,
		    'font' => 'helvetica',
		    'fontsize' => 9,
		    'stretchtext' => 0
		);

		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor('Ricardo J. Cesar');
		$this->SetTitle('Listagem de Membros Ativos');
		$this->SetSubject('Clube Pioneiros');
		$this->SetKeywords('Desbravadores, Especialidades, Pioneiros, Capão Redondo');
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
	}

	public function Footer() {
		$this->SetTextColor(90,90,90);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'N', 6);
		$this->SetY(-9);
		$this->Cell(40, 3, "Página ". $this->getAliasNumPage() ." de ". $this->getAliasNbPages(), 0, false, 'L');
		
		$this->Image("img/logod1.jpg", 160, 264, 22, 22, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		$this->Image("img/logo.jpg", 183, 263, 20, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}
	
 	public function Header() {
 		$this->setXY(0,0);
 		$this->Image("img/iasd-full.jpg", 10, 7, 32, 30, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
 		
 		$this->Line(161, 7, 161, 42, $this->stLine2);

 		$this->setXY(163,7);
 		$this->SetFont(PDF_FONT_NAME_MAIN, 'N', 9);
 		$this->Cell(44, 5, "Distrito de Capão Redondo", 0, false, 'L', false, false, false, false, 'T', 'M');
 		$this->setXY(163,12);
 		$this->Cell(44, 5, "Av. Ellis Maas, 520", 0, false, 'L', false, false, false, false, 'T', 'M');
 		$this->setXY(163,17);
 		$this->Cell(44, 5, "Capão Redondo", 0, false, 'L', false, false, false, false, 'T', 'M');
 		$this->setXY(163,22);
 		$this->Cell(44, 5, "São Paulo - SP", 0, false, 'L', false, false, false, false, 'T', 'M');
 		$this->setXY(163,27);
 		$this->Cell(44, 5, "CEP 05859-000", 0, false, 'L', false, false, false, false, 'T', 'M');
 		$this->setXY(163,32);
 		$this->Cell(44, 5, "CNPJ 43.586.122/0121-20", 0, false, 'L', false, false, false, false, 'T', 'M');
 		$this->setXY(163,37);
 		$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 10);
 		$this->SetTextColor(0,128,128);
 		$this->Cell(44, 5, "Associação Paulista Sul", 0, false, 'L', false, false, false, false, 'T', 'M');
 		$this->SetTextColor(0,0,0);
 		$this->posY = 43;
 	}

	public function newPage() {
		$this->AddPage();
		$this->setCellPaddings(0,0,0,0);
		$this->SetTextColor(0,0,0);
		$this->setXY(0,0);
	}

	public function download() {
		$this->lastPage();
		$this->Output("ListagemEstrela_".date('Y-m-d_H:i:s').".pdf", "I");
	}
}

$pdf = new LISTAESTRELAS();

fConnDB();
$pdf->newPage();

$result = $GLOBALS['conn']->Execute("SELECT NM_PASTOR, NOW() AS DH FROM CON_PASTOR");
$nmPastor = titleCase(mb_strtolower($result->fields["NM_PASTOR"]));

$pdf->posY += 5;
$pdf->setXY(20,$pdf->posY);
$pdf->SetFont(PDF_FONT_NAME_MAIN, 'N', 13);
$pdf->Cell(100, 5, "São Paulo, ".utf8_encode(strftime("%d de %B de %Y",strtotime($result->fields["DH"]))), 0, false, 'L', false, false, false, false, 'T', 'M');

$pdf->posY += 12;
$pdf->SetY(++$pdf->posY);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont(PDF_FONT_NAME_MAIN, '', 13);

$html = "<p align=\"justify\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Eu, ".$nmPastor.", pastor do Distrito de Capão Redondo,
			venho através desta, recomendar a <b>Estrela de Tempo de Serviço</b> aos 
			membros da direção do Clube Pioneiros listados abaixo, em reconhecimento 
			de sua dedicação no trabalho de liderar o clube para Salvação e Serviço.		
		</p>";
$pdf->setCellHeightRatio(2);
$pdf->writeHTMLCell(0,0,20,$pdf->posY,$html,0,0,false,true,"",false);

$result = $GLOBALS['conn']->Execute("
	SELECT CD, NM, COUNT(*) AS QTD
	  FROM CON_COMPRAS
	 WHERE CD LIKE '03-01-%'
	   AND FG_COMPRA = 'N'
	 GROUP BY CD, NM
	 ORDER BY CD, NM
");
if (!$result->EOF):
	$pdf->SetFont(PDF_FONT_NAME_MAIN, '', 10);
	$pdf->posY += $pdf->getLastH()+4;
	$pdf->setCellHeightRatio(0);
	$pdf->SetTextColor(255,255,255);
	$pdf->SetFillColor(252,70,70);
	$pdf->setCellPaddings(1,0,1,0);
	$pdf->setXY(20, $pdf->posY);
	$pdf->Cell(120, 7, "Nome Completo", 0, false, 'L', true);
	$pdf->setXY(140, $pdf->posY);
	$pdf->Cell(30, 7, "Tempo", 0, false, 'C', true);
	$pdf->setXY(170, $pdf->posY);
	$pdf->Cell(30, 7, "Quantidade", 0, false, 'C', true);
	$pdf->posY += 7;
	$pdf->SetTextColor(0,0,0);
	
	foreach($result as $k => $f):
		if ($pdf->lineAlt):
			$pdf->SetFillColor(245,245,245);
		else:
			$pdf->SetFillColor(255,255,255);
		endif;
		$pdf->setXY(20, $pdf->posY);
		$pdf->Cell(120, 5, utf8_encode($f["NM"]), 0, false, 'L', true);
		$pdf->setXY(140, $pdf->posY);
		$pdf->Cell(30, 5, (substr($f["CD"],-2) * 1), 0, false, 'C', true);
		$pdf->setXY(170, $pdf->posY);
		$pdf->Cell(30, 5, $f["QTD"], 0, false, 'C', true);
		$pdf->posY += 5;
		$pdf->lineAlt = !$pdf->lineAlt;
	endforeach;
	
	$pdf->setCellPaddings(0,0,0,0);
	$pdf->posY += 10;
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont(PDF_FONT_NAME_MAIN, '', 13);
	$pdf->setXY(20, $pdf->posY);
	$pdf->Cell(120, 6, "Atenciosamente,", 0, false, 'L', true);
	$pdf->posY += 30;
	$pdf->setXY(20, $pdf->posY);
	$pdf->Cell(120, 6, $nmPastor, 0, false, 'L', true);
	$pdf->posY += 6;
	$pdf->setXY(20, $pdf->posY);
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'N', 10);
	$pdf->SetTextColor(120,120,120);
	$pdf->Cell(120, 3, "Pastor Distrital", 0, false, 'L', true);
endif;

$pdf->download();
exit;
?>