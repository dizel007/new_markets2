<?php

	//########################//
	// 
	// Author :Harish Chauhan
	// Created : 7July 2005
	// 
	//########################//

	/*
	* This class is for generating barcodes in diffrenct encoding symbologies.
	* It supports EAN-13,EAN-8,UPC-A,UPC-E,ISBN ,2 of 5 Symbologies(std,ind,interleaved),postnet,
	* codabar,code128,code39,code93 symbologies.
	* 
	* This program is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	* 
	* Requirements : PHP with GD library support. 
	* 
	* Reference : http://www.barcodeisland.com/symbolgy.phtml
	*/
	
	class BARCODE
	{
		var $_encode;
		var $_error;
		var $_width;
		var $_height;
		var $_scale;
		var $_color;
		var $_font;
		var $_bgcolor;
		var $_format;
		var $_n2w;
		
		function BARCODE($encoding="EAN-13")
		{
			
			if(!function_exists("imagecreate"))
			{
				die("This class needs GD library support.");
				return false;
			}

			$this->_error="";
			$this->_scale=2;
			$this->_width=0;
			$this->_height=0;
			$this->_n2w=2;
			$this->_height=60;
			$this->_format='png';
			
		
		    // $this->_font=dirname($_SERVER["PATH_TRANSLATED"])."/"."arialbd.ttf";

			
			// $path = realpath('fonts/')."/";
			// $path= "https://anmarkets.ru/vse_instrumenti/fonts/";
			// $path= "https://anmarkets.ru/vse_instrumenti/fonts/";
			// Установка переменной окружения для GD
			$this->_font='arialbd.ttf';


			$this->setSymblogy($encoding);
			$this->setHexColor("#000000","#FFFFFF");
		}

		function setSymblogy($encoding="EAN-13")
		{
			$this->_encode=strtoupper($encoding);
		}
		
		function setHexColor($color,$bgcolor)
		{
			$this->setColor(hexdec(substr($color,1,2)),hexdec(substr($color,3,2)),hexdec(substr($color,5,2)));
		$this->setBGColor(hexdec(substr($bgcolor,1,2)),hexdec(substr($bgcolor,3,2)),hexdec(substr($bgcolor,5,2)));
		}

		function setColor($red,$green,$blue)
		{
			$this->_color=array($red,$green,$blue);
		}

		function setBGColor($red,$green,$blue)
		{
			$this->_bgcolor=array($red,$green,$blue);
		}
		
		function setScale($scale)
		{
			$this->_scale=$scale;
		}
		
		function setFormat($format)
		{
			$this->_format=strtolower($format);
		}

		function setHeight($height)
		{
			$this->_height=$height;
		}

		function setNarrow2Wide($n2w)
		{
			if($n2w<2)
				$n2w=3;
			$this->_n2w=$n2w;
		}
		
		function error($asimg=false)
		{
			if(empty($this->_error))
				return "";
			if(!$asimg)
				return $this->_error;
			

			@header("Content-type: image/png");
			$im=@imagecreate(250,100);
			$color = @imagecolorallocate($im,255,255,255);
			$color = @imagecolorallocate($im,0,0,0);
			// @imagettftext($im,10,0,5,50,$color,$this->_font , wordwrap($this->_error, 40, "\n"));
			@imagepng($im);
			@imagedestroy($im);
		}

		function genBarCode($barnumber,$format="gif",$file="")
		{
			$this->setFormat($format);
			if($this->_encode=="EAN-13")
			{
				if(strlen($barnumber)>13)
				{
					$this->_error="Barcode number must be less then 13 characters.";
					return false;
				}
				$this->_eanBarcode($barnumber,$this->_scale,$file);
			}
			elseif($this->_encode=="UPC-A")
			{
				if(strlen($barnumber)>12)
				{
					$this->_error="Barcode number must be less then 13 characters.";
					return false;
				}
				$this->_eanBarcode($barnumber,$this->_scale,$file);
			}
			elseif($this->_encode=="ISBN")
			{
				if(strlen($barnumber)>13 || strlen($barnumber)<12)
				{
					$this->_error="Barcode number must be less then 13 characters.";
					return false;
				}
				elseif(substr($barnumber,0,3)!="978")
				{
					$this->_error="Not an ISBN barcode number. Must be start with 978";
					return false;
				}
				$this->_eanBarcode($barnumber,$this->_scale,$file);
			
			
		}
		}	
	
			
		///Start Functions from EAN-13 Encoding

		function _ean13CheckDigit($barnumber)
		{
			 $csumTotal = 0; // The checksum working variable starts at zero

			 // If the source message string is less than 12 characters long, we make it 12 characters
			 if(strlen($barnumber) <= 12 )
			  {
				$barnumber = str_pad($barnumber, 13, "0", STR_PAD_LEFT);  
			  }
			  
			  /*if(strlen($barnumber) == 13)
				$barnumber = substr($barnumber,0,12);*/

			 // Calculate the checksum value for the message
			
			 for($i=0;$i<strlen($barnumber);$i++) 
			  {
				  if($i % 2 == 0 )
					   $csumTotal = $csumTotal + intval($barnumber{$i});
				  else
					   $csumTotal = $csumTotal + (3 * intval($barnumber{$i}));
			  }

			 // Calculate the checksum digit

			 if( $csumTotal % 10 == 0 )
				$checksumDigit = '';
			 else
				$checksumDigit = 10 - ($csumTotal % 10);
			 return $barnumber.$checksumDigit;
		}

		/*An EAN-13 barcode has the following physical structure:

		Left-hand guard bars, or start sentinel, encoded as 101. 
		The second character of the number system code, encoded as described below. 
		The five characters of the manufacturer code, encoded as described below. 
		Center guard pattern, encoded as 01010. 
		The five characters of the product code, encoded as right-hand characters, described below. 
		Check digit, encoded as a right-hand character, described below. 
		Right-hand guard bars, or end sentinel, encoded as 101. 
		FIRST NUMBER

		SYSTEM DIGIT PARITY TO ENCODE WITH 
			SECOND NUMBER
			SYSTEM DIGIT MANUFACTURER CODE CHARACTERS 
						1	2	3	 4	5 
		0 (UPC-A)	Odd	Odd	Odd	Odd	Odd	Odd 
		1			Odd Odd Even Odd Even Even 
		2			Odd Odd Even Even Odd Even 
		3			Odd Odd Even Even Even Odd 
		4			Odd Even Odd Odd Even Even 
		5			Odd Even Even Odd Odd Even 
		6			Odd Even Even Even Odd Odd 
		7			Odd Even Odd Even Odd Even 
		8			Odd Even Odd Even Even Odd 
		9			Odd Even Even Odd Even Odd 


		*/

		function _eanEncode($barnumber)
		{
			$leftOdd=array("0001101","0011001","0010011","0111101","0100011","0110001","0101111","0111011","0110111","0001011");
			$leftEven=array("0100111","0110011","0011011","0100001","0011101","0111001","0000101","0010001","0001001","0010111");
			$rightAll=array("1110010","1100110","1101100","1000010","1011100","1001110","1010000","1000100","1001000","1110100");

			$encTable=array("000000","001011","001101","001110","010011","011001","011100","010101","010110","011010");
			
		    $guards=array("bab","ababa","bab");

			$mfcStr="";
			$prodStr="";
			
			$encbit=$barnumber[0];

			for($i=1;$i<strlen($barnumber);$i++)
			{
				$num=(int)$barnumber{$i};
				if($i<7) 
				{
					$even=(substr($encTable[$encbit],$i-1,1)==1);
					if(!$even)
						$mfcStr.=$leftOdd[$num];
					else
						$mfcStr.=$leftEven[$num];
				}
				elseif($i>=7)
				{
					$prodStr.=$rightAll[$num];
				}

			}

			return $guards[0].$mfcStr.$guards[1].$prodStr.$guards[2];
		}
		
		function _eanBarcode($barnumber,$scale=1,$file="")
		{
			$barnumber=$this->_ean13CheckDigit($barnumber);

			$bars=$this->_eanEncode($barnumber);
			if(empty($file))
				header("Content-type: image/".$this->_format);

			if ($scale<1) $scale=2;
			$total_y=(double)$scale * $this->_height;
			// if (!@$space)
			  $space=array('top'=>2*$scale,'bottom'=>2*$scale,'left'=>2*$scale,'right'=>2*$scale);
			
			/* count total width */
			$xpos=0;
			
			$xpos=$scale*(114); 

			/* allocate the image */
			$total_x= $xpos +$space['left']+$space['right'];
			$xpos=$space['left']+($scale*6);
	
		    $height=floor($total_y-($scale*10));
		    $height2=floor($total_y-$space['bottom']);
		
			$im=@imagecreatetruecolor($total_x, $total_y);
			$bg_color = @imagecolorallocate($im, $this->_bgcolor[0], $this->_bgcolor[1],$this->_bgcolor[2]);
			@imagefilledrectangle($im,0,0,$total_x,$total_y,$bg_color); 
			$bar_color = @imagecolorallocate($im, $this->_color[0], $this->_color[1],$this->_color[2]);
	
			for($i=0;$i<strlen($bars);$i++)
			{
				$h=$height;
				$val=strtoupper($bars[$i]);
				if(preg_match("/[a-z]/i",$val))
				{
					$val=ord($val)-65;
					$h=$height2;
				}
				if($this->_encode=="UPC-A" && ($i<10 || $i>strlen($bars)-13))
					$h=$height2;

				if($val==1)
					@imagefilledrectangle($im,$xpos, $space['top'],$xpos+$scale-1, $h,$bar_color);
				$xpos+=$scale;
			}
			
		
			// if($this->_encode=="UPC-A")
			// 	$str=substr($barnumber,1,1);
			// else
			// 	$str=substr($barnumber,0,1);

			// @imagettftext($im,$scale*6,0, $space['left'], $height, $bar_color,$this->_font , $str);

			// if($this->_encode=="UPC-A")
			// 	$str=substr($barnumber,2,5);
			// else
			// 	$str=substr($barnumber,1,6);
			
			// $x= $space['left']+$scale*strlen($barnumber)+$scale*6;	
			// @imagettftext($im,$scale*6,0,$x, $height2, $bar_color,$this->_font , $str);
			
			// if($this->_encode=="UPC-A")
			// 	$str=substr($barnumber,7,5);
			// else
			// 	$str=substr($barnumber,7,6);
			// $x=$space['left']+$scale*strlen($bars)/1.65+$scale*6;
			// @imagettftext($im,$scale*6,0, $x, $height2, $bar_color,$this->_font ,$str);

			// if($this->_encode=="UPC-A")
			// {
			// 	$str=substr($barnumber,12,1);
			// 	$x=$total_x-$space['left']-$scale*6;
			// 	@imagettftext($im,$scale*6,0, $x, $height, $bar_color,$this->_font , $str);
			// }
			
			if($this->_format=="png")
			{
				if(!empty($file))
					@imagepng($im,$file.".".$this->_format);
				else
					@imagepng($im);
			}





			@imagedestroy($im);
		}
	}
	