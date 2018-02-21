<?php
$inputtext = "Введите номер добровольца и нажмите клавишу «Ввод»";
$action = "index.php";
$error = "";

include "classes/db.php";

$title_here = "Страница входа на склад";
require_once ("blocks/header.php");


$db = new db();
$siteid = $db->siteid;

?>
<div class="cntr"> 
    <?php // echo $error; echo "siteid: '$siteid' SERVER: $_SERVER[REMOTE_ADDR]"?>
    
    <form name="SyPerson" method="POST" action="orders.php" >
        <input class="pass-show" name="VolunteerNumber" id="VolunteerNumber" type="password" 
               value="<?php echo (empty($number)) ? '' : $number ; ?>" 
        />
        <center ><label>Введите номер добровольца и нажмите клавишу «Ввод»</label></center>
        <input type="submit" id="myButton" style="display: none;">
    </form>
</div>

<?php
require_once ("blocks/footer.html");
?>

<script>
$(document).ready(function () {
    var siteid  = <?php echo "'$siteid'"?>;
    $('#VolunteerNumber').focus();
    if (siteid === '1'){
//        $('body').css('-moz-transform', 'rotate(90deg)');
//        $('div.cntr').css('left', '70%'); 
//        $('form').css('width', '60%'); 
    }
    
});

$(window).keyup(function(e) {
    if(e.keyCode === 13) {
        $('#VolunteerNumber').focus();
    } 
//    alert(natIP());
}).keyup();

$('#text-show').click(function() {
    var $txt = $('#text-show');
    var $psw = $('#VolunteerNumber');
    $txt.val('').hide();
    $psw.show().focus();
});

$('#myButton').click(function() {
    var $psw = $('#VolunteerNumber').val();
    if ($psw === ""){
        return false;
    } 
});

$('#check').change(function() {
    var $txt = $('#text-show');
    var $psw = $('#VolunteerNumber');
    if ($txt.val() == "Введите номер добровольца и нажмите клавишу «Ввод»") {
        return;
    }
    if ($(this).is(':checked')) {
        $psw.hide();
        $txt.removeClass().addClass("inputtextact").val($psw.val()).show();
    }
    else {
        $txt.hide();
        $psw.val($txt.val()).show();
    }
});
</script>