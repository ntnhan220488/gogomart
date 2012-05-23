$j(document).ready(function(){
   $j(".block-viewed .block-content").hide();
   $j(".block-viewed .block-title").click(function(){
       $j(".block-viewed .block-content").toggle();
   });
});