<?php

class db {
    
    const DEPPREFIX = "191410";
    
    private $user = "pxeclient"; // пользователь БД
    private $password = "13qe!#QE13"; // Пароль к БД
    private $server = "10.147.8.122"; // сервер БД
    private $database ="Inv"; // имя БД на сервере Admin2000
    private $port = 1433;
    public  $pdo;
    private $PDOStatement;
    
    public $charset = 'UTF-8';
    public $error = '';
    public $siteid = 4;
    public $curdate;
    public $alien; // чужой комп
    
    


    public $isTotals; // показывает, что данные идут напрямую из ZzTotals


    public function __construct() {
        $this->isTotals = false;
//        $server = "10.147.8.122"; $database ="AdminTest";
//        $user = "pxeclient"; $password = "13qe!#QE13";
//        $port = 1433; $sql = "SELECT TOP 10 * FROM SyPerson";

//        try { // dblib:host=localhost;dbname=testdb
//        $dbh = new PDO ("dblib:host=mssql:$port;dbname=$database","$user","$password");
//        } catch (PDOException $e) {
//        echo "<br>Failed to get DB handle: " . $e->getMessage() . "\n";
//        exit;
//        }
//        $stmt = $dbh->prepare($sql);
//        $stmt->execute();
//        while ($row = $stmt->fetch()) {
//        print_r($row);
//        }
//        unset($dbh); unset($stmt);
        try {    
            $this->alien = TRUE;
            if ($this->local()) {
                $this->pdo = new PDO ("sqlsrv:server={$this->server},{$this->port};database={$this->database}",$this->user,$this->password);
//                $db = new PDO("sqlsrv:server=$db_server,$this->port;database=$db_database;", $db_user, $db_passwd);
                $this->charset = 'windows-1251';
                $this->alien = FALSE;
            } else {
                $this->pdo = new PDO ("dblib:host=mssql:$this->port;dbname=$this->database;charset=UTF-8",$this->user,$this->password);
            }
            $this->setsiteid();
            $this->setcurdate();
        } catch (PDOException $e) {
            echo "<br>Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
        }	
    }
    
    // убираю разряды из числовых значений
    public function fixcells($rows) {
        $newrows = array();
        foreach ($rows as $key => $value) {
            foreach ($value as $key1 => $value1) {
                if(strpos($key1, 'Id')!==FALSE){
                    $newrows[$key][$key1] = $value1;
                    continue;
                }
                if($key1=='Date' or $key1=='ExpireDate'){
                    if (strlen($value1) > 20){
                        $a1 = array(
                            0 => 4,
                            1 => 6
                        );
                        $a2 = array(
                            0 => '.',
                            1 => ':'
                        );
                        for ($i = 0; $i <= count($a1) - 1; $i++) {
                            $s1 = substr($value1, strlen($value1)-$a1[$i], strlen($value1));
                            $p1 = substr($s1, 0, 1);
                            if ($p1 === $a2[$i]){
                                $value1 = substr($value1, 0, strlen($value1) - strlen($s1));
                                break;
                            } 
                        }
                    }
                    $newrows[$key][$key1] = $value1;
                    continue;
                }  
                if($key1=='Quantity' || $key1=='Price' || $key1=='Sum'){
                    // убираю третий ноль из числа .560 -> .56
                    $pos = strpos($value1, '.');
                    $old = substr($value1, $pos);
                    $new = substr($old, 0,3);
                    $newrows[$key][$key1] = str_replace($old , $new, $value1);
//                    $newrows[$key][$key1] = str_replace('.000' , '.00', $value1);
                    continue;
                }
                $newrows[$key][$key1] = $value1;
            }
        }
        return $newrows;
    }
    
    function get_option_period($per = 'week', $deep = 10) {
        date_default_timezone_set('Asia/Almaty');
        if ($per == 'week') {
            $rper = "Неделя";
            $cper = "Текущая неделя"; 
        }else{
            $rper = "Месяц";
            $cper = "Текущий месяц"; 
        }
        $period = '<option value="0" selected="selected">'.$cper.'</option>';
        for ($i = 1; $i <= $deep; $i++) {
            $curday = strtotime("-$i $per");
            if ($per == 'week') {
                $curweekday = date('N', $curday); // текущий день недели
                $monday = date('j', $curday - ($curweekday - 1)*24*3600);
                $txt = "$rper от ".$this->rdate("$monday M Y", $curday);  
            } else {
                $txt = "$rper от ".$this->rdate("1 M Y", $curday);
            }
            $period.='<option value="'.$i.'">'.$txt.'</option>';
        }
        return $period;
    }
    
