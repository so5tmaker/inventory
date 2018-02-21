<?php 
$dir = "../";
$title_here = "Список номенклатуры";
include("header.html");
$dir = "../";
?>
<div id="helpDivTitle">
    В названии продуктов не должно быть символов "," и ";" !
</div>
<!-- Upload Button-->
<div id="upload" class="adding">Загрузить файл</div>
<div id="message" class="msgcontainer"></div>
<!--List Files-->
<ul id="files" ></ul>

<div style="display: none;" id="dialog" title="Выберите папку">
    <select id="cParent" name="cParent" class="inputbar">
        
    </select>
    <div style='text-align: center; margin-top:15px'>
        <button id="сButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text">Выбрать</span>
        </button>
    </div>
</div>

<div class="adding" >
    Поле поиска (выберите по какому полю нужно искать и нажмите клавишу «Ввод»):
    <select id="record" name="record" class="inputbar">
        <option value="Name">Наименование</option>
        <option value="BarCode" selected="selected">Штрихкод</option>
    </select>
    <input onclick="this.select()" type="text" name="barcode" id="barcode" class="inputbar" />
    <input style="display: none;" type="text" name="Parent" id="Parent"/>
    <input style="display: none;" type="text" name="Level" id="Level"/>
    <!--<button type="submit" id="LoadRecordsButton">Load records</button>float: left;  width: 25%;-->
</div>
<div class="printbarcode">
    <div class="floatleftbtn">
        <button id="PrintPrice" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text">Распечатать ценник</span>
        </button>
    </div>
    <div class="floatleftbtn">
        <button id="PrintAllButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text">Распечатать штрихкоды</span>
        </button>
    </div>
    <div class="floatleftcap"><div class="divcap">Количество:</div></div>
    <div class="floatleft"><input maxlength="3" type="text" name="quant" id="quant" class="inputamount" /> </div>
</div>
<div style="clear: both;"></div>

<div class="adding" >
    <button id="MyButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Добавить</span>
    </button>
</div>

<div class="adding" >
    <button id="Transfer" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Перенести в группу</span>
    </button>
</div>

