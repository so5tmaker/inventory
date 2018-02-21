<html>
  <head>
    <meta charset="UTF-8">  
    <title>Создать штрихкоды</title>
   </head>
   <body>
<?php
$crt = filter_input(INPUT_POST, 'create');

include "../../classes/db.php";

try
{
    $db = new db();

    if (isset($crt)){
        include("php-barcode.php");
        echo "<div style='width: 100%; float: left;'>";
        $mode = "png"; 
        $qnt = 0 + $crt;
//        $qnt = 5;
        for ($index = 0; $index <  $qnt; $index++) {
            // добавляем новый товар
            $q = $db->add_new_item();
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            $rows = $q->single();
            $code = $rows[BarCode];
            $bars = barcode_encode($code,"ANY");
            $image = "img/".preg_replace('/[\\/:*?\'<>|]/', '', $code).".".$mode;
            barcode_outimage($bars['text'],$bars['bars'], 1, $mode, 0, '', $image);
            $out = '<img src="'.$image.'" align="top" />'; 
            print "<div style='float: left; width: 20%; padding-bottom: 2px;'>".$out."</div>";
        }
        echo "</div>";
        echo "<div style='clear: both;'></div>";

        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Message'] = "Изображения штрихкодов успешно созданы!";
        $jTableResult['Options'] = $rows;
        print json_encode($jTableResult);
    }
}
catch(Exception $ex)
{
    //Return error message
    $jTableResult = array();
    $jTableResult['Result'] = "ERROR";
    $jTableResult['Message'] = $ex->getMessage();
    print json_encode($jTableResult);
}
?>
    </body>
</html>