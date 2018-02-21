<?php 
include "../classes/db.php";

$uploaddir = 'uploads/'; 

// Загрузка файла
$excel_file = $_FILES['uploadfile'];
if( $excel_file )
        $excel_file = $_FILES['uploadfile']['tmp_name'];

if( $excel_file == '' ) fatal("Нет файла для загрузки");

move_uploaded_file( $excel_file, $uploaddir . $_FILES['uploadfile']['name']);	
$excel_file = $uploaddir . $_FILES['uploadfile']['name'];

try
{
//  Open database connection
    $db = new db();
    $error = "1";
    $row = 1;
    $csv = array_map('str_getcsv', file($excel_file));
    $noloaditem = "";
    $updateitem = "";
    $notenoughdata = "";
    $crtbarcode = "";
    $kol_strok = 0;
    $kol_noload = 0;
    $kol_update = 0;
    $kol_barcode= 0;
    $kol = 0;
    $num = count($csv);
    for ($c=1; $c < $num; $c++) {
        $str = iconv('cp1251','UTF-8',$csv[$c][0]);

        $str_arr = explode(";", $str); 
         
        $name    = trim(str_replace('"', '', $str_arr[0]));
        $barcode = $db->clearstr($str_arr[1]);
        $price   = trim(str_replace(',', '.', $str_arr[2]));
        $kol +=1;
        $clmns = "Наименование &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; Штрихкод &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; Цена";
        // если есть, то не загружаем в БД
        if (empty($name)) { // empty($price) or 
            $value = "$name &nbsp;&nbsp;&nbsp;&nbsp; | $barcode &nbsp;&nbsp;&nbsp;&nbsp; | $price";
            $notenoughdata .= ($notenoughdata == "") ? "Следующие товары не загружены, так как о них не хватает данных: <br> $clmns <br> " : ". <br>";
            $notenoughdata .= $value;
            $kol_noload +=1;
            continue;
        }
        
        if (empty($barcode)) {
            $barcode = $db->genbarcode();
            $value = "$name &nbsp;&nbsp;&nbsp;&nbsp; | $barcode &nbsp;&nbsp;&nbsp;&nbsp; | $price";
            $crtbarcode .= ($crtbarcode == "") ? "Для следующих товаров созданы новые штрихкоды: <br> $clmns <br> " : ". <br>";
            $crtbarcode .= $value;
            $kol_barcode +=1;      
        }
        
        $table = 'ZzItem';

        $q = $db->select("*", $table, "BarCode = '$barcode'");
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $itemrow = $q->single();
        if (empty($itemrow)){
            // если нет такого в таблице товаров, то создаем новый
            $data = array(
                "Name" => $name,
                "BarCode" => $barcode
            );

            //Insert record into database
            $q = $db->insert($data, $table);
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            //Get last inserted record (to return to jTable)
            $rows = $q->all();
            $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
            $itemrow = $q->single();
        }
        
        if (empty($price)) {
            $kol_strok +=1;
            continue;    
        }

        // если есть в таблице товаров, то идем дальше...
        // проверим, есть ли такая цена в БД 
        // AND  ZzPrice.Value = '$price'
        $sql = "SELECT TOP 1
          ZzPrice.Value
        FROM  dbo.ZzPrice
        WHERE ZzPrice.ItemId = '$itemrow[Id]' 
        ORDER BY ZzPrice.Date DESC";
        $q = $db->query($sql);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $zizp = $q->single();
        
        $table = 'ZzPrice';
        
        // если есть, то обновляем в БД
        $dbValue = (float) $zizp[Value]; 
        $lcPrice = (float) $price;
        if (!empty($zizp) and ($dbValue !== $lcPrice)) {

            // добавляем новую цену
            $data = array(
                "Value" => $db->clearstr($price)
            );
            // обновляем номенклатуру, если её нашли...
            $q = $db->update($data, $table, "ItemId = '$itemrow[Id]'");
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            $value = "$itemrow[Name] &nbsp;&nbsp;&nbsp;&nbsp; | $itemrow[BarCode] &nbsp;&nbsp;&nbsp;&nbsp; | $price";
            $updateitem .= ($updateitem == "") ? "Следующие цены для номенклатуры обновлены: <br> $clmns <br> " : ". <br>";
            $updateitem .= $value;
            $rows = $data;
            $kol_update +=1;
            continue;
        }
        
        // если есть, то не добавляем в БД
        if (!empty($zizp) and ($dbValue == $lcPrice)) {
//            $value = "$itemrow[Name] &nbsp;&nbsp;&nbsp;&nbsp; | $itemrow[BarCode] &nbsp;&nbsp;&nbsp;&nbsp; | $zizp[Value]";
//            $noloaditem .= ($noloaditem == "") ? "Следующие цены для номенклатуры не добавлены, так как они уже имеются в базе: <br> $clmns <br> " : ". <br>";
//            $noloaditem .= $value;
            continue;
        }

        // добавляем новую цену
        $data = array(
            "Date" => date("Ymd G:i:s"),
            "ItemId"   => $itemrow[Id],
            "Value" => $price
        );
        //Insert record into database
        $q = $db->insert($data, $table);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $kol_strok +=1;
    }
    
    $jTableResult = array();
    $jTableResult['Result'] = "OK";
    $updateitem = ($updateitem === '') ? '' : "<br><br><br><br>".$updateitem;
    $noloaditem = ($noloaditem === '') ? '' : "<br><br><br><br>".$noloaditem;
    $crtbarcode = ($crtbarcode === '') ? '' : "<br><br><br><br>".$crtbarcode;
    $Message    = $notenoughdata.$noloaditem."<br>Пропущенных строк: ".$kol_noload.$updateitem."<br>Обновлено строк: ".$kol_update.$crtbarcode."<br>Создано штрихкодов: ".$kol_barcode;
    $Message    = ($Message === '') ? "Загружено  строк: ".$kol_strok : $Message."<br>Загружено  строк: ".$kol_strok;
    $jTableResult['Message'] = $Message."<br>Всего строк: ".$kol;
    print json_encode($jTableResult);
}
catch(Exception $ex)
{
    $jTableResult['Result'] = "ERROR";
    $jTableResult['Message'] = "Не удалось загрузить файл Excel! Ошибка: ".$ex->getMessage();
    print json_encode($jTableResult);
    $is_error = TRUE;
}
?>