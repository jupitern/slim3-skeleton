


/* Datatable */
function initDatatable(instanceName, ajaxUrl, columns, optionsOverride)
{
    var options = {
        'orderCellsTop': true,
        'sDom': '<"top">rt<"bottom"ip><"clear">',
        'processing': true,
        'serverSide': true,
        'ajax': {
            url: ajaxUrl,
            data : function(d) {
                d.searchFields = $("#"+instanceName+" .gridSearchForm").serializeObject();
            },
            type: 'POST'
        },
        'columns': columns,
        'info': false,
        "pagingType": "full_numbers",
        'pageLength': 10
    };

    var dt = $("#"+instanceName+" .gridTable").DataTable(options);

    $('#'+instanceName+' .gridSearchToggleBtn').on('click', function () {
        var formObj = $(this).parent().find('.gridSearchForm');
        formObj.is(":visible") ? formObj.hide() : formObj.show();
    }).trigger('click');

    $('#'+instanceName+' .gridSearchBtn').on('click', function () {
        $(this).parents('.gridContainer').find('table').DataTable().ajax.reload(null, false);
    });

    return dt;
}

// Loads the correct sidebar on window load,
// collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    $('#side-menu').metisMenu();

    $(window).bind("load resize", function() {
        var topOffset = 50;
        var width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        var height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });
});