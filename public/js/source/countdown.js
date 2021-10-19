$(document).ready(function(){
    $('#load_more').click(function(){
        loading_button = $(this);
        loading_gif = $('#loading');
        all_loaded = $('#all_loaded');
        $(this).addClass('d-none');
        loading_gif.removeClass('d-none');

        page = $(this).data('page');
        var data = {
            action: "load_more_countdown",
            page: page,
        };

        //console.log(data);

        $.post(countdown_ajax_object.ajax_url, data, function(e) {
            if (e.success) {
                $('#countdown_archive').append(e.data.html);
                if (!e.data.nextPage) {
                    all_loaded.removeClass('d-none');
                    loading_button.addClass('d-none');
                    loading_gif.addClass('d-none');
                }else {
                    page = page + 1;
                    loading_button.data('page', page);
                    all_loaded.addClass('d-none');
                    loading_button.removeClass('d-none');
                    loading_gif.addClass('d-none');
                }
            } else {
                all_loaded.removeClass('d-none');
                loading_button.addClass('d-none');
                loading_gif.addClass('d-none');
            }
        });
    });
});
