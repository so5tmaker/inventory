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

<div class="adding" >
    Поле поиска (выберите по какому полю нужно искать и нажмите клавишу «Ввод»):
    <select id="record" name="record" class="inputbar">
        <option value="Name">Наименование</option>
        <option value="BarCode" selected="selected">Штрихкод</option>
    </select>
    <input onclick="this.select()" type="text" name="barcode" id="barcode" class="inputbar" />
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
    <!--<input type="button" name="MyButton" id="MyButton" value="Добавить" />-->
    <button id="MyButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Добавить</span>
    </button>
</div>

<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">
            var barcode = $("#barcode");
            var record = $("#record");
            var paramid;
            var sql;
            $(document).ready(function () {
                sql = "SELECT zi1.Id, zi1.BarCode, zi1.Name, zi1.Date, ISNULL(zp1.Value,0) AS Value, ISNULL(zp1.Id, 0) AS priceid,  999 AS row FROM ( \n\
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
                        defaultSorting: 'Date ASC',
                        selecting: true, //Enable selecting
                        multiselect: true, //Allow multiple selecting
                        selectingCheckboxes: true, //Show checkboxes on first column
//                        paging: true, //Enable paging
                        pageList: 'normal',
                        gotoPageArea: 'combobox',
                        pageSizes: [10, 20, 50, 100],
                        pageSizeChangeArea: true,
                        pageSize: 100, //Set page size (default: 10)
//                        selectOnRowClick: false, //Enable this to only select using checkboxes
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
                            priceid: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            Name: {
                                    title: 'Наименование',
                                    width: '45%'
                            },
                            BarCode: {
                                    title: 'Штрихкод',
                                    width: '30%'
                            },
                            Value: {
                                title: 'Цена',
                                width: '10%'
                            },
                            Date: {
                                title: 'Изменен',
                                width: '15%'
                            }
                    },
                    // events
                    formCreated:  function(event,data){
                        if (data.formType === 'create'){
                            if (record.val() === 'Name') {
                            $('#Edit-Name').val(barcode.val()); 
                            } else {
                                $('#Edit-BarCode').val(barcode.val());
                            }
                            $('#AddRecordDialogSaveButton .ui-button-text').text('Добавить (Ctrl + Enter)');
                        }
                        if (data.formType === 'edit'){
                            $('#EditDialogSaveButton .ui-button-text').text('Изменить (Ctrl + Enter)');
                        } 
                        $('#Edit-Name').width('400px');
                        $('#Edit-Value').css({
                            'text-align': 'Right'
                        });
                    },
                    formClosed:  function(event,data){
                        if (data.formType === 'create'){
//                            $('#ItemTableContainer').jtable('load', {
//                                BarCode: $('#Edit-BarCode').val()
//                            });
                        }
                    },
                    formSubmitting: function (event, data) {
//                        if (data.record) {
//                             $('#ItemTableContainer').jtable('load', {
//                                BarCode: $('#Edit-BarCode').val()
//                            });
//                        }
                    },
                    rowInserted: function (event, data) {
                    // check the record to decide if record is selected
//                        if (data.isNewRow) {
//                            $('#ItemTableContainer').jtable('reload', {
//                                BarCode: data.record.BarCode
//                            });
//                        }
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
//                    if ($('#ui-dialog-title-2').text() === '+ Добавить') {
//                        $('#AddRecordDialogSaveButton').click();
//                    } else {
//                        $('#EditDialogSaveButton').click();
//                    }
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
    </script>
    
<?php require_once ("footer.html");?>

