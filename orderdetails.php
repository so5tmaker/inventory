<?php session_start();
$title_here = "Страница ввода заказа";
require_once ("blocks/jheader.php");

$list = unserialize($_SESSION['list']);
if (empty($list)) {
    header("Location: index.php");    
}
$ords     = $list['ords'];
$fio      = $list['fio'];
$prsnid   = $list['prsnid'];
$spouseid = $list['spouseid'];
$depid    = $list['depid'];
$siteid   = $list['siteid'];
$begin    = $list['begin'];
$end      = $list['end'];

$limit = 0;
$weekend = "";
if ($siteid == 4){
    try
    {
        include "classes/db.php";
        //Open database connection
        $db = new db();
        $qnt = 1;
//        $db->setcurdate(strtotime(date("22.m.Y G:i:s")));
        if ($depid ==0){
            if ($db->in_period()){
                if (!empty($prsnid)){
                    // проверяю являтся ли супруг или супруга вефильцем,
                    // если да, то сумму увеличиваю в два раза 7937199
                    $sql_spouse = "SELECT dbo.ZzGetHolidaySum($prsnid) AS HolidaySum";
    //                $sql_spouse = "SELECT DISTINCT
    //                  SyPersonEnrollment.EnrollmentId,
    //                  SyPersonEnrollment.PersonId
    //                FROM dbo.SyPersonEnrollment
    //                WHERE 
    //                SyPersonEnrollment.EnrollmentId IN (57, 67, 20, 2, 3, 31, 17, 18)
    //                AND SyPersonEnrollment.PersonId = $spouseid";
                    $q = $db->query($sql_spouse);
                    if ($q->error <>'') {
                        throw new Exception($q->error);
                    }
                    $rows = $q->single();
                    if(!empty($rows)){
                       $limit = $rows[HolidaySum];
                    }
                }
    //            $limit = $db->getparambyprop('СуммаНаВыходные')*$qnt;
                $weekend = "<em>(ваша сумма - $limit)</em>";
            }
        }
    }
    catch(Exception $ex)
    {
        echo "Ошибка выполнения запроса: ".$ex->getMessage();
    } 
}  
?> 

<br>
<div class="jtitle"><?php echo $fio;?></div>

<div class="adding" >
    <strong>Количество, шт/Масса, кг</strong>
    <input type="text" name="qnt" maxlength="6" id="qnt" class="inputbar" size="6" 
        onkeyup="return proverka(this);" onchange="return proverka(this);"
        value='00.000' 
        onfocus="if (this.value == '00.000') {this.value = '';}" 
        onblur="if (this.value == '') {this.value = '00.000';}"
    />
</div>
<div class="adding" >
    <strong>Штрихкод</strong>
    <input type="text" name="barcode" id="barcode" class="inputbar" />
