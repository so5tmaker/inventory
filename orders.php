<?php session_start();

include "classes/db.php";

try {

    $db = new db();
    
    if ($db->alien) {
        $title_here = "Это чужой компьютер, работа невозможна!";
        require_once ("blocks/header.php");
    
        if (empty($prcs)) {
            echo "<div class='error-message'>К сожалению, с программой могут работать только складские компьютеры! Нажмите клавишу «Ввод» для возврата на предыдущую страницу</div>".$_SERVER[REMOTE_ADDR];
        }
    } else {

        $number = filter_input(INPUT_POST, 'VolunteerNumber');

        $number7 = substr($number,0,7); 

        $siteid = $db->siteid;
        date_default_timezone_set('Asia/Almaty');
        $begin = $db->getdate(0, 0); // Monday, 00:00:00 начало недели
        $end   = $db->getdate(6, 3); // Sunday, 23:59:59 конец недели
//        $begin = date("d.m.Y G:i:s", strtotime(date('Y-m-1'))); // начало месяца
//        $end   = date("d.m.Y 23:59:59", strtotime(date('Y-m-t'))); // конец месяца
//        $db->setcurdate(strtotime(date("22.m.Y G:i:s")));
        // Устанавливаю интервал с Пятницы, 12:00:00 по Воскресенье, 23:59:59  
//        $Friday = $db->getdate(4, 2); // Friday, 12:00:00
//        $Sunday = $db->getdate(6, 3); // Sunday, 23:59:59
//        if ($siteid == 4){ // меняем интервал дат для склада на кухне
//            // Передаю в запрос по умолчанию интервал с Понедельника, 00:00:00 по Пятницу, 11:59:59 
//            $begin = $db->getdate(0, 0); // Monday, 00:00:00
//            $end   = $db->getdate(4, 1); // Friday, 11:59:59
//            // Если текущая дата входит в интервал с Пятницы, 12:00:00 по 
//            // Воскресенье, 23:59:59, то передаю этот интервал в запрос.    
//            if ($db->in_period($Friday, $Sunday)){
//                $begin = $Friday;
//                $end   = $Sunday;
//            }
//        }
        $siteid = ($db->local()) ? 4 : $siteid;
        
        $depid = 0;
        if(stristr($number, db::DEPPREFIX) !== false){
            $id = substr($number,6,2);
            $q = $db->query("SELECT DepartmentName, $number7 AS PersonId, 0 AS SpousePersonId FROM Admin2000.dbo.VoDepartment WHERE DepartmentId = ?",$id);
            $depid = $number;
        } else {
            $q = $db->query("SELECT PersonId, LastName, FirstName, MiddleName, SpousePersonId FROM Admin2000.dbo.SyPerson WHERE VolunteerNumber = ?",$number7);
        }

        $vols = $q->single();

        if (empty($vols)) {
            $title_here = "Никого не найдено!";
            require_once ("blocks/header.php");

            $q = $db->getitemprice($number);
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            if(is_object($q)){
                $prcs = $q->single();
            } else {
                $prcs = null;
            }
            if (empty($prcs)) {
                echo "<div class='error-message'>К сожалению, ничего не удалось найти по этому номеру! Нажмите клавишу «Ввод» для возврата на предыдущую страницу</div>";
            }
            echo "<div class='success'>$prcs[Name] - $prcs[Value]</div>";
            echo "<div class='success'>Нажмите клавишу «Ввод» для возврата на предыдущую страницу</div>";
        } else {
            $prsnid = $vols[PersonId];
            $q = $db->select('Id', "ZzOrder", "PersonId='$prsnid' AND SiteId='$siteid' AND Date BETWEEN Convert(datetime,'$begin',103) AND Convert(datetime,'$end',103)");
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            $ids = $q->all();
            if (empty($ids)) {
                // добавляем новый заказ, если не нашли...
                $q = $db->add_new_order($siteid, $prsnid);
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
                $ids = $q->all();
            }
            foreach ($ids as $column => $value) {
                $ords .= ($ords == "") ? "" : ", ";
                $ords .= "$value[Id]";
            }
            if($depid == 0){
                $fio = $vols[LastName]." ".$vols[FirstName]." ".$vols[MiddleName];
            } else {
                $fio = $vols[DepartmentName];
            }
            
            $list = array();
            $list['siteid'] = $siteid;
            $list['ords'] = $ords;
            $list['begin'] = $begin;
            $list['end'] = $end;
            $list['prsnid'] = $prsnid;
            $list['fio'] = $fio;
            $list['spouseid'] = $vols[SpousePersonId];
            $list['depid'] = $depid;
            $_SESSION['list'] = serialize($list);
    //        header("Location: orderdetails.php?ords=$ords&begin=$begin&end=$end&prsnid=$prsnid&siteid=$siteid&fio=$fio&spouseid=$vols[SpousePersonId]");
            header("Location: orderdetails.php");
        }
    } // alien
}
catch(Exception $ex)
{
    echo $ex->getMessage();
}

require_once ("blocks/footer.html");
?>

<script>
$(document).ready(function () {
    var siteid  = <?php echo "'$siteid'"?>;
    if (siteid === '1'){
//        $('body').css('-moz-transform', 'rotate(90deg)');
    }
    
});

$(document).keypress(function(e) {
    if(e.keyCode === 13) {
        location.replace("index.php");
    }
  });
</script>