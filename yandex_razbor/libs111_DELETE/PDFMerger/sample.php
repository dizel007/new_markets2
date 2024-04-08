<?php
require_once 'PDFMerger.php';
use PDF_Merger\PDFMerger;
$pdf = new PDFMerger;


for ($i =1; $i <=3; $i++ ) {
$pdf->addPDF("samplepdfs/".$i.".pdf", '1');
}
//  $pdf->addPDF('samplepdfs/two.pdf', '1');
//  $pdf->addPDF('samplepdfs/three.pdf', '1');
 $pdf->merge('file', __DIR__.'\test77788877777.pdf');

	// $pdf->addPDF('samplepdfs/one.pdf', '1, 3, 4')
	// ->addPDF('samplepdfs/two.pdf', '1-2')
	// ->addPDF('samplepdfs/three.pdf', 'all')
	// ->merge('file', 'samplepdfs/TEST2.pdf');

	//REPLACE 'file' WITH 'browser', 'download', 'string', or 'file' for output options
	//You do not need to give a file path for browser, string, or download - just the name.
	echo "rrrr";