    function rdate($param, $time=0) {
        if(intval($time)==0)$time=time();
        $MN=array("Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
        $MonthNames[]=$MN[date('n',$time)-1];
        //$MN=array("","Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота", "Воскресенье");
        $MN=array("Воскресенье","Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота");
        $MonthNames[]=$MN[date('w',$time)];
        $arr[]='M';
        $arr[]='N';
        if(strpos($param,'M')===false) return date($param, $time); else return date(str_replace($arr,$MonthNames,$param), $time);
    }
    
    public function setcurdate($curDate = 0) {
        date_default_timezone_set('Asia/Almaty');
        $this->curdate = ($curDate == 0) ? strtotime("now") : $curDate;
    }
    
    public function in_period($begin = 0, $end = 0) {
        date_default_timezone_set('Asia/Almaty');
        // Устанавливаю интервал по умолчанию с Пятницы, 12:00:00 по Воскресенье, 23:59:59  
//        $lbegin = ($begin == 0) ? $this->getdate(4, 2) : $begin; // Friday, 12:00:00
//        $lend = ($end == 0) ? $this->getdate(6, 3) : $end; // Sunday, 23:59:59
        $lbegin = ($begin == 0) ? $this->getdate(0, 0) : $begin; // Monday, 00:00:00 начало недели
        $lend   = ($end == 0)   ? $this->getdate(6, 3) : $end; // Sunday, 23:59:59 конец недели
        if ($this->curdate >= strtotime($lbegin) AND $this->curdate <= strtotime($lend)){
            return TRUE;
        }
        return FALSE;
    }
    
    public function getdate($intWeekDay, $intTime) { 
        date_default_timezone_set('Asia/Almaty');
        $days = array(
            0 => "Monday",
            1 => "Tuesday",
            2 => "Wednesday",
            3 => "Thursday",
            4 => "Friday",
            5 => "Saturday",
            6 => "Sunday"
        );
        $times = array(
            0 => "00:00:00",
            1 => "11:59:59",
            2 => "12:00:00",
            3 => "23:59:59"
        );
        $day_of_week = date("N", $this->curdate) - 1;
        if (($day_of_week <= $intWeekDay)){
            return date("d.m.Y $times[$intTime]", strtotime("$days[$intWeekDay]", $this->curdate));
        } else {
            return date("d.m.Y $times[$intTime]", strtotime("last $days[$intWeekDay]", $this->curdate));
        }  
    }
    
    public function add_new_item($barcode = "") {
        $table   = 'ZzItem';
        $code = ($barcode == "") ? $this->genbarcode() : $barcode;
        // создаем новый товар
        $data = array(
                    "Name" => "РезервныйШтрихкод",
                    "BarCode" => $code
                );
        //Insert record into database
        $this->insert($data, $table);
        $this->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
        return $this;
    }
    
    public function add_new_order($siteid, $prsnid) {
        $table = 'ZzOrder'; 
//        date_default_timezone_set('Asia/Almaty');
        $data = array(
            "Date" => date("Ymd G:i:s", $this->curdate),
            "SiteId" => $siteid,
            "PersonId" => $prsnid
            );
        //Insert record into database
        $this->insert($data, $table);
        $this->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
        return $this;
    }
    
    public function getitemprice($barcode) {
        $barcode7 = substr($barcode, 0, 7);
        $q = $this->select("BarCode", 'ZzItem', "BarCode LIKE '$barcode7%' AND BarCode LIKE '%ves%'");
        if ($q->error <>'') {
            return FALSE;
        }
        $rows = $q->single();
        if (empty($rows)){
            $WHERE = "WHERE cast(BarCode as nvarchar(100)) = '$barcode'";
            $Quantity = 1;
        }  else {
            $WHERE = "WHERE cast(BarCode as nvarchar(100)) = '$rows[BarCode]'";
            $Quantity = substr($barcode, 7, -1)/1000;
        } 
        $sql = "
            SELECT zi1.Id As ItemId, zi1.BarCode, zi1.Name, zp1.Value AS Price, zp1.Value, zp1.Id AS priceid, $Quantity As Quantity FROM (
            SELECT * From (
                SELECT * FROM ZzItem $WHERE
            ) zi 
                INNER join (
                    SELECT ItemId, MAX(Date) as PDate FROM  
                        ZzPrice WHERE ItemId IN (SELECT Id FROM ZzItem $WHERE) GROUP BY  ItemId
                ) zp 
                ON (zi.Id = zp.ItemId)
            ) zi1 LEFT JOIN (
                    SELECT ItemId, Date, Value, Id From ZzPrice
                  ) zp1 
            ON (zi1.Id = zp1.ItemId AND zi1.PDate = zp1.Date)
            ";
        return $this->query($sql, $barcode);
    }
    
    public function checkbarcode($barcode) {
        $q = $this->select("BarCode", 'ZzItem', "BarCode = '$barcode'");
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $itemrow = $q->single();
        if (empty($itemrow)){
            return FALSE;
        }  else {
            return TRUE;
        } 
    }

    public function barcode_gen_ean_sum($ean){
      $even=true; $esum=0; $osum=0;
      for ($i=strlen($ean)-1;$i>=0;$i--){
            if ($even) $esum+=$ean[$i];	else $osum+=$ean[$i];
            $even=!$even;
      }
      return (10-((3*$esum+$osum)%10))%10;
    }
    
    public function genbarcode($barcode = "") {
        if ($barcode === "") {
            $q = $this->select("ISNULL(MAX(BarCode),0) AS BarCode", 'ZzItem', "BarCode LIKE '2000000%'");
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            $itemrow = $q->single();
            if (empty($itemrow)){
                $barcode = '200000000001';
            }  else {
                if ($itemrow[BarCode] === '0'){
                    $barcode = '200000000001';
                }else{
                    $barcode = substr($itemrow[BarCode], 0, 12);
                } 
//                $barcode = substr($barcode, 0, -1);
                $barcode = ($barcode == '200000000001') ? $barcode : $barcode + 1;
            }
        }else{
            $barcode = substr($barcode, 0, 12) + 1;
        }
        
        $barcode.=$this->barcode_gen_ean_sum("".$barcode);

        if ($this->checkbarcode($barcode)) {
            return $this->genbarcode($barcode);
        } else {
            return $barcode;
        }
    }
    
    public function generate_code($length = 12){
        $code = '';
        $symbols = '0123456789abcdefghijklmnopqrstuvwxyz';
        for( $i = 0; $i < (int)$length; $i++ )
        {
            $num = rand(1, strlen($symbols));
            $code .= substr( $symbols, $num, 1 );  
        }           
        $barcode = "F$code";
        if ($this->checkbarcode($barcode)) {
            return $this->generate_code($barcode);
        } else {
            return $barcode;
        }
    }
    
    public function getarray($param) {
        $val = filter_input(INPUT_POST, $param);
        if (!isset($val)){
            $val = filter_input(INPUT_GET, $param);
        }
        if (isset($val)){
            return explode(",", $val);
        }
        return NULL;
    }

    public function local() {
        //Здесь я проверяю путь к файлу исполняемого в данный момент скрипта,
        //чтобы определить какую базу мне нужно локальную или удаленную
        $SCRIPT = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
        return (strstr($SCRIPT, 'inventory') === FALSE) ? FALSE : TRUE;
    }
    
    public function setsiteid() {
        $SERVER = $_SERVER[REMOTE_ADDR];
        if (strstr($SERVER, '243') !== FALSE) {
            $this->siteid = 1;
            // 10.147.10.243 - склад в ? 
            $this->alien = FALSE;
        }
        if (strstr($SERVER, '240') !== FALSE OR 
                strstr($SERVER, '241') !== FALSE OR 
                strstr($SERVER, '242') !== FALSE OR 
                strstr($SERVER, '10.147.11.30') !== FALSE OR
                strstr($SERVER, '10.147.64.4') !== FALSE) {
            $this->siteid = 4;
            // 10.147.10.240-242 - склад в четвертом здании 
            $this->alien = FALSE;
        }
    }
    
    public function getparambyprop($prop, $date = '') {
        $locdate = ($date == '') ? "Convert(datetime,'".date("d.m.Y")."',103)" : "Convert(datetime,'".$date."',103)";
//        $this->select("Value", 'ZzParam', "Prop='$prop' AND Date<=$locdate");
        $sql = "SELECT zp1.Value FROM (
                    SELECT MAX(Date) AS Date, Prop from ZzParam  WHERE
                        Prop='СуммаНаВыходные' GROUP BY Prop
                    ) zp
                    INNER join (
                    SELECT Date as Date, Prop AS Prop, Value FROM  ZzParam 
                    ) zp1 
                    ON (zp.Date = zp1.Date AND zp.Prop = zp1.Prop)";
        $this->query($sql);
        $ret = $this->single();
        return (empty($ret[Value])) ? 0 : $ret[Value];
    }
    
