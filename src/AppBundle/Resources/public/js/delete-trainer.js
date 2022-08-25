$(function() {
    $('#delete-trainer form').on('submit', function(e) {
        e.preventDefault();
        var modal = $('#delete-trainer');
        var th = $(this);
        var formData = th.serializeArray();
        var button =  th.find('button[type="submit"]');
        var buttonDefault = th.find('.btn-default');
        button.html(button.attr('data-loading-text'));
        $.post(th.attr('action'), formData).done((data) => {
            window.location.href = "/logout";
    }, 1000);
    });


});