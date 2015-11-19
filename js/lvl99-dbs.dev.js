// LVL99 Plugin logic
(function ($, window) {
  $(document).ready( function () {
    var $doc = $(document),
        $win = $(window),
        $html = $('html'),
        $body = $('body');

    // Enable/disable selected posttypes
    $doc.on( 'change', '.lvl99-dbs-tables input[type=radio]', function (event) {
      var $elem = $(this),
          $options = $('.lvl99-dbs-tablelist input[type=checkbox]');

      if ( $elem.val() === 'all' ) {
        if ( $elem.is(':checked') ) {
          $options.attr('checked', 'checked').attr('disabled', 'disabled');
        } else {
          $options.removeAttr('disabled');
        }
      } else if ( $elem.val() === 'selected' ) {
        if ( $elem.is(':checked') ) {
          $options.removeAttr('disabled');
        } else {
          $options.attr('disabled', 'disabled');
        }
      }
    });

    // Add filter
    $doc.on( 'click', 'a[href=#add-filter]', function (event) {
      var rand = 'a'+(new Date().getTime()+'').slice(-8, -1),
          $newFilter = $('<div class="lvl99-dbs-filter-item ui-draggable ui-sortable"><div class="lvl99-dbs-filter-method"><span class="fa-arrows-v lvl99-sortable-handle"></span><select name="lvl99-image-import_filters['+rand+'][method]"><option value="replace">Search &amp; Replace</option></select></div><div class="lvl99-dbs-filter-input"><input type="text" name="lvl99-dbs_filters['+rand+'][input]" value="" placeholder="Search for..." /></div><div class="lvl99-dbs-filter-output"><input type="text" name="lvl99-dbs_filters['+rand+'][output]" value="" placeholder="Replace with empty string" /></div><div class="lvl99-dbs-filter-controls"><a href="#remove-filter" class="button button-secondary button-small">Remove</a></div></div>');

      event.preventDefault();
      $newFilter.appendTo('.lvl99-dbs-filters');
    });

    // Change filter type
    $doc.on( 'change', '.lvl99-dbs-filter-method select', function (event) {
      var $select = $(this),
          $item = $select.parents('.lvl99-dbs-filter-item');

      switch ( $select.val() ) {
        case 'replace':
          $item.find('.lvl99-image-import-filter-output input').show();
          break;
      }
    })

    // Remove filter
    $doc.on( 'click', 'a[href=#remove-filter]', function (event) {
      event.preventDefault();
      var $filter = $(this).parents('.lvl99-dbs-filter-item');
      $filter.remove();
    });

    // Initialise sortables
    $('.lvl99-sortable').sortable({
      items: '.lvl99-dbs-filter-item',
      handle: '.lvl99-sortable-handle'
    });

    // Submit form
    $doc.on('submit', '.lvl99-dbs-page form', function (event) {
      console.log( 'submitted form' );
      $(this).attr('disabled', 'disabled');
      $(this).find('button, input[type=submit], input[type=button]').attr('disabled', 'disabled');
      $(this).find('.lvl99-dbs-loading').show();
    });

  });
})(jQuery, window);
