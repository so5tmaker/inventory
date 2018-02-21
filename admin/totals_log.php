<?php

$ExpireDate = $_POST[ExpireDate];
$ItemId = $_POST[ItemId];

function get_sign($param) {
    return (($param > 0) ? ' + '.$param : ' - '.(-$param));
}

$title_here = "Распечатка движений";
include("header.html");

if (isset($ExpireDate) AND isset($ItemId)) {
    
    $db = new db();
    $sql = "SELECT (CONVERT(varchar(10), dateadd(month,1,dateadd(day,1-day(zz.Date),zz.Date))) + '_' + CONVERT(varchar(12), zz.ItemId) + '_'  + CONVERT(varchar(10), zz.ExpireDate)) AS Ident,
                    (CONVERT(varchar(12), zz.ItemId) + '_'  + CONVERT(varchar(10), zz.ExpireDate)) AS Ident1,
                    zz.ItemId, zz.Quantity AS Quantity, zz.ExpireDate, dateadd(month,1,dateadd(day,1-day(zz.Date),zz.Date)) AS Date FROM (
            SELECT zzid.ItemId, SUM(zzid.Quantity) AS Quantity, zzid.ExpireDate, zzid.Date AS Date FROM
                    (SELECT zid.ItemId, zid.Quantity, zid.ExpireDate, zi.Date FROM ZzIncomeDetail zid 
            INNER JOIN ZzIncome zi on zid.IncomeId = zi.Id WHERE zid.ItemId=$ItemId AND zid.ExpireDate = Convert(datetime, '$ExpireDate',103)) zzid 
            GROUP BY zzid.ItemId, zzid.ExpireDate, zzid.Date
            UNION
            SELECT zzod.ItemId, -SUM(zzod.Quantity) AS Quantity, zzod.ExpireDate, zzod.Date AS Date FROM
                    (SELECT zod.ItemId, zod.Quantity AS Quantity, zod.ExpireDate,  CAST(zo.Date AS date) AS Date FROM ZzOrderDetail zod
            INNER JOIN ZzOrder zo on zod.OrderId = zo.Id WHERE zod.ItemId=$ItemId AND zod.ExpireDate = Convert(datetime, '$ExpireDate',103)) zzod GROUP BY zzod.ItemId, zzod.ExpireDate, zzod.Date) zz 
            ORDER BY zz.ItemId, zz.ExpireDate, zz.Date
    ";
    try {
        $q = $db->query($sql);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $totals = $q->all();
        $out = '';
        $last = 0;
        $qnt = 0;
        $m = array();
        $i = 0; 
        $sum = array();
        $aout= array();
        $ident = 0;
        for ($i = 0; $i <= count($totals); $i++) {
            $total = $totals[$i];
            IF ($ident == $total[Ident]) {
                $qnt = $qnt + $total[Quantity]; 
                $out.= get_sign($total[Quantity]);
            } ELSE {
                if ($i !== 0) {
                    $sum[$totals[$i-1][Ident]] = $qnt;
                    $aout[$totals[$i-1][Ident]] = $out;
                } 
                $out = ' '.get_sign($total[Quantity]);
                $qnt = $total[Quantity];
            }
            $ident = $total[Ident]; 
        }
        echo '<div class="totalslog">';
        foreach ($sum as $key => $val) { 
            echo "<br/><strong>".substr($key, 0, 10).":</strong> $last $aout[$key] = ".($val + $last);
            $last = $val + $last;
        }
        echo '<br/><a href="totals_log.php"><< Назад</a>';
        echo '</div>';
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else {
?>
<form class="totalslog" name = "Params" method = "POST" enctype = "multipart/form-data">
    <div>
        <input type = "text" name = "ItemId" value = "17132" size = "20" />
        <input type = "date" name = "ExpireDate" value = "31.03.2016" size = "20" />
    </div>
    <div>
       <input type="submit" name="submit" />
    </div>
</form >

<?
}
require_once ("footer.html");