</div>
<div id="TmpItemTableContainer" class="tablecontainer"></div>  
<script type="text/javascript">
    var barcode = $("#barcode");
    var ords    = <?php echo "'$ords'"?>;
    var prsnid  = <?php echo "'$prsnid'"?>;
    var siteid  = <?php echo "'$siteid'"?>;
    var begin   = <?php echo "'$begin'"?>;
    var end     = <?php echo "'$end'"?>;
    var spouseid= <?php echo "'$spouseid'"?>;
    var qnt     = $("#qnt");
    var tbl     = '#ItemTableContainer';
    var tmptbl  = '#TmpItemTableContainer';
    var limit   = <?php echo $limit?>;
    var currId  = 0;
    var Total = 0;
    var limitstr = '';
    $(document).ready(function () {

        //Prepare jTable
        $(tmptbl).jtable({
                title: 'Предварительный заказ <?php echo $weekend?>',
                sorting: true,
                defaultSorting: 'ItemId ASC',
                actions: {
//                            listAction:   'actions.php?action=list&table=ZzOrderDetail<?php echo "&paramid=OrderId&paramyes=1"?>',
//                            createAction: 'actions.php?action=create&table=ZzOrderDetail&fields=ItemId,Quantity,Price,Sum',
//                            updateAction: 'actions.php?action=update&table=ZzOrderDetail&fields=ItemId,Quantity,Price,Sum,OrderId',
//                            deleteAction: 'actions.php?action=delete&table=ZzOrderDetail'
                },
                fields: {
                    Id: {
                            key: true,
                            create: false,
                            edit: false,
                            list: false
                    },
                    ItemId: {
                        title: 'Товар',
                        width: '40%',
                        options: 'options.php?table=ZzItem'
                    },
                    Quantity: {
                        title: 'Количество',
                        width: '10%'
                    },
                    Price: {
                        title: 'Цена',
                        width: '10%'
                    },
                    Sum: {
                        title: 'Сумма',
                        width: '10%'
                    },
                    OrderId: {
                        create: false,
                        edit: false,
                        list: false
                    }
            }
        });
    
        var personlist = "ord.PersonId IN (" + prsnid + ((spouseid === '') ? '' : ", " + spouseid) + ")";
        
        var sql = "SELECT 1 as Id, Zz.ItemId, Zz.Quantity As Quantity, Zz.Price, Zz.Sum, Zz.OrderId, ISNULL(Zz.Date, ord.Date) AS Date FROM (\n\
        SELECT * FROM dbo.ZzOrderDetail) Zz \n\
        INNER join (SELECT * FROM ZzOrder ord WHERE " + personlist + ") ord on (ord.Id = Zz.OrderId)\n\
        WHERE ord.Date BETWEEN Convert(datetime,'"+begin+"',103) AND Convert(datetime,'"+end+"',103) AND ord.SiteId = " + siteid;
        
        //Prepare jTable
        $('#ItemTableContainer').jtable({
            title: 'Приобретено товаров',
            sorting: true,
            defaultSorting: 'ItemId ASC',
            actions: {
                    listAction:   'actions.php?action=list&table=ZzOrderDetail<?php echo "&fields=ItemId,Quantity,Price,Sum,OrderId,Date&spouseid=$spouseid&params=$ords&paramid=OrderId&paramyes=1&siteid=$siteid"?>&sql='+sql,
//                            createAction: 'actions.php?action=create&table=ZzOrderDetail&fields=ItemId,Quantity,Price,Sum,OrderId',
//                            updateAction: 'actions.php?action=update&table=ZzOrderDetail&fields=ItemId,Quantity,Price,Sum,OrderId',
//                            deleteAction: 'actions.php?action=delete&table=ZzOrderDetail'
            },
            fields: {
                Id: {
                        key: true,
                        create: false,
                        edit: false,
                        list: false
                },
                ItemId: {
                    title: 'Товар',
                    width: '40%',
                    options: 'options.php?table=ZzItem'
                },
                Quantity: {
                    title: 'Кол-во',
                    width: '10%'
                },
                Price: {
                    title: 'Цена',
                    width: '10%'
                },
                Sum: {
                    title: 'Сумма',
                    width: '10%'
                },
                OrderId: {
                    create: false,
                    edit: false,
                    list: false
                },
                Date: {
                    title: 'Дата',
                    width: '20%',
                    type: 'datetime',
                    displayFormat: 'yy-mm-dd G:i:s'
                } 
            },
            // events
            recordsLoaded: function(event, data) {
                
//                var limit = data.serverResponse.limit;
                Total = 0;
                for(var i=0; i<data.records.length; i++) {
                  Total = Number((Total + Number(data.records[i].Sum)).toFixed(2));
                }
                
                var exc = (Number(limit) - Total).toFixed(2);
                if (exc<0 && Number(limit) !=0){
                    showmess('Превышение суммы: ' + (-exc), 'red', false);
                }
                if($("#tfoot").length === 0) {
                    $('#ItemTableContainer table.jtable').append('<tfoot id="tfoot"></tfoot>');
                }
                if (Number(limit) != 0){
                    limitstr = '<tr><td align=right colspan="3"><strong>Ваша сумма:</strong></td><td id="flimit">&nbsp;<strong>'+limit+'</strong></td><td ></td><td ></td></tr>';
                }
                $('#tfoot').html('<tr><td > </td><td align=right colspan="2"><strong>Итого:</strong></td><td id="ftotal">&nbsp;<strong>'+Total+'</strong></td><td ></td></tr>'+limitstr);
            }    
        });
 
        //Load person list from server
        $('#ItemTableContainer').jtable('load');
        
        var $barcode = $('#barcode');
        $barcode.focus();
        $('#limit').html(limit);
        // работа со счетчиком обратного отчета
         startCountdown();
         
         if (siteid === '1'){ // переворот экрана
//            $('body').css('-moz-transform', 'rotate(90deg)');
//            changeview();
        }
    });
    
    function changeview(){
        var width = '70%';
        var margin= '15px 10%';
//        $('body').css({'width': width});
        $('div.tablecontainer').css({'width': width, 'margin': margin});
        $('div.jtitle').css({'width': width, 'margin': margin});
        $('div.adding').css({'width': width, 'margin': margin});
        $('#expireDiv').css({'width': width, 'margin': margin});
        $('table.help').css({'width': width, 'margin': margin});
        $('#message').css({'width': width, 'margin': margin});
        
        OffScroll ();  //Запустили отмену прокрутки
    }
    
    function OffScroll () {
    var winScrollTop = $(window).scrollTop();
    $(window).bind('scroll',function () {
      $(window).scrollTop(winScrollTop);
    });}

    function setsumfoot(sum){
        $('#tfoot_tmp').html('<tr><td > </td><td > </td><td></td><td id="total_tmp">&nbsp;<strong>'+sum.toFixed(2)+'</strong></td></tr>');
    }     
         
    function settotal(id){
        if($('#tfoot_tmp').length === 0) {
            $('#TmpItemTableContainer table.jtable').append('<tfoot id="tfoot_tmp"></tfoot>');
        }
        var total_tmp = get_tbl_sum(id);
        var total     = Number($('#ftotal').text());
        var limit     = Number($('#limit').text());
        var Total     = total_tmp + total;
        var exc = (limit - Total).toFixed(2);
        if (exc<0 && limit !=0 ){
            showmess('Превышение суммы: ' + (-exc), 'red', false);
        }
        if (exc>0 && limit !=0 ){
            showmess('Остаток суммы: ' + exc, 'green', false);
        }
        setsumfoot(total_tmp);
    }
            
    barcode.keypress(function(e) {
        if(e.keyCode === 13) {
            var value = barcode.val();
            if (value === ''){
                return false;
            }
            var options = getitem(value)[0];
            if (typeof(options) == "undefined"){
                barcode.val('').focus();
                showmess('К сожалению, товар со штрихкодом "' + value + '" не найден!', 'red', true);
//                return false;
//                var chosenprice = prompt('К сожалению, товар со штрихкодом "' + value + '" не найден, если вы хотите взять этот товар, \n\то введите цену и нажмите кнопку "OK". Если не хотите, то оставьте поле пустым нажмите кнопку "OK".');
//                if ( chosenprice === null || chosenprice === false || chosenprice === ''){
//                    return false;
//                } else {
//                    createitem(value, chosenprice);
//                }    
//                var options = getitem(value)[0];
            }
            // если поле количество на заполненено беру одну единицу
            var Quantity = (Number(qnt.val()) === 0) ? Number(options.Quantity) : Number(qnt.val());
            var add = addqnt(options, Quantity);
            currId = options.ItemId;
 
            if (add === true){
                settotal(0);
                barcode.val('').focus();
                qnt.val('00.000');
                return false;
            }
            
            currId = options.ItemId;

            $(tmptbl).jtable('addRecord', {
                clientOnly:true,
                record: {
                    Id: options.ItemId,
                    ItemId: options.ItemId,
                    Quantity: Quantity,
                    Price: options.Price,
                    Sum: Number(options.Price)*Quantity,
                    OrderId: ''
                },
                success: function() {
//                        alert('success');
                      barcode.val('').focus();
                      qnt.val('00.000');
                      settotal(0);
                      selrow();
                },
                error: function() {
                    alert('Не удалось добавить записи!');
                }
            });
        }
    }).keypress();

    function addqnt(options, Quantity){
        $(tmptbl + ' .ui-state-highlight').removeClass('ui-state-highlight');
        var $row = $(tmptbl).jtable('getRowByKey', Number(options.ItemId));
        var ret = false;
        if ($row === null){
            return false;
        }
        $row.addClass('ui-state-highlight'); /// for your css class
        $(tmptbl).jtable('selectRows', $row);
        var $selectedRows = $(tmptbl).jtable('selectedRows');
        $selectedRows.each(function () {
            var rcrd = $(this).data('record');
            rcrd.Quantity = Number(rcrd.Quantity) + Number(Quantity);
            rcrd.Sum = Number(rcrd.Price) * Number(rcrd.Quantity);
            $(tmptbl).jtable('updateRecord', {clientOnly:true, record: rcrd});
            ret = true;
        });
        return ret;
    }
    
    function get_rec_by_key($rows, key){
        var i = 0;
        var ret = false;
        jQuery.each($rows,function(){
            var id = $(this).attr('data-record-key');
            if (Number(id) === Number(key)){
                ret = true;
            }
            if (ret) {return i;}
            i = i+1;
        });
        return i;
    }
    
    function get_tbl_sum(id){
        var sum = 0;
        var $rows = $('#TmpItemTableContainer .jtable-data-row');
        jQuery.each($rows,function(){
            var rcrd = $(this).data('record');
            if (id !=0) {
                if (id != rcrd.Id) {
                    sum = sum + rcrd.Sum;
                }
            } else {
                sum = sum + rcrd.Sum;
            }    
        });
        return sum;
    }
    
    // выделение строки в таблице
    // тип стрелки: 38 - стрелка вверх и 40 - стрелка вниз 
    function sel(rowtype){
        if (rowtype === 38 || rowtype === 40){
            $(tmptbl + ' .ui-state-highlight').removeClass('jtable-row-selected');
            $(tmptbl + ' .ui-state-highlight').removeClass('jtable-row-updated');
            var $r = $('#TmpItemTableContainer .jtable-data-row');
            var curStrNum;
            if (currId === 0){
                if (rowtype === 38){ // стрелка вверх
                    // если не выделена ни одна строка, то последняя 
                    curStrNum = $r.length-1;  
                }
                if (rowtype === 40){ // стрелка вниз 
                    // если не выделена ни одна строка, то первая 
                    curStrNum = 0;
                }
            } else {
                curStrNum = get_rec_by_key($r, currId);
                if (rowtype === 38){ // стрелка вверх
                    curStrNum = curStrNum-1; 
                    if (0 > curStrNum){
                        curStrNum = $r.length-1;
                    }
                }
                if (rowtype === 40){ // стрелка вниз  
                    curStrNum = curStrNum+1;
                    if ($r.length-1 < curStrNum){
                        curStrNum = 0;
                    }
                }
            }
            currId = Number($r[curStrNum].attributes["data-record-key"].nodeValue);
            return selrow();
        }
    }
    
    function selrow(){
        $(tmptbl + ' .ui-state-highlight').removeClass('ui-state-highlight');
        var $row = $(tmptbl).jtable('getRowByKey', Number(currId));
        if ($row === null){
            return false;
        }
        $row.addClass('ui-state-highlight'); /// for your css class
        return true;
    }
    
    function createitem(barcode, price) {       
        var options = [];
        $.ajax({ //Not found in cache, get from server
            url: 'createitem.php',
            type: 'POST',
            dataType: 'json',
            async: false,
            processData: false,
            data: "barcode="+barcode+"&price="+price,
            success: function (data) {
                options = data.Options;
                return;
            },
            error: function(data) {
                showmess('Ошибка:'+data.Message, 'red', false);
            }
        });
        return  options;
    }

    function getitem(barcode) {       
        var options = [];
        $.ajax({ //Not found in cache, get from server
            url: 'getitemsbycode.php',
            type: 'POST',
            dataType: 'json',
            async: false,
            processData: false,
//                    data: {data:barcode},
            data: "barcode="+barcode,
            success: function (data) {
                if (data.Result != 'OK') {
                    alert(data.Message);
                    return;
                }
                options = data.Options;
            } 
        });
        return  options;
    }

    function getorderid(rcrd) {       
        var options = [];
        $.ajax({ //Not found in cache, get from server +"&begin="+begin+"&end="+end
            url: 'getorderid.php',
            type: 'POST',
            dataType: 'json',
            async: false,
            processData: false,
            data: "ords="+ords+"&siteid="+siteid+"&prsnid="+prsnid+
                    "&ItemId="+rcrd.ItemId+"&Quantity="+rcrd.Quantity+
                    "&Price="+rcrd.Price+"&Sum="+rcrd.Sum+"&begin="+begin+"&end="+end,
            success: function (data) {
                options = data.Options;
                showmess('Данные успешно добавлены в основную таблицу!', 'green', true);
            },
            error: function(data) {
                showmess('Ошибка:'+data.responseText, 'red', false);
            }
        });
        return  options;
    }
    
    function delonerecord() {
        var $row = $(tmptbl).jtable('getRowByKey', Number(currId));
        if ($row === null){
            return false;
        }
        var id = $row.attr('data-record-key');
        $(tmptbl).jtable('deleteRecord', {
            key: id,
            clientOnly: true
        });    
        sel(38);
        showmess('Запись успешно удалена из промежуточной таблицы!', 'green', true);
        settotal(id);
    }
    
    function delallrecords(table, mode) {
        var $rows = $(table).find('.jtable-data-row');
        $.each($rows,function(){
            var id = $(this).attr('data-record-key');
            $(table).jtable('deleteRecord', {
                key: id,
                clientOnly: true
            });
        });   
        setsumfoot(0);
        if (mode) {
            showmess('Данные успешно удалены из промежуточной таблицы!', 'green', true);
        }
    }

    function addallrecords(table) {
        var $rows = $(table).find('.jtable-data-row');
        $.each($rows,function(){
            var rcrd = $(this).data('record');
            getorderid(rcrd);
            $('#ItemTableContainer').jtable('reload');
        });
        delallrecords(table, false);    
    }

    $(window).keyup(function(e) {
        stopCountdown();
        sel(e.keyCode); // выделение строки в таблице
        if(e.keyCode === 13) {
            barcode.val('').focus();
        }
        if(e.keyCode === 36) { // клавиша NumPad Home
            location.replace("index.php");
        }
        if(e.keyCode === 46) { // клавиша Delete
            delonerecord();
        }
        if(e.keyCode === 106) { // клавиша NumPad * - amount
            barcode.val('');
            qnt.focus();
        }
        if(e.keyCode === 107) { // клавиша "+" - add
            addallrecords(tmptbl);
            barcode.val('').focus();
        }
        if(e.keyCode === 109) { // клавиша "-" - subtract           
            delallrecords(tmptbl, true);
            barcode.val('').focus();
        }
        if(e.keyCode === 45) { // клавиша NumPad "Ins" - help
            alert('\
            «Ins» - описание назначения клавиш\n\
            «*» - переход на поле ввода количества\n\
            «Ввод» - переход на поле ввода штрихкода\n\
            «Стрелка вверх» - перемещение вверх по таблице "Предварительный заказ"\n\
            «Стрелка вниз» - перемещение вниз по таблице "Предварительный заказ"\n\
            «Del» - удаление текущей записи таблицы "Предварительный заказ\n\
            «–» - удаление данных из таблицы "Предварительный заказ"\n\
            «+» - добавление данных в таблицу "Приобретено товаров"\n\
            «Home» - переход на страницу ввода номера добровольца\n\"');
        }
    }).keyup();
    
    function startCountdown(){
        var startFrom = 60;
        $('#expireDiv span').text(startFrom).parent('div').show();
        timer = setInterval(function(){
            $('#expireDiv span').text(--startFrom);
            if(startFrom <= 0) {
                clearInterval(timer);
                $('#expireDiv').text('Ваш сеанс истёк!');
                location.replace("index.php");
            }
        },1000);
    }
    function stopCountdown(){
        clearInterval(timer);
        $('#expireDiv span').text('');
        startCountdown();
    }
             
