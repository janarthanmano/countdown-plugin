$(document).ready(function(){
    $('#generate-countdown').click(function(){

        $("#countdown_field_generate_posts").addClass('d-none');

        var data = {
            action: "countdown_create_dummy_posts",
            no_of_posts: $('#countdown_field_no_of_posts').val()
        }

        $.post(countdown_ajax_object.ajax_url, data, function(e) {
            if (e.success) {
                if(e.data.status == 'success'){
                    $("#countdown_field_generate_posts").html(e.data.html).removeClass('d-none').fadeIn();
                    setTimeout(function(){ $("#countdown_field_generate_posts").fadeOut() }, 2000);
                }
            }
        });
    });
});
