<?php session_start();?>
<html>
  <head>
    <meta charset="UTF-8">  
    <title>Товарные цены и штрихкоды</title>
    <style type="text/css">
       TABLE {
        /*width: 300px;  Ширина таблицы */
        border-collapse: collapse; /* Убираем двойные линии между ячейками */
        border: 1px solid #eeeeee; /* Прячем рамку вокруг таблицы */
       }
       TD, TH {
        padding: 10px; /* Поля вокруг содержимого таблицы */
        border: 1px solid #eeeeee; /* Параметры рамки */
       }
       TH {
        background: #b0e0e6; /* Цвет фона */
       }
  </style>
   </head>
   <body>
<?php

function printtable($atbl) {
    print "<table width='100%' align='center' cellpadding='0' cellspacing='0'> "
    . "<col width='50%'/>"
    . "<col width='50%'/>";
    for ($i = 1; $i <= 6; $i++) {
        $z = $i % 2;
        $k = $i-1;
        if ($z > 0){
            echo "<tr height = '320px'>"
            . "<td align='center' valign='bottom'>$atbl[$k]</td>";
        } else {
            echo "<td align='center' valign='bottom'>$atbl[$k]</td>"
            . "</tr>";
        }
    }
    echo '</table>';
}

$print = filter_input(INPUT_GET, 'print');

include "../../classes/db.php";
$db = new db();

if (isset($print)){
    include("php-barcode.php");
    
    $list = unserialize($_SESSION['list']);
    $quant = $list[quant];
    $ids = $list[ids];
    
    if (isset($quant)){
        $quant = ($quant == '') ? 1 : $quant;
        $barcode = TRUE;
    } else {
       $quant = 1; 
    }
    
    echo "<div style='width: 100%; float: left;'>";
    

    $mode = "png"; 
    $table = 'ZzPrice';
    
    foreach ($ids as $column => $value) {
        $params .= ($params == "") ? "" : ", ";
        $params .= "'$value'";
    }
    $sql = "
    SELECT zi1.BarCode, zi1.Name, ISNULL(zp1.Value,0) AS Value FROM (
        SELECT * From (
            SELECT * FROM ZzItem z WHERE z.Id IN ($params)
        ) zi 
            LEFT JOIN (
                SELECT ItemId, MAX(Date) as PDate FROM  
                    ZzPrice WHERE ItemId IN ($params) GROUP BY  ItemId
            ) zp 
        ON (zi.Id = zp.ItemId)
    ) zi1 LEFT JOIN (
            SELECT ItemId, Date, Value From ZzPrice
          ) zp1 
    ON (zi1.Id = zp1.ItemId AND zi1.PDate = zp1.Date)
    ";
    $q = $db->query($sql);
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    $prcs = $q->all();
    $atbl = array();
    $k = 1;
    foreach ($prcs as $key => $prc) { 
        $code = $prc[BarCode];
        $bars = barcode_encode($code,"ANY");
        $image = "img/".preg_replace('/[\\/:*?\'<>|]/', '', $code).".".$mode;
        barcode_outimage($bars['text'],$bars['bars'], 1, $mode, 0, '', $image);
        if (isset($barcode)){
            for ($i = 1; $i <= $quant; $i++) {
                $out = '<img src="'.$image.'" align="top" />'; 
                print "<div style='float: left; width: 20%; padding-bottom: 2px;'>".$out."</div>";
            } 
        } else {
//            $out = $prc[Name].'<br><img src="'.$image.'" align="top" />'; 
//            print "<div style='float: left; width: 70%; vertical-align: top; font-size: 1.5em;'>".$prc[Name]."</div>";
//            print "<div style='float: left; width: 15%; padding-bottom: 5px;'>".$out."</div>";
//            print "<div style='float: left; width: 15%; padding-top: 2%; font-size: 1.3em; text-align: right;'>".$prc[Value]."</div><br>";
//            echo "<div style='clear: both;'></div>"; OR count($atbl) == count($prcs) 
            $atbl[] = $prc[Name].'<br><img src="'.$image.'" align="top" />'; 
            if (count($atbl) == 6 OR count($prcs) == ($key + 1)){
                printtable($atbl);
                $atbl = array();
            }
            $k += 1;
//            $k += ($k == 6)? 0 : 1;
        }
        ?>
<?php
    }
    if (isset($barcode)){
        echo "</div>";
        echo "<div style='clear: both;'></div>";
    }
}  else {
    $list = array();
    $list['ids'] = $db->getarray('id');
    $list['quant'] = filter_input(INPUT_POST, 'quant');
    $_SESSION['list'] = serialize($list);
}
?>
    </body>
</html>