<?php 
$dir = "../";
$title_here = "Список заказов";
include("header.html");
?>
<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">
            $(document).ready(function () {

                //Prepare jTable
                $('#ItemTableContainer').jtable({
                        title: 'Заказы',
                        sorting: true,
                        defaultSorting: 'PersonId ASC',
                        actions: {
                                listAction:   '../actions.php?action=list&table=ZzOrder&fields=Id,SiteId,PersonId,Date',
                                createAction: '../actions.php?action=create&table=ZzOrder&fields=SiteId,PersonId,Date',
                                updateAction: '../actions.php?action=update&table=ZzOrder&fields=SiteId,PersonId,Date',
                                deleteAction: '../actions.php?action=delete&table=ZzOrder'
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
                                type: 'datetime',
                                displayFormat: 'yy-mm-dd G:i:s'
                            },
                            PersonId: {
                                title: 'Вефилец',
                                width: '30%',
//                                    options: { 1: 'Шоколад', 2: 'Казинаки' },
                                options: '../options.php?table=SyPerson&fields=PersonId,LastName,FirstName,MiddleName&orderby=LastName'
                            },
                            SiteId: {
                                title: 'Склад',
                                width: '40%',
                                options: '../options.php?table=ZzSite'
                            }
                    }
                });

                //Load person list from server
                $('#ItemTableContainer').jtable('load');

            });

    </script>
<?php require_once ("footer.html");?>