    public function getparambyid($id) {
        $this->select("Value", 'ZzParam', "id=$id");
        $ret = $this->single();
        return $ret[Value];
    }
    
    public function query($sql) {
        // пример: $db->query("SELECT * FROM aslkd WHERE id = ?",$id);
        $this->PDOStatement = $this->pdo->prepare($sql);
        $this->args = func_get_args(); // получаю все аргументы в виде массива
        $first = array_shift($this->args); // в $first присваевается первый элемент массива, а в $this->args остаются все без первого элемента
        if ($this->PDOStatement->execute($this->args) === false) { // заменяем все вопросики в тексте запроса на аргументы из $this->args
            echo "\nPDOStatement::errorInfo():\n";
            $arr = $this->PDOStatement->errorInfo();
            $this->error = "Ошибка в запросе: ".$arr[2];
        }
        return $this;		
    }

    public function checkexe($mode) {
        if ($this->PDOStatement->execute() === false) { 
            echo "\nPDOStatement::errorInfo():\n";
            $arr = $this->PDOStatement->errorInfo();
            $this->error = "Ошибка $mode в запросе: ".$arr[2];
        }
        return $this;
    }

    public function get_level($id) {
        $q = $this->select('Level', "ZzItem", "Id=$id");
        $row = $q->single();
        if (!empty($row)) {
            return $row[Level] + 1;
        }
        return 0;
    }
    