</script>

<div id="message" class="msgcontainer"></div>

<div id="ItemTableContainer" class="tablecontainer"></div> 
<div id="limit" style="display: none;"></div>  
<div id="limit_show" class="show"></div>  
<br>
<div id="expireDiv">
    Ваш сеанс истечет через <span></span> секунд
</div>
<br>
<div id="helpDivTitle">
    Описание клавиш для работы со складом:
</div>

<table class="help" border="0" >
        <colgroup>
        <col width="25%">
        <col width="75%">
        </colgroup>
        <tbody> 
            <tr>
                <td>«Ins»</td>
                <td> - описание назначения клавиш</td>
            </tr>
            <tr>
                <td>«*»</td>
                <td>- переход на поле ввода количества</td>
            </tr>
            <tr>
                <td>«Ввод»</td>
                <td> - переход на поле ввода штрихкода</td>
            </tr>
            <tr>
                <td>«Стрелка вверх»</td>
                <td>- перемещение вверх по таблице "Предварительный заказ"</td>
            </tr>
            <tr>
                <td>«Стрелка вниз»</td>
                <td>- перемещение вниз по таблице "Предварительный заказ"</td>
            </tr>
            <tr>
                <td>«Del»</td>
                <td>- удаление текущей записи таблицы "Предварительный заказ"</td>
            </tr>
            <tr>
                <td>«–»</td>
                <td>- удаление данных из таблицы "Предварительный заказ"</td>
            </tr>
            <tr>
                <td>«+»</td>
                <td>- добавление данных в таблицу "Приобретено товаров"</td>
            </tr>
            <tr>
                <td>«Home»</td>
                <td>- переход на страницу ввода номера добровольца</td>
            </tr>
        </tbody>
    </table>

<?php require_once ("blocks/footer.html");?>
