$('body').on("click",".un-list-body > div",function(){
    $('#caption_wait').html('<div class="pls-wait"><i class="fa fa-spinner fa-spin"></i> '+lan["hashmon_pleasewait"]+'</div>');
    var user = $(".un-current").text();
    var data = {
        'user':user,
    };
    $.ajax({
        url: 'index.php?do=fix_permission&subdo=get_domain',
        data:data,
        type: "POST",
        success: function(data) {
            $("#content_virthost").show().html(data);
             $('#caption_wait').html('');
        }
    });
});


$('body').on("click",".do_fix",function(){
    $('#caption_wait_fix').html('<div class="pls-wait"><i class="fa fa-spinner fa-spin"></i> '+lan["hashmon_pleasewait"]+'</div>');
    var site = $("#sel_domain").val();
    var data = {
        'site':site,
    };
    $.ajax({
        url: 'index.php?do=fix_permission&subdo=fix',
        data:data,
        type: "POST",
        success:function(data){
            $('#caption_wait_fix').html('');
            var obj = $.parseJSON(data);
            show_modal(obj.message);
        }
    });
});
