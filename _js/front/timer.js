$(function(){

var note = $('#note'),
        //ts = new Date(2014, 2, 7),
        newYear = true;
//if((new Date()) > ts){
        var t = new Date();
        var beforeTommorow = 6*1000;//(24-t.getHours())*60*60*1000+(60-t.getMinutes())*60*1000+(60-t.getSeconds())*1000;

        ts = (new Date()).getTime() + beforeTommorow;
        newYear = false;
//}

$('#countdown').countdown({
        timestamp        : ts,
        callback        : function(days, hours, minutes, seconds){
                if (seconds==0 && minutes==0 && hours==0 && days==0)
                   submit_dalee('next_form');
                var message = "";
               /*
                message += days + " day" + ( days==1 ? '':'s' ) + ", ";
                message += hours + " hour" + ( hours==1 ? '':'s' ) + ", ";
                message += minutes + " minute" + ( minutes==1 ? '':'s' ) + " and ";
                message += seconds + " second" + ( seconds==1 ? '':'s' ) + " <br />";

                if(newYear){
                        message += "left until the new year!";
                }
                else {
                        message += "left to 10 days from now!";
                }

                note.html(message);
                */
        }
});
});