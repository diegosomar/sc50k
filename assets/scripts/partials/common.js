(function($) {
  'use strict';

  $(function() {
    $(document).ready(function(){

      // Enable firefox compatibility with link rel preload
      if ( navigator.userAgent.search("Firefox") > -1 ) {
        $('link[rel="preload"]').each(function(){
          if ( $(this).attr('as') == 'style' ){
            $(this).attr('rel', 'stylesheet');
          }
        });
      }
    });
  });
})(jQuery);