    public function select($clmns = "*", $table, $where = "", $sort = "") {
        // $clmns = "ItemID, Name, BareCode";
        // $where = "ItemID = ? AND Name=?",$id, $name);
        $where_loc = ($where == "") ? "" : "WHERE ".$where;
        $sql = "SELECT $clmns FROM $table $where_loc $sort";
        $this->PDOStatement = $this->pdo->prepare($sql);
        return $this->checkexe('выборки');
    }
    
    public function clearstr($str) {
        // убираю пробелы и другие символы в строке
        return preg_replace("/[^x\d|*\.]/"," ",preg_replace('!(?:\xc2\xa0|[\pZ\s]++)++!', ' ', $str));
    }
    
    // изменяю количество по заданным измерениям
    public function change_quantity($row, $newdata, $where) {
        $tbl = 'ZzTotal';
        $Quantity = $newdata[Quantity];
        $remains = $row[qnt_new] - $row[qnt_old];
        $newqnt = $Quantity + $remains;
        // для нового кода товара, если такого товара
        // еще нет в таблице итогов, вводится новая запись
        if ($Quantity == 0 AND $newdata[ItemId] == 0) {
            $newdata[Quantity] = $row[qnt_new];
            //Insert record into ZzTotal
            $q = $this->insert($newdata, $tbl);
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            $newdata[Sign] = '>';
        } else {
           if ($remains == 0) {
                //Delete from database
                $q = $this->delete($tbl, $where);
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
                $newdata[Quantity] = $row[qnt_new];
                $newdata[Sign] = '>=';
           } else {
                $newdata[Quantity] = $remains;
                $newdata[Sign] = '>=';
           }
        }
        $q = $this->update_totals($newdata);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        // меняю актуальные итоги
//        $this->change_actual_totals($newdata);
    }
    
    // проверим изменились ли измерения, если да, 
    // то нужно менять данные в двух направлениях
    public function check_dimensions($row, $newdata) {
        $change = false;
        if($row[ItemId_old] !== $row[ItemId_new]){
            $change = true;
        }
        if($row[ExpireDate_old] !== $row[ExpireDate_new]){
            $change = true;
        }
         if($row[Date_old] !== $row[Date_new]){
            $change = true;
        }
        // если изменились реквизиты табличной части документа, 
        // то создаем или удаляем в таблице итогов строки
        if ($change) {
            // ищу сумму в ZzTotal по новому условию, чтобы добавить сумму
            $row_new = $row;
            $where_new = "ItemId=$row_new[ItemId_new] AND ExpireDate = '$row_new[ExpireDate_new]' AND Date = '$row_new[Date_new]'";
            $newdata[Quantity] = $this->get_total_quantity($where_new);
            $row_new[qnt_old]  = 0;
            // изменяю количество по заданным измерениям
            $this->change_quantity($row_new, $newdata, $where_new);
            
            // ищу сумму в ZzTotal по новому условию, чтобы отнять от суммы
            $row_old = $row;
            $where_old = "ItemId=$row_old[ItemId_old] AND ExpireDate = '$row_old[ExpireDate_old]' AND Date = '$row_old[Date_old]'";
            $newdata[Quantity]   = $this->get_total_quantity($where_old);
            $newdata[ItemId]     = $row_old[ItemId_old];
            $newdata[ExpireDate] = $row_old[ExpireDate_old];
            $newdata[Date]       = $row_old[Date_old];
            $row_old[qnt_new]    =  0;
            // изменяю количество по заданным измерениям
            $this->change_quantity($row_old, $newdata, $where_old);
        // если изменилось количество табличной части документа, 
        // то меняем в таблице итогов только одну строку в колонке количество
        } else {
            $where = "ItemId=$newdata[ItemId] AND ExpireDate = '$newdata[ExpireDate]' AND Date = '$newdata[Date]'";
            // изменяю количество по заданным измерениям
            $this->change_quantity($row, $newdata, $where);
        }    
    }
    
