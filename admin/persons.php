<?php 
$dir = "../";
$title_here = "Список вефильцев";
include("header.html");

?>
<!--<p>
    <label>
    <input type="checkbox" name="flag" id="flag">
    Вывести все
    </label>
</p>-->
<div class="printbarcode">
    <div class="floatleftbtn">
        <button id="PrintPrice" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text">Распечатать штрихкод</span>
        </button>
    </div>
    
</div>
<div style="clear: both;"></div>
<br>
<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">
        var sql = "";
        $(document).ready(function () {
                
        sql = "SELECT pr.PersonId AS PersonId,dbo.fnPersonName(pr.PersonId, DEFAULT) AS Name, sy.VolunteerNumber \n\
        from Admin2000.dbo.SyPersonEnrollment pr \n\
        INNER JOIN Admin2000.dbo.VoEnrollment En ON En.EnrollmentId = Pr.EnrollmentId \n\
        LEFT JOIN Admin2000.dbo.OrSupervisedGeoLocation Sg ON Sg.GeoLocationId = Pr.GeoLocationId \n\
        left join Admin2000.dbo.OrGeoLocation ge on ge.GeoLocationId = Sg.GeoLocationId \n\
        inner join Admin2000.dbo.SyPerson sy on sy.PersonId = pr.PersonId \n\
        WHERE pr.EnrollmentId IN (57, 67, 20, 2, 3, 31, 17, 18) AND En.BethelEnrollmentYesNo = 1 AND CONVERT (nvarchar, GETDATE(), 112) BETWEEN Pr.StartDate AND \n\
        ISNULL(Pr.EndDate, N'29991231') AND (Pr.GeoLocationId IS NULL OR Sg.GeoLocationId = Pr.GeoLocationId) \n\
        and sg.GeoLocationId=115 ORDER BY Name";
                
//                sql = "SELECT pr.PersonId AS PersonId,dbo.fnPersonName(pr.PersonId, DEFAULT) AS Name, sy.VolunteerNumber \n\
//        from SyPersonEnrollment pr \n\
//        INNER JOIN VoEnrollment En ON En.EnrollmentId = Pr.EnrollmentId \n\
//        LEFT JOIN OrSupervisedGeoLocation Sg ON Sg.GeoLocationId = Pr.GeoLocationId \n\
//        left join OrGeoLocation ge on ge.GeoLocationId = Sg.GeoLocationId \n\
//        inner join SyPerson sy on sy.PersonId = pr.PersonId \n\
//        WHERE En.BethelEnrollmentYesNo = 1 \n\
//        AND (Pr.GeoLocationId IS NULL OR Sg.GeoLocationId = Pr.GeoLocationId) \n\
//        and sg.GeoLocationId=115 ORDER BY Name";
                //Prepare jTable
                $('#ItemTableContainer').jtable({
                        title: <?php echo "'$title_here'"?>,
                        sorting: true,
                        defaultSorting: 'Name ASC',
                        selecting: true, //Enable selecting
                        multiselect: true, //Allow multiple selecting
                        selectingCheckboxes: true, //Show checkboxes on first column
                        actions: {
                              listAction:   '../actions.php?action=list&table=Admin2000.dbo.SyPerson&fields=PersonId,Name,VolunteerNumber&sql='+sql  
//                                listAction:   '../actions.php?action=list&table=SyPerson&fields=PersonId,dbo.fnPersonName(PersonId, 0) AS Name,VolunteerNumber'
//                                createAction: '../actions.php?action=create&table=SyPerson&fields=dbo.fnPersonName(PersonId, 0) AS Name,VolunteerNumber'
//                                updateAction: '../actions.php?action=update&table=ZzPrice&fields=Date,ItemId,Value'
//                                deleteAction: '../actions.php?action=delete&table=ZzPrice'
                        },
                        fields: {
                            PersonId: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            Name: {
                                    title: 'ФИО',
                                    width: '80%'
                            },
                            VolunteerNumber: {
                                title: 'Номер добровольца',
                                width: '20%'
                            }
                        }
                });

                //Load person list from server
                $('#ItemTableContainer').jtable('load');

            });
        
        $('#PrintPrice').button().click(function () {
                var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');
                var glue = "", Id = '';
                $selectedRows.each(function () {
                    var rcrd = $(this).data('record');
                    glue = (Id === '') ? '' : ",";
                    Id+= glue + rcrd.PersonId;
                });
                if (Id !== ""){
                    $.ajax({ //Not found in cache, get from server
                        url: 'barcode/persons-barcode.php',
                        type: 'POST',
                        dataType: 'json',
                        async: false,
                        processData: false,
                        data: "id="+Id 
                    });
                    window.open("barcode/persons-barcode.php?print", '_blank');
//                    window.open("barcode/barcode.php?BarCode="+BarCode+'&quant='+$('#quant').val()+'&Name='+Name+'&id='+Id, '_blank');
                }
            });

            
//            $('#flag').change(function() {
//                if ($("#flag").is(':checked')) {
//                    sql = "SELECT pr.PersonId AS PersonId,dbo.fnPersonName(pr.PersonId, DEFAULT) AS Name, sy.VolunteerNumber \n\
//        from SyPersonEnrollment pr \n\
//        INNER JOIN VoEnrollment En ON En.EnrollmentId = Pr.EnrollmentId \n\
//        LEFT JOIN OrSupervisedGeoLocation Sg ON Sg.GeoLocationId = Pr.GeoLocationId \n\
//        left join OrGeoLocation ge on ge.GeoLocationId = Sg.GeoLocationId \n\
//        inner join SyPerson sy on sy.PersonId = pr.PersonId \n\
//        WHERE En.BethelEnrollmentYesNo = 1 \n\
//        AND (Pr.GeoLocationId IS NULL OR Sg.GeoLocationId = Pr.GeoLocationId) \n\
//        and sg.GeoLocationId=115 ORDER BY Name";
//                $('#ItemTableContainer').jtable('load', {
//                    listAction:   '../actions.php?action=list&table=SyPerson&fields=PersonId,Name,VolunteerNumber&sql='+sql 
//                });
////                $('#ItemTableContainer').jtable('load', {sql: sql});
//                }else{
//                    sql = "SELECT pr.PersonId AS PersonId,dbo.fnPersonName(pr.PersonId, DEFAULT) AS Name, sy.VolunteerNumber \n\
//        from SyPersonEnrollment pr \n\
//        INNER JOIN VoEnrollment En ON En.EnrollmentId = Pr.EnrollmentId \n\
//        LEFT JOIN OrSupervisedGeoLocation Sg ON Sg.GeoLocationId = Pr.GeoLocationId \n\
//        left join OrGeoLocation ge on ge.GeoLocationId = Sg.GeoLocationId \n\
//        inner join SyPerson sy on sy.PersonId = pr.PersonId \n\
//        WHERE pr.EnrollmentId IN (57, 67, 20, 2, 3, 31, 17, 18) AND En.BethelEnrollmentYesNo = 1 AND CONVERT (nvarchar, GETDATE(), 112) BETWEEN Pr.StartDate AND \n\
//        ISNULL(Pr.EndDate, N'29991231') AND (Pr.GeoLocationId IS NULL OR Sg.GeoLocationId = Pr.GeoLocationId) \n\
//        and sg.GeoLocationId=115 ORDER BY Name";
//                    $('#ItemTableContainer').jtable('load');
//                }
//                
//            });
    </script>
<?php require_once ("footer.html");?>