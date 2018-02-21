<?php 
$dir = "../";
$title_here = "Список цен номенклатуры";
include("header.html");
?>
<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">
            $(document).ready(function () {

                //Prepare jTable
                $('#ItemTableContainer').jtable({
                        title: 'Цены номенклатуры',
                        sorting: true,
                        defaultSorting: 'Date ASC',
                        actions: {
                                listAction:   '../actions.php?action=list&table=ZzPrice',
                                createAction: '../actions.php?action=create&table=ZzPrice&fields=Date,ItemId,Value',
                                updateAction: '../actions.php?action=update&table=ZzPrice&fields=Date,ItemId,Value'
//                                deleteAction: '../actions.php?action=delete&table=ZzPrice'
                        },
                        fields: {
                            Id: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            Date: {
                                title: 'Дата',
                                width: '15%',
                                type: 'date',
                                displayFormat: 'yy-mm-dd'
                            },
                            ItemId: {
                                title: 'Номенклатура',
                                width: '40%',
//                                    options: { 1: 'Шоколад', 2: 'Казинаки' },
                                options: '../options.php?table=ZzItem'
                            },
                            Value: {
                                title: 'Цена',
                                width: '10%'
                            }
                        }
                });

                //Load person list from server
                $('#ItemTableContainer').jtable('load');

            });
            $('tr').dblclick(function() {
                if ($('#stop').text() == 'stop'){
                    $('#stop').text('');
                    return false;
                } else {
                    $('#stop').text('stop');
                }
//                alert( "Handler for .dblclick() called. " + $('#stop').text() );
                window.open("http://www.softmaker.kz", '_blank');
                });
    </script>
    <div id="stop" style="display: none;"></div> 
<?php require_once ("footer.html");?>