    //метод для получения значений массива по условию, 
    // который появляется в результате работы запроса
    public function get_values($ItemId, $date) {
//        $sql = "SELECT zz.ItemId, zz.BalanceExpireDate,  
//            MAX(zz.Quantity) AS Quantity, SUM(zz.BalanceQuantity) AS BalanceQuantity,
//            MAX(zz.BalanceDate) AS BalanceDate From (
//        SELECT  MAX(zod.ItemId) AS ItemId, MAX(zod.Quantity) AS Quantity,
//	SUM(Round(zt.Quantity,3)) AS BalanceQuantity, 
//        MAX(zt.ExpireDate) AS BalanceExpireDate, MAX(zt.Date) AS BalanceDate
//        FROM ZzTotal zt LEFT JOIN ZzOrderDetail zod ON zt.ItemId = zod.ItemId
//        WHERE (zt.Date <= '$date') And (zt.ItemId IN ($ItemId))
//            GROUP BY zod.ItemId, zt.ExpireDate, zt.Date
//        HAVING (SUM(Round(zt.Quantity,3)) > 0)
//        ) zz group by ROLLUP (zz.ItemId, zz.BalanceExpireDate) 
//        ORDER by zz.ItemId, zz.BalanceExpireDate";
        $sql = "SELECT  ItemId AS ItemId, SUM(Quantity) AS Quantity,
                ExpireDate AS ExpireDate, 
                Date AS Date
         FROM ZzTotal 
         WHERE (Date <= Convert(datetime,'$date',103)) And (ItemId = '$ItemId')
                    GROUP BY ItemId, ExpireDate, Date
          HAVING (SUM(Quantity) > 0) ORDER by ExpireDate";
        $q = $this->query($sql);
        if ($q->error <>'') {
            return $q->error;
        }
        return $q->all();
//        $values = array();
//        if ($mode == 'groups') { // получаем значения группировок номенклатуры по итоговому количеству
//            $values[groups] = array_filter($rows, 
//                    function ($var) {
//                        return is_array($var) && 
//                        $var['ItemId'] !== null && 
//                        $var['BalanceExpireDate'] == null;
//                    }
//                );
//        } else { // получаем значения номенклатуры по количеству
//            $values[items] = array_filter($rows, 
//                    function ($var) {
//                        return is_array($var) && 
//                        $var['ItemId'] == $ItemId && 
//                        $var['BalanceExpireDate'] !== null;
//                    }
//                );
//        }
//        return $values;
        
    }
    
    // получение актуального количества из таблицы ZzTotal по заданному условию
    public function get_actual_quantity($where) {

        $q = $this->select("ISNULL(SUM(Quantity),0) AS Quantity", 'ZzTotal', $where);
        $row = $q->single();
        if (empty($row)) {
            return 0;
        }
        return $row[Quantity];
    }
    
    public function change_actual_totals($data) {
        $tbl = 'ZzTotal';
        // получим актуальное количество из таблицы ZzTotal по заданному условию
        $where = "ItemId=$data[ItemId] AND ExpireDate = '$data[ExpireDate]' AND Date = '$data[Date]'";
//        $Quantity = $this->get_actual_quantity($where);
        $Quantity = $this->get_total_quantity($where);
        $data[Date] = '39991101';
        try {
            // удаляю запись, если по этим условиям получил ноль
            if ($Quantity == 0) {
                //Delete from database
                $q = $this->delete($tbl, $where);
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
            } else {
                $data[Quantity] = $Quantity;
                // получаю общее актуальное количество
                $where = "ItemId=$data[ItemId] AND ExpireDate = '$data[ExpireDate]' AND Date = '39991101'";
                $Quantity = $this->get_total_quantity($where);
                if ($Quantity == 0) {
                    //Insert record into ZzTotal
                    $q = $this->insert($data, $tbl);
                    if ($q->error <>'') {
                        throw new Exception($q->error);
                    }
                } else { 
                    //Update record in database
                    $q = $this->update($data, $tbl, $where);
                    if ($q->error <>'') {
                        throw new Exception($q->error);
                    }
                }   
            }       
        } catch(Exception $ex) {
            return $ex->getMessage();
        }
        return true;
    }
    
    public function update_totals($data) {
        $sql2 = "UPDATE ZzTotal SET Quantity = Quantity + $data[Quantity] 
                WHERE ItemId=$data[ItemId] 
                AND ExpireDate = '$data[ExpireDate]' 
                AND Date $data[Sign] '$data[Date]'";
        $q2 = $this->query($sql2);
        return $q2;
    }
    
