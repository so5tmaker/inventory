

$(document).ready(function(){ 

});

function showmess(mess, color, fade) {
    $('#message').css('color',color);
    $('#message').stop(true, true).show().html(mess);
    if (fade){
        $('#message').fadeOut(10000);
    }
}

function proverka(input) { 
    if (input.value == '00.000') {
        input.value = '';
    } else {
        input.value = input.value.replace(/[^\d.]/g, '');
    }
};

// убираю из даты разные хвосты
function new_date(strDate) {   
//    var new_date = strDate;
    var date = new Date(strDate);
    var newdate = ('0'+date.getDate()).substr(-2,2)+'.'+('0'+(date.getMonth()+1)).substr(-2,2)+'.' + date.getFullYear();
//    if (strDate.length > 20){
//        var a1 = [ 4, 6 ];
//        var a2 = [ '.', ':' ];
//        for (var i = 0; i < a1.length; i++) {
//          var s1 = strDate.substring(strDate.length-a1[i], strDate.length);
//            var p1 = s1.slice(0,1);
//            if (p1 === a2[i]){
//                new_date = strDate.slice(0, strDate.length - s1.length);
//                break;
//            } 
//        }
//    }
    return newdate;    
}
