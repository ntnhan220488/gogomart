$(document).ready(function(){
   $(".block-viewed .block-content").hide();
   $(".block-viewed").hover(
        function(){$(".block-viewed .block-content").show();},
        function(){$(".block-viewed .block-content").hide();}
    );
});