    // получение количества из таблицы ZzTotal по заданному условию
    public function get_total_quantity($data, $params = array()) { 
        if (is_array($data)) {
            // FirstQuantity - это количество прошлого месяца
            // SecondQuantity - это количество текущего месяца
            // z1 - таблица, которая получает последние остатки
            // z2 - таблица, которая получает остатки на месяц, в котором вводится данный док
            
            if (isset($params[mvDate]) AND $params[setDate]) {
                $ziDate = "'$params[mvDate]'";
            }else{
                $ziDate = "(
                -- этим запросом получаем последнюю дату, в которой были движения
                SELECT TOP 1 CAST(dateadd(day,1-day(zz.Date),zz.Date) AS DATE) AS FirstDayOfRemains
                FROM (
                        SELECT ISNULL(MAX(zi.Date),'$data[Date]') AS Date from ZzIncomeDetail zid
                                INNER JOIN ZzIncome zi on zid.IncomeId=zi.Id
                        WHERE zid.ItemId=$data[ItemId] AND zi.Date < '$data[Date]'
                        UNION
                        SELECT MAX(zod.Date) AS Date from ZzOrderDetail zod
                        INNER JOIN ZzOrder zo on zod.OrderId=zo.Id
                        WHERE zod.ItemId=$data[ItemId] AND zo.Date < '$data[Date]'
                ) zz WHERE zz.date is not NULL ORDER BY Date)";
            }
            
            $sql2 = "
                SELECT ISNULL(zt1.Quantity,0) AS FirstQuantity, zt1.Id AS FirstId,
                        ISNULL(zt2.Quantity,0) AS SecondQuantity, zt2.Id AS SecondId
                FROM ZzTotal zt1
                FULL JOIN (SELECT * FROM ZzTotal WHERE Date = '$data[Date]') zt2 ON 
                zt2.ItemId=zt1.ItemId AND zt2.ExpireDate = zt1.ExpireDate
                WHERE zt1.ItemId=$data[ItemId] AND zt1.Date = $ziDate
                AND zt1.ExpireDate = '$data[ExpireDate]'";
            $q2 = $this->query($sql2);
            $row2 = $q2->single();
            if (empty($row2)) {
                $row2[FirstQuantity] = 0;
                $row2[FirstId] = 0;
                $row2[SecondQuantity] = 0;
                $row2[SecondId] = 0;
            }
            return $row2;
        } else {
            $q = $this->select("ISNULL(Quantity,0) AS Quantity, ISNULL(ItemId,0) AS ItemId", 'ZzTotal', $data);
            $row = $q->single();
            if (empty($row)) {
                $row[Quantity] = 0;
                $row[ItemId] = 0;
            }
            return $row;
        }
    }
    
    public function mvDate($fdm) {
        return date('Y-m-d', mktime(0, 0, 0, date("m", strtotime($fdm)), date("d", strtotime($fdm)), date("Y", strtotime($fdm))));
    }
    
    // метод для изменения остатков в таблице итогов
    public function change_totals($mode, $table, $data) {
        $tbl = 'ZzTotal';
        try {
            if ($table == 'ZzIncomeDetail' OR $table == 'ZzOrderDetail') {
                $params[setDate] = true;
                If ($table == 'ZzIncomeDetail'){
                    $DocId = 'IncomeId';
                    $MainTbl = 'ZzIncome';
                }
                If ($table == 'ZzOrderDetail'){
                    $DocId = 'OrderId';
                    $MainTbl = 'ZzOrder';
                    $data[Quantity] = -(int)$data[Quantity];
//                    $params[setDate] = false;
                }                
                
                // нахожу дату документа
                $q = $this->select("dateadd(month,1,dateadd(day,1-day(Date),Date)) AS FirstDayOfMonth, dateadd(day,1-day(Date),Date) AS mvDate", $MainTbl, "Id = $data[$DocId]");
                $row = $q->single();
                if (empty($row)) {
                    return false;
                }

                $FirstDayOfMonth = $this->mvDate($row[FirstDayOfMonth]);
                if ($this->isTotals) {
                    $params[mvDate] = $data[Date];
                } else {
//                    $params[mvDate] = $this->mvDate($row[mvDate]);
                    $params[mvDate] = $FirstDayOfMonth;
                }
                
                $newdata = array(
                    "ItemId" => $data[ItemId],
                    "Quantity" => 0,
                    "Date" => $FirstDayOfMonth,
                    "ExpireDate" => $data[ExpireDate]
                );
                //Update record in ZzTotal
                if ($mode == 'update') {
                    // получим массив дат начала месяца после $FirstDayOfMonth 
                    // из таблицы ZzTotal по заданному условию
                    $where = "ItemId=$data[ItemId] AND ExpireDate = '$data[ExpireDate]' AND Date = '$FirstDayOfMonth'";
                    $newrow = $this->get_total_quantity($where);
                    $newdata[Quantity] = $newrow[Quantity];
                    $newdata[ItemId] = $newrow[ItemId];
                    // получим новое и старое количество после обновления
                    $q = $this->select("*", "dbo.".$table."_$mode");
                    $row = $q->single();
                    if (empty($row)) {
                        return false;
                    }
                    $row[Date_new] = $FirstDayOfMonth;
                    $row[Date_old] = $FirstDayOfMonth;
                    
                     // проверим изменились ли измерения, если да, 
                     // то нужно менять данные в двух направлениях
                    $this->check_dimensions($row, $newdata, $FirstDayOfMonth);
                }
                //Insert record in ZzTotal
                if ($mode == 'insert') {   
                    $qntData = $this->get_total_quantity($newdata, $params);
                    // Если в текущем месяце нет остатков, 
                    // то добавляем к ним остатки с прошлого месяца
                    if ($qntData[SecondQuantity] == 0 AND $qntData[SecondId] == 0) {
                        $newdata[Quantity] = $data[Quantity] + $qntData[FirstQuantity];
                        //Insert record into ZzTotal
                        $q = $this->insert($newdata, $tbl);
                        if ($q->error <>'') {
                            throw new Exception($q->error);
                        }
                        
                        $newdata[Quantity] = $data[Quantity];
                        $newdata[Sign] = '>';
                        $q = $this->update_totals($newdata);
                        if ($q->error <>'') {
                            throw new Exception($q->error);
                        }
                    }  else {
                        $newdata[Quantity] = $data[Quantity];
                        $newdata[Sign] = '>=';
                        $q = $this->update_totals($newdata);
                        if ($q->error <>'') {
                            throw new Exception($q->error);
                        }
                    }
                }
                if ($mode == 'delete') {
//                    $params[mvDate] = $FirstDayOfMonth;
                    $qntData = $this->get_total_quantity($newdata, $params);
                    // Если в текущем месяце нет остатков, 
                    // то отнимаем количество от остатков с прошлого месяца
                    if ($qntData[SecondQuantity] == 0) {
                        $newdata[Quantity] = $qntData[FirstQuantity] - $data[Quantity];
                        //Insert record into ZzTotal
                        $q = $this->insert($newdata, $tbl);
                        if ($q->error <>'') {
                            throw new Exception($q->error);
                        }
                        
                        $newdata[Quantity] = -$data[Quantity];
                        $newdata[Sign] = '>';
                        $q = $this->update_totals($newdata);
                        if ($q->error <>'') {
                            throw new Exception($q->error);
                        }
                    } else {
                        $newqnt = $qntData[FirstQuantity] - ($qntData[SecondQuantity] - $data[Quantity]); 
                        if ($newqnt == 0) {
                            //Delete from database
                            $q = $this->delete($tbl, $where);
                            if ($q->error <>'') {
                                throw new Exception($q->error);
                            }
                            $newdata[Quantity] = -$data[Quantity];
                            $newdata[Sign] = '>';
                            $q = $this->update_totals($newdata);
                            if ($q->error <>'') {
                                throw new Exception($q->error);
                            }
                        } else {
                            $newdata[Quantity] = - $data[Quantity];
                            $newdata[Sign] = '>=';
                            $q = $this->update_totals($newdata);
                            if ($q->error <>'') {
                                throw new Exception($q->error);
                            }
                        }
                    }
                }
//                if ($mode == 'delete' OR $mode == 'insert') {
//                    // меняю актуальные итоги
//                    $actual = $this->change_actual_totals($newdata);
//                    if (is_string($actual)) {
//                        throw new Exception($actual);
//                    }
//                }
            } //if ($table == 'ZzIncomeDetail' OR $table == 'ZzOrderDetail') {
        } catch(Exception $ex) {
            return $ex->getMessage();
        }
        return true;
    }
    
    public function get_clmns_str($table) {
        $params = "";
        // получаем названия полей и их типы
        $q = $this->select("COLUMN_NAME, DATA_TYPE", 'INFORMATION_SCHEMA.columns', "TABLE_NAME = N'$table'");
        $row = $q->all();
        if (empty($row)) {
            return $params;
        }
        foreach ($row as $column => $type) {
            $params .= ($params == "") ? "" : ", ";
            $params .= $type[COLUMN_NAME].' '.$type[DATA_TYPE];
        }
        return $params;
    }
    
    // создадим таблицы, чтобы сохранять старое и новое количество
    public function crt_tbl($mode, $table) {
        $crt_tbl = "IF OBJECT_ID('dbo.".$table."_$mode') IS NOT NULL
            DROP TABLE dbo.".$table."_$mode

            CREATE TABLE dbo.".$table."_$mode
            (
            qnt_old decimal(15, 3) DEFAULT 0 NOT NULL,     
            ItemId_old int NOT NULL,
            ExpireDate_old date NOT NULL,
            qnt_new decimal(15, 3) DEFAULT 0 NOT NULL,
            ItemId_new int NOT NULL,
            ExpireDate_new date NOT NULL
            )";
        $this->query($crt_tbl);
    }
    
    public function update($data = "", $table, $where = "") {
        $mode = 'update';
        if ($table == 'ZzIncomeDetail' OR $table == 'ZzOrderDetail') {
            If ($table == 'ZzIncomeDetail'){
                $DocId = 'IncomeId';
            }
            If ($table == 'ZzOrderDetail'){
                $DocId = 'OrderId';
            }
            // получим названия полей и их типы
//            $params = $this->get_clmns_str($table);
            // создам таблицу для обновления полей
            $this->crt_tbl($mode, $table);    
            $output = "";
            $output = "OUTPUT DELETED.Quantity, DELETED.ItemId, "
                    . "DELETED.ExpireDate, "
                    . "INSERTED.Quantity, INSERTED.ItemId, "
                    . "INSERTED.ExpireDate "
                    . "INTO dbo.".$table."_$mode";
            foreach ($data as $column => $value) {
                $params .= ($params == "") ? "" : ", ";
                $params .= "$column = '$value'";   
            }
            $sql = "UPDATE $table SET $params $output WHERE $where";
            $this->PDOStatement = $this->pdo->prepare($sql);
            $q = $this->checkexe('обновления');
            if (!is_object($q)) {
                return $q;
            }
            // измененим остатки в таблице итогов
            $this->change_totals($mode, $table, $data);
        } else {
            //Update record in database
            foreach ($data as $column => $value) {
                $sql = "UPDATE $table SET $column = '$value' WHERE $where";
                $this->PDOStatement = $this->pdo->prepare($sql);
                $q = $this->checkexe('обновления');
                if (!is_object($q)) {
                    return $q;
                }
            }
        }
        return $this;
    }

    public function insert($data = "", $table) {
        $columns = "";
        $values = "";
        foreach ($data as $column => $value) {
            $columns .= ($columns == "") ? "" : ", ";
            $columns .= $column;
            $values .= ($values == "") ? "" : ", ";
            $values .= "'$value'";
        }
        $sql = "insert into $table ($columns) values ($values)";
        // пример: $sql = "INSERT INTO square (point, area, date)
        // VALUES (N'FT2', N'Square Feet ', '20080923'), (N'Y', N'Yards', '20080923'), (N'Y3', N'Cubic Yards', '20080923')";
        $this->PDOStatement = $this->pdo->prepare($sql);
        $q = $this->checkexe('вставки');
        if (!is_object($q)) {
            return $q;
        }
        if ($table == 'ZzIncomeDetail' OR $table == 'ZzOrderDetail') {
            // измененим остатки в таблице итогов
            $this->change_totals('insert', $table, $data);
        } 
        return $this;
    }
    
    //Вносит изменения в БД, удаляя записи
    public function delete($table, $where) {
        $has = true;
        if ($table == 'ZzIncomeDetail' OR $table == 'ZzOrderDetail') {
            $q = $this->select("*", $table, $where);
            $data = $q->single();
            if (empty($data)) {
                $has = false;
            }
        }
        $sql = "DELETE FROM $table WHERE $where";
        $this->PDOStatement = $this->pdo->prepare($sql);
        $q = $this->checkexe('удаления');
        if (!is_object($q)) {
            return $q;
        }
        if ($table == 'ZzIncomeDetail' OR $table == 'ZzOrderDetail' AND $has) {
            // измененим остатки в таблице итогов
            $this->change_totals('delete', $table, $data);
        } 
        return $this;
    }
    
    public function assoc() {
        $result = array();
        while ($row = $this->PDOStatement->fetch(PDO::FETCH_ASSOC)) $result[] = $row;
        return $result;
    }

    public function all() {
        return $this->PDOStatement->fetchAll();
    }
    
    public function single() {
        $vols = $this->PDOStatement->fetchAll();
        if (!empty($vols)){
            return $vols[0];
        } 
    }
	
}

