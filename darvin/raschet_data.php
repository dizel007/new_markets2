<?php

function razbor_all_tranzactions_yandex(array $uploadedData)
{
  $arr_article_data = [];
  foreach ($uploadedData as $array_with_data) {
    $start = 0;
    $i = 0;
    foreach ($array_with_data['result']['data'] as $string_data) {
      $i++;

      // пропускаем шапку талицы
      if ($string_data[3] == "Артикул") {
        $start = 1;
        $start_string = $i;
        continue;
      }
      if ($start == 1) {
        $article  = trim($string_data[3]);
        $strihcode  = $string_data[5];
        $count = $string_data[30];


        if (!is_numeric($count)) {
          $start = 0;
          break;
        }
        $arr_article_data[$article]['strihcode'] = $strihcode;
        $arr_article_data[$article]['count'] = $count;
      }
    }
  }

  return $arr_article_data;
}
