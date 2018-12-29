$(function() {
    $("body").find('a[data-menu-action=quick_delete_block]').unbind('click.core').on('click.core', function(evt) {
        evt.preventDefault();
        $.concreteAjax({
            url: $(this).attr('data-menu-href'),
            data: {
                'cID': $(this).attr('data-cid'),
                'bID': $(this).attr('data-bid'),
                'arHandle': $(this).attr('data-arhandle'),
                'ccm_token': $(this).attr('data-token'),
            },
            success: function(r) {
                var editor = Concrete.getEditMode();
                var area = editor.getAreaByID(parseInt(r.aID));
                var block = area.getBlockByID(parseInt(r.bID));

                ConcreteEvent.fire('EditModeBlockDeleteComplete', {
                    block: block
                });

                ConcreteAlert.notify({
                    'message': r.message,
                    'title': r.title
                });
            }
        });

    });
});