//class mssql {
//    private static $statement=null;
//
//    private static $typemap=array(
//        'ntext' => 'text',
//        'bigint' => 'real',
//        'decimal' => 'real',
//        'float' => 'real',
//        'numeric' => 'real'
//    );
//   
//    public static function all_fields($table) {
//        if(self::$statement==null) {
//            $pdoobj = new db();
//            $db = $pdoobj->pdo;
////            $db=pdodb::instance(); // or however you get your global instance
//            $query="SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=?";
//            self::$statement=$db->prepare($query);
//        }
//       
//        self::$statement->execute(array($table));
//       
//        $fields=array();
//        $need_cast=false;
//        while($field=self::$statement->fetch()) {
//            $field_quoted=self::quote_field($field['COLUMN_NAME']);
//            if(isset(self::$typemap[$field['DATA_TYPE']])) {
//                $fields[]='CAST('.$field_quoted.' AS '.self::$typemap[$field['DATA_TYPE']].') AS '.$field_quoted;
//                $need_cast=true;
//            } else $fields[]=$field_quoted;
//        }
//        return ($need_cast) ? implode(', ',$fields) : '*';
//    }
//   
//    public static function quote_field($field) {
//        $pos=strpos($field,' ');
//        return ($pos===false) ? $field : '['.$field.']';
//    }
//}
