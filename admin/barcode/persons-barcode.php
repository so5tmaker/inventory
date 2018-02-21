<?php session_start();?>
<html>
  <head>
    <meta charset="UTF-8">  
    <title>Штрихкоды вефильцев</title>
   </head>
   <body>
<?php
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
    SELECT pr.PersonId AS PersonId,dbo.fnPersonName(pr.PersonId, DEFAULT) AS Name, sy.VolunteerNumber AS VolunteerNumber
        from Admin2000.dbo.SyPersonEnrollment pr 
        INNER JOIN Admin2000.dbo.VoEnrollment En ON En.EnrollmentId = Pr.EnrollmentId 
        LEFT JOIN Admin2000.dbo.OrSupervisedGeoLocation Sg ON Sg.GeoLocationId = Pr.GeoLocationId 
        left join Admin2000.dbo.OrGeoLocation ge on ge.GeoLocationId = Sg.GeoLocationId 
        inner join Admin2000.dbo.SyPerson sy on sy.PersonId = pr.PersonId 
        WHERE pr.PersonId IN ($params) AND En.BethelEnrollmentYesNo = 1 AND CONVERT (nvarchar, GETDATE(), 112) BETWEEN Pr.StartDate AND 
        ISNULL(Pr.EndDate, N'29991231') AND (Pr.GeoLocationId IS NULL OR Sg.GeoLocationId = Pr.GeoLocationId) 
        and sg.GeoLocationId=115 ORDER BY Name";
    
    
    $q = $db->query($sql);
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    $prcs = $q->all();
    foreach ($prcs as $key => $prc) {
        $code = $prc[VolunteerNumber];
        $bars = barcode_encode($code,"39");
        $image = "img/".preg_replace('/[\\/:*?\'<>|]/', '', $code).".".$mode;
        barcode_outimage($bars['text'],$bars['bars'], 1, $mode, 0, '', $image);
        if (isset($barcode)){
            for ($i = 1; $i <= $quant; $i++) {
                $out = '<img src="'.$image.'" align="top" />'; 
                print "<div style='float: left; width: 20%; padding-bottom: 2px;'>".$out."</div>";
            } 
        } else {
            $out = '<img src="'.$image.'" align="top" />'; 
            print "<div style='float: left; width: 70%; vertical-align: top; font-size: 1.5em;'>".$prc[Name]."</div>";
            print "<div style='float: left; width: 15%; padding-bottom: 20px;'>".$out."</div>";
//            print "<div style='float: left; width: 15%; padding-top: 2%; font-size: 1.3em; text-align: right;'>".$prc[Value]."</div><br>";
            echo "<div style='clear: both;'></div>";
        }
    }
    echo "</div>";
    echo "<div style='clear: both;'></div>";
}  else {
    $list = array();
    $list['ids'] = $db->getarray('id');
//    $list['quant'] = filter_input(INPUT_POST, 'quant');
    $_SESSION['list'] = serialize($list);
}
?>
    </body>
</html>