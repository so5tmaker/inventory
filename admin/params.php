<?php 
$dir = "../";
$title_here = "Список параметров";
include("header.html");
?>
<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">

            $(document).ready(function () {

                //Prepare jTable
                $('#ItemTableContainer').jtable({
                        title: 'Параметры',
                        sorting: true,
                        defaultSorting: 'Name ASC',
                        actions: {
                                listAction:   '../actions.php?action=list&table=ZzParam',
                                createAction: '../actions.php?action=create&table=ZzParam&fields=Name,Prop,Date,Value',
                                updateAction: '../actions.php?action=update&table=ZzParam&fields=Name,Date,Value'
//                                deleteAction: '../actions.php?action=delete&table=ZzParam'
                        },
                        fields: {
                            Id: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            Name: {
                                    title: 'Наименование',
                                    width: '30%'
                            },
                            Prop: {
                                    title: 'Свойство',
                                    edit: false,
                                    width: '30%'
                            },
                            Date: {
                                title: 'Дата',
                                width: '20%',
                                type: 'date'
                            },
                            Value: {
                                    title: 'Значение',
                                    width: '20%'
                            }
                    }
                });

                //Load person list from server
                $('#ItemTableContainer').jtable('load');

            });

    </script>
<?php require_once ("footer.html");?>