<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">
            var barcode = $("#barcode");
            var record = $("#record");
            var paramid;
            var sql;
            $(document).ready(function () {
                sql = "SELECT zi1.Id, zi1.Folder, zi1.Parent, zi1.Parent as ParentId, zi1.Level,  zi1.BarCode, zi1.Name, zi1.Date, ISNULL(zp1.Value,0) AS Value, ISNULL(zp1.Id, 0) AS priceid,  999 AS row FROM ( \n\
                            SELECT * From ( SELECT * FROM ZzItem z  WHERE 5=5) zi \n\
                            LEFT join ( \n\
                                SELECT ItemId, MAX(Date) as PDate FROM  ZzPrice GROUP BY  ItemId \n\
                            ) zp \n\
                            ON (zi.Id = zp.ItemId) \n\
                            ) zi1 LEFT JOIN ( \n\
      				SELECT ItemId, Date, Value, Id From ZzPrice \n\
                            ) zp1 \n\
                        ON (zi1.Id = zp1.ItemId AND zi1.PDate = zp1.Date) 6=6";
                //Prepare jTable
                $('#ItemTableContainer').jtable({
                        title: 'Номенклатура',
                        sorting: true,
                        defaultSorting: 'Folder DESC',
                        selecting: true, //Enable selecting
                        multiselect: true, //Allow multiple selecting
                        selectingCheckboxes: true, //Show checkboxes on first column
//                        paging: true, //Enable paging
                        pageList: 'normal',
                        gotoPageArea: 'combobox',
                        pageSizes: [10, 20, 50, 100],
                        pageSizeChangeArea: true,
                        pageSize: 100, //Set page size (default: 10)
                        selectOnRowClick: false, //Enable this to only select using checkboxes
                        actions: {
                                listAction:   '../actions.php?action=list&table=ZzItem&paramyes=1&sql='+sql,
                                createAction: 'createitemprice.php?action=create',
                                updateAction: 'createitemprice.php?action=update'
//                                deleteAction: '../actions.php?action=delete&table=ZzItem'
                        },
                        fields: {
                            Id: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            Folder: {
                                title: '',
                                width: '2%',
//                                sorting: false,
//                                edit: false,
//                                create: false,
                                display: function (ItemData) {
                                    var $img;
                                    if(ItemData.record.Folder === '1'){
                                        $img = $('<img style="cursor: pointer;" src="../img/folder.png" title="Это папка" />');
                                        $img.click(function () {
                                            var Id = ($("#Parent").val() === '') ? ItemData.record.Id : '';
                                            var Level = ItemData.record.Level;
                                            var ParentId = ItemData.record.ParentId;
                                            $("#Parent").val(Id);
                                            $("#Level").val(Level);
                                            $('#ItemTableContainer').jtable('load', {
                                                FolderID: Id,
                                                ParentId: ParentId,
                                                Level: Level
                                            });
                                        });
                                    } else {
                                        $img = $('<img src="../img/Element.png" title="Это элемент" />');
                                    }   
                                    //Return image to show on the person row
                                    return $img;
                                },
                                options: { 0: 'Элемент', 1: 'Папка' }
                                
                            },
                            priceid: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            Parent: {
                                title: 'Выберите группу',
                                options: '../options.php?table=ZzItem&Folder=1',
                                list: false
                            },
                            Name: {
                                    title: 'Наименование',
                                    width: '45%'
                            },
                            BarCode: {
                                    title: 'Штрихкод',
                                    width: '28%'
                            },
                            Value: {
                                title: 'Цена',
                                width: '10%'
                            },
                            Date: {
                                title: 'Изменен',
                                width: '15%',
                                create: false,
                                edit: false,
                            }
                    },
                    // events
                    formCreated:  function(event,data){
                        if (data.formType === 'create'){
//                            $('#Edit-Folder').prop('multiple',true);
                            if (record.val() === 'Name') {
                                $('#Edit-Name').val(barcode.val()); 
                            } else {
                                $('#Edit-BarCode').val(barcode.val());
                            }
                            $('#AddRecordDialogSaveButton .ui-button-text').text('Добавить (Ctrl + Enter)');   
                        }
                        if (data.formType === 'edit'){
                            $('#EditDialogSaveButton .ui-button-text').text('Изменить (Ctrl + Enter)');
//                            $('#Edit-Folder').attr('disabled', true);
                            if (data.record.Folder === '1'){
                                $('#Edit-Value').attr('disabled', true);
                                $('#Edit-BarCode').attr('disabled', true);
                                $('#Edit-Parent').attr('disabled', true);
//                                $('#Edit-Folder').attr('disabled', true);
                            } else {
                                $('#Edit-Folder').attr('disabled', true);
                            }
                        } 
                        $('#Edit-Folder').change(function() {
                            var value = $('#Edit-Folder').val();
                            if (value === '1') {
                                $('#Edit-Value').attr('disabled', true).val(0);
                                $('#Edit-BarCode').attr('disabled', true);
                                $('#Edit-Parent').attr('disabled', true).val(-1);
                            } else {
                                $('#Edit-Value').attr('disabled', false);
                                $('#Edit-BarCode').attr('disabled', false);
                                $('#Edit-Parent').attr('disabled', false);
                            }
                        }).change();
                        $('#Edit-Name').width('400px');
                        $('#Edit-Value').css({
                            'text-align': 'Right'
                        });
                    },
                    formClosed:  function(event,data){

                    },
                    formSubmitting: function (event, data) {

                    },
                    rowInserted: function (event, data) {
//                        $('#ItemTableContainer').jtable('load');
                    },
                    addRecordButton: $('#MyButton'),
                    //Register to selectionChanged event to hanlde events
                    selectionChanged: function () {
                        //Get all selected rows
                        var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');

                        $('#message').empty();
                        $('#back-top').html('<a href="#top">^Наверх^</a>');
                        if ($selectedRows.length > 0) {
                            //Show selected rows
                            $selectedRows.each(function () {
                                $('#message').html(
                                    'Выделено строк: ' + $selectedRows.length
                                );
                                $('#back-top').html(
                                        '<a href="#top">^Наверх^</a><br>Выделено строк: ' + $selectedRows.length
                                );
                            });
                        } 
                    }
                });

                //Load person list from server
                $('#ItemTableContainer').jtable('load');
                var $upload = $('#upload');
                $upload.focus();
                var $barcode = $('#barcode');
                $barcode.focus();
            });
            
            //Print selected items
            $('#PrintAllButton').button().click(function () {
                var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');
                var Id = "", glue = "";
                $selectedRows.each(function () {
                    var rcrd = $(this).data('record');
                    glue = (Id === '') ? '' : ",";
                    Id+= glue + rcrd.Id;
                });
                
                if (Id !== ""){
                    $.ajax({ //Not found in cache, get from server
                        url: 'barcode/barcode.php',
                        type: 'POST',
                        dataType: 'json',
                        async: false,
                        processData: false,
                        data: "id="+Id+'&quant='+$('#quant').val() 
                    });
                    window.open("barcode/barcode.php?print", '_blank');
                }
            });
            
            $('#PrintPrice').button().click(function () {
                var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');
                var glue = "", Id = '';
                $selectedRows.each(function () {
                    var rcrd = $(this).data('record');
                    glue = (Id === '') ? '' : ",";
                    Id+= glue + rcrd.Id;
                });
                if (Id !== ""){
                    $.ajax({ //Not found in cache, get from server
                        url: 'barcode/barcode.php',
                        type: 'POST',
                        dataType: 'json',
                        async: false,
                        processData: false,
                        data: "id="+Id 
                    });
                    window.open("barcode/barcode.php?print", '_blank');
                }
            });
            
            // Меняю родителей у элементов
            $('#сButton').button().click(function () {
                var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');
                var glue = "", Id = $('#cParent').val(), selopt = $('#cParent option:selected').text();
                $selectedRows.each(function () {
                    var rcrd = $(this).data('record');
                    if (rcrd.Folder !== '1'){
                        glue = (Id === '') ? '' : ",";
                        Id+= glue + rcrd.Id;
                    }
                });
                if (Id !== ""){
                    $.ajax({ //Not found in cache, get from server
                        url: 'change_parent.php?table=ZzItem',
                        type: 'POST',
                        dataType: 'json',
                        async: false,
                        processData: false,
                        data: "id="+Id,
                        success: function (data) {
                            $("#dialog").dialog("close");
                            if (data.Result === 'ERROR') {
                                showmess('Ошибка:'+data.Message, 'red', true);
                                return;
                            } else {
                                showmess('Элементы успешно перенесены в папку «' + selopt + '»', 'green', true);
                                $('#ItemTableContainer').jtable('load');
                                return;
                            }
                        },
                        error: function(data) {
                            showmess('Ошибка:'+data.Message, 'red', true);
                        }
                    });
//                    window.open("barcode/barcode.php?print", '_blank');
                }
            });
            
            // Переношу элементы в группу
            $('#Transfer').button().click(function () {
                var opt = get_parent_option();
                var options;
                for(var i=0; i<opt.length; i++) {
                    var rcrd = opt[i];
                    options = options + '<option value="'+rcrd.Value+'">'+rcrd.DisplayText+'</option>';
                }
                $("#cParent").html(options);
                $(function() {
                    $( "#dialog" ).dialog();
                });
            });

            $(function(){
		var btnUpload=$('#upload');
		var status=$('#message');
		new AjaxUpload(btnUpload, {
                    action: 'upload-file.php',
                    name: 'uploadfile',
                    onSubmit: function(file, ext){
                        if (! (ext && /^(csv|csv)$/.test(ext))){ 
                            // extension is not allowed 
                            showmess('Можно загружать только файлы csv!', 'red', false);
                            return false;
                        }
                        status.text('Загрузка...');
                    },
                    onComplete: function(file, response){
                        var data = JSON.parse(response);
                        //On completion clear the status
                        status.text('');
                        //Add uploaded file to list
                        if(data.Result === "OK"){
                            if (data.Message === ""){
                                showmess('Загрузка файла успешно завершена!', 'green', false);
                            }else{
                                showmess(data.Message, 'blue', false);
                            }
                            $('#ItemTableContainer').jtable('reload');
                        } else{
                            showmess(data.Message, 'red', false);
                        }
                    }
		});
            });

            barcode.keypress(function(e) {
                if(e.keyCode === 13) {
                    var value = barcode.val();
                    if (value === ''){
                        $('#ItemTableContainer').jtable('load');
                        return false;
                    }
                    $('#ItemTableContainer').jtable('load', {
                        BarCode: barcode.val(),
                        record:  record.val()
                    });
                }
            }).keypress();
            
            $(window).keyup(function(e) {
                if(e.keyCode === 13 && e.ctrlKey) {
                    if ($('#AddRecordDialogSaveButton .ui-button-text').text() === 'Добавить (Ctrl + Enter)') {
                        $('#AddRecordDialogSaveButton').click();
                    } 
                    if ($('#EditDialogSaveButton .ui-button-text').text() === 'Изменить (Ctrl + Enter)') {
                        $('#EditDialogSaveButton').click();
                    } 
                }
            }).keyup();
            
            record.change(function() {
              barcode.focus();
            });
            
            function get_parent_option() {       
                var options = [];
                $.ajax({ //Not found in cache, get from server
                    url: '../options.php?table=ZzItem&Folder=1',
                    type: 'POST',
                    dataType: 'json',
                    async: false,
                    processData: false,
    //                data:"deep="+deep.val() ,
                    success: function (data) {
                        options = data.Options;
                    },
                    error: function(data) {
                        showmess('Ошибка:'+data.responseText, 'red', false);
                    }
                });
                return options;
            }
    </script>
    
<?php require_once ("footer.html");?>

