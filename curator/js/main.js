

$(document).ready(function() {
    $('.login-link').click(function (e) {
        e.preventDefault();
        $('#login_form').dialog({
            modal:true,
            width:420,
            height:240,
            buttons: {
                Login: function () {
                    $('#login_form').submit();
                },
                Cancel: function() {
                    $(this).dialog('close');
                }
            }
        });
        $('#login_form').removeClass('hidden');
    });
});
