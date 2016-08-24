function init(elem, options){
        elem.addClass('countdownHolder');
        // Создание HTML разметки внутри контейнера
        $.each(['Days','Hours','Minutes','Seconds'],function(i){
                $('<span class="count'+this+'">').html(
                        '<span class="position">\
                                <span class="digit static">0</span>\
                        </span>\
                        <span class="position">\
                                <span class="digit static">0</span>\
                        </span>'
                ).appendTo(elem);
                if(this!="Seconds"){
                        elem.append('<span class="countDiv countDiv'+i+'"></span>');
                }
        });
}
// Создает анимационный эффект между двумя цифрами таймера
function switchDigit(position,number){
        var digit = position.find('.digit')
        if(digit.is(':animated')){
                return false;
        }
        if(position.data('digit') == number){
                // We are already showing this number
                return false;
        }
        position.data('digit', number);
        var replacement = $('<div>',{
                'class':'digit',
                css:{
                        top:'-2.1em',
                        opacity:0
                },
                html:number
        });
        // The .static class is added when the animation
        // completes. This makes it run smoother.
        digit
                .before(replacement)
                .removeClass('static')
                .animate({top:'2.5em',opacity:0},'fast',function(){
                        digit.remove();
                })
        replacement
                .delay(100)
                .animate({top:0,opacity:1},'fast',function(){
                        replacement.addClass('static');
                });
}

(function($){
        // количество секунд в каждом блоке времени
               var days        = 24*60*60,
                hours        = 60*60,
                minutes        = 60;
        // создание плагина
        $.fn.countdown = function(prop){
                var options = $.extend({
                        callback        : function(){},
                        timestamp        : 0
                },prop);
                var left, d, h, m, s, positions;
                // Инициализация таймера
                init(this, options);
                positions = this.find('.position');
                (function tick(){
                        // Оставьшееся время
                        left = Math.floor((options.timestamp - (new Date())) / 1000);
                        if(left < 0){
                                left = 0;
                        }
                        // Количество оставшихся дней
                        d = Math.floor(left / days);
                        updateDuo(0, 1, d);
                        left -= d*days;
                        // Количество оставшихся часов
                        h = Math.floor(left / hours);
                        updateDuo(2, 3, h);
                        left -= h*hours;
                        // Количество оставшихся минут
                        m = Math.floor(left / minutes);
                        updateDuo(4, 5, m);
                        left -= m*minutes;
                        // Количество оставшихся секунд
                        s = left;
                        updateDuo(6, 7, s);
                        // Callback
                        options.callback(d, h, m, s);
                        // Автоматический вызов этой функции каждую секунду
                        setTimeout(tick, 1000);
                })();
                // Эта функция обновляет две цифры в блоке
                function updateDuo(minor,major,value){
                        switchDigit(positions.eq(minor),Math.floor(value/10));
                        switchDigit(positions.eq(major),value);
                }
                return this;
        };
        /* Две вспомогательные функции, которые мы создали ранее */
})(jQuery);