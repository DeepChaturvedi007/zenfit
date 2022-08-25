toastr.options.preventDuplicates = true;

$('body').on('click', '[data-action="toggle-action"]', function (event) {
  event.preventDefault();

  var $el = $(this);
  var $dropdown = $el.closest('.dropdown');
  var currentStatus = $dropdown.data('status');
  var nextStatus = $el.data('status');
  var url = $el.data('url');

  if (currentStatus === nextStatus || !url) {
    return;
  }

  var titles = {
    'active': 'Active',
    'inactive': 'Inactive',
    'hidden': 'Hidden',
  };

  $.post(url, {
    status: nextStatus,
  }, function (res) {
    if (nextStatus === 'hidden') {
        toastr.success('The plan is now hidden, and won\'t be visible in your client\'s app.');
    } else if (nextStatus == 'active'){
        toastr.success('This plan is now active in your client\'s app.', 'Plan status updated');
    } else if (nextStatus === 'inactive') {
        toastr.info('This plan is now inactive. Your client can still see it in the Zenfit app, but it will be listed as inactive.', 'Plan status updated');
    }

    var isActive = nextStatus === 'active';

    $dropdown
      .data('status', nextStatus)
      .find('.plan-status-label')
      .toggleClass('text-valid', isActive)
      .toggleClass('text-invalid', !isActive)
      .text(titles[nextStatus]);
  })
  .fail(function() {
    toastr.error('Status update failed.');
  })
});
