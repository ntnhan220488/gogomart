$(document).ready(function(){
   $(".block-viewed .block-content").hide();
   $(".block-viewed .block-title").hover(
        function(){$(".block-viewed .block-content").show();},
        function(){$(".block-viewed .block-content").hide();}
    );
});