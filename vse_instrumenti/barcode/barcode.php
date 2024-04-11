<?php
	require_once("barcode.inc.php");

$height="60";
$scale="4";
$bgcolor="#FFFFFF";
$color="#000000";
$type="png";
$encode="EAN-13";

	
	$bar= new BARCODE();
	
	if($bar==false)
		die($bar->error());
	
	$bar->setSymblogy($encode);
	$bar->setHeight($height);
	// $bar->setFont("arial");
	$bar->setScale($scale);
	$bar->setHexColor($color,$bgcolor);

	/*$bar->setSymblogy("UPC-E");
	$bar->setHeight(50);
	$bar->setFont("arial");
	$bar->setScale(2);
	$bar->setHexColor("#000000","#FFFFFF");*/

	//OR
	//$bar->setColor(255,255,255)   RGB Color
	//$bar->setBGColor(0,0,0)   RGB Color

  	
	$return = $bar->genBarCode($barnumber, $type, $file);
	if($return==false)
		$bar->error(true);
	
?>