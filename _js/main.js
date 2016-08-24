jQuery(document).ready(function(){

        jQuery('.transform-check').iCheck({ checkboxClass: 'icheckbox_minimal' });

       //jQuery(".transform-select").coreUISelect();

        //jQuery("#list-special").jScrollPane();

        jQuery("#toggle-menu").on("click", function(){

                jQuery("#h-user-menu").toggleClass("open");

                return false;

        });

        jQuery("#toggle-city").on("click", function(){

                jQuery("#city-list").toggleClass("open");

                return false;

        });

        jQuery(".part-list > li > a").on("click", function(){

                jQuery(this).parent().toggleClass("open");

                return false;

        });

        jQuery(".portfolio-photo-more").on("click", function(){

                jQuery(".more-photo").slideToggle(200);
                jQuery(".more-photo").toggleClass("open");

                return false;

        });

        jQuery(".portfolio-photo-more").toggle(
                function () {
                        jQuery(this).text("Свернуть все");

                },

                function () {
                        jQuery(this).text("Развернуть все");
                }
        );

        jQuery("#open-list-special").on("click", function(){

                jQuery(".list-special-wr").toggleClass("open");

                return false;

        });

        jQuery("#open-list-special").toggle(
                function () {
                        jQuery(this).text("Свернуть");

                },

                function () {
                        jQuery(this).text("Выбрать");
                }
        );

        jQuery('#link-enter').click(function() {

                jQuery('#block-enter').reveal({
                        animation: 'fadeAndPop',
                        animationspeed: 300,
                        closeonbackgroundclick: true,
                        dismissmodalclass: 'close-reveal-modal'
                });
        });

        jQuery('#link-registration').click(function() {
                jQuery('#block-registration').reveal({
                        animation: 'fadeAndPop',
                        animationspeed: 300,
                        closeonbackgroundclick: true,
                        dismissmodalclass: 'close-reveal-modal'
                });
        });

        jQuery(".no-login a").on("click", function(){

                jQuery(".reveal-modal").toggleClass("open");

                return false;

        });

        jQuery(".other-city").jScrollPane({
                'mouseWheelSpeed':'20',
                'stickToBottom':'true',
                'verticalDragMaxHeight':'40'
        });

});