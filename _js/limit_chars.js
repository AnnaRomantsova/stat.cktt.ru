   (function($) {
    $.fn.extend( {
        limiter: function(limit) {
            $(this).on("keyup focus", function() {

                setCount(this);
            });
            function setCount(src) {
                //alert(src);
                var chars = src.value.length;
                if (chars > limit) {
                    src.value = src.value.substr(0, limit);
                    chars = limit;
                }
                //elem.html( limit - chars );
            }
            setCount($(this)[0]);
        }
    });
    })(jQuery);
