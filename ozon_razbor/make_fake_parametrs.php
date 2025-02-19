<?php
require_once "vendor/autoload.php";


// подключаем шрифты
define('FPDF_FONTPATH',"vendor/setasign/fpdf/font/");


echo "eeeeeeeeeeeeeeeeeeef";
use setasign\Fpdi\Fpdi;


$pdf = new Fpdi();

// добавляем шрифт ариал
$pdf->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
$pdf->AddFont('TimesNRCyrMT-Bold','','timesnrcyrmt_bold.php'); 
// добавляем шрифт ариал
$pdf->AddFont('Arial','','arial.php'); 
// устанавливаем шрифт Ариал


// $pdf->AddFont('Arial','','Arial.php'); 

// устанавливаем шрифт Ариал


$pdf->setSourceFile('222_MERGE.pdf'); 

// Добавляем страницу из оригинального PDF 
$page_count = $pdf->setSourceFile('222_MERGE.pdf');

echo "page_count = $page_count<br>";


echo "<br>";

$arr =[58,80];
for ($i=1; $i<=$page_count; $i++) {
echo "i = $i <br>";

 make_pdf_fake_sizes($pdf, $i);
 
}

// Сохранение отредактированного PDF
    $pdf->Output('edited.pdf', 'F'); 
    echo "PDF успешно отредактирован!";
    // die('END PAGE');





function make_pdf_fake_sizes($pdf, $i) {
    $arr =[58,80];
    $pageId = $pdf->importPage($i);
    $pdf->AddPage('P', $arr); 
    $pdf->useTemplate($pageId, 0, 0, 58,40); 

// Красный цвет 
$pdf->SetFillColor(255,255,22);
// $pdf->Rect(13.5, 22.4, 5.7, 2.8, 'F'); 
// $pdf->Rect(21,   22.4, 4.5, 2.8, 'F'); 
// $pdf->Rect(27,   22.4, 4.5, 2.8, 'F'); 

if ($i%2 == 0)  {
// $pdf->Rect(10, 22.4, 40, 2.8, 'F'); 
// Устанавливаем шрифт 

// $pdf->SetFont('Arial','B' , 7);
// $pdf->SetTextColor(0, 100, 0); 
// $visota = '1000 x 554 x 160 мм | 900 г.';
// $visota = MakeUtf8Font($visota);
// $pdf->SetXY(10, 22.55); 
// $pdf->Cell(6, 3, $visota  ,0, 0, 'L');

$pdf->Image('images/72456080_ready.jpg', $x = 1, $y= 22.4,  $width = 55, $hight = 3, '' );


}



// // Добавляем текст Длина
// $dlina = 666;
// $pdf->SetXY(13.95, 22.55); 
// $pdf->Cell(6, 3, $dlina ,0, 0, 'R');   

// // Добавляем текст Ширина
// $shirina = 557;
// $pdf->SetXY(20.4, 22.55); 
// $pdf->Cell(6, 3, $shirina,0, 0, 'С');

// // Добавляем текст высота
// $visota = 160;
// $pdf->SetXY(26.4, 22.55); 
// $pdf->Cell(6, 3, $visota ,0, 0, 'С');




}

function MakeUtf8Font($string) {
    $string = iconv('utf-8', 'windows-1251', $string);
    return $string;
  }

// dump($pdf);


$pdf2 = new Fpdi();

$pdf2->setSourceFile('edited.pdf'); 


for ($i=1; $i<=$page_count; $i++) {
    $pageId = $pdf2->importPage($i); 
    $arr =[58,40];
    $pdf2->AddPage('L', $arr); 
    $pdf2->useTemplate($pageId, 0, 0, 58,80); 
           
    }


// Добавляем страницу из оригинального PDF 



// Сохранение отредактированного PDF
 $pdf2->Output('edited_333.pdf', 'F'); 
 echo "PDF успешно отредактирован!";


// dump($pdf);

