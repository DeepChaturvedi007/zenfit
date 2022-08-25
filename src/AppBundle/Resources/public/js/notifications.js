(function ($) {
  function isFunction(fn) {
    return typeof fn === 'function';
  }

  function Notifications(settings) {
    if (typeof settings !== 'object' || settings === null) {
      settings = {};
    }

    this.fetching = false;
    this.pagination = settings.pagination;
    this.isEmpty = true;

    this.before = $.noop;
    this.after = $.noop;
    this.success = $.noop;
    this.failure = $.noop;
  }

  Notifications.prototype.fetch = function () {
    if (this.fetching || (this.pagination && !this.pagination.next)) {
      return;
    }

    this.fetching = true;

    if (isFunction(this.before)) {
      this.before.call(this);
    }

    var promise = $.getJSON('/notifications/latest', {
      page: this.pagination ? this.pagination.next : 1,
    });

    var that = this;

    promise
      .done(function (response) {
        that.pagination = response.pagination;
        that.isEmpty = !response.data.length;

        if (isFunction(that.success)) {
          response.data = response.data.filter(function (message) {
            return message.event_name !== 'client.login';
          });

          that.success.apply(that, [response]);
        }
      })
      .fail(function () {
        if (isFunction(that.failure)) {
          that.failure.apply(that, Array.prototype.slice.call(arguments));
        }
      })
      .always(function () {
        that.fetching = false;

        if (isFunction(that.after)) {
          that.after.apply(that);
        }
      });

    return promise;
  };

  Notifications.prototype.markAll = function (ids) {
    if (!Array.isArray(ids) || !ids.length) {
      return;
    }

    return $.post('/notifications/mark', { id: ids }, 'json');
  };

  Notifications.messageReducer = function (message) {
    var action = '';
    var clientName = message.client_name;

    switch (message.event_name) {
      case 'client.updated_bodyprogress':
        action = 'updated <strong>Body progress</strong>';
        break;
      case 'client.updated_meal_plans':
        action = 'updated <strong>Meal progress</strong>';
      case 'client.uploaded_image':
        action = 'updated <strong>Progress picture</strong>';
        break;
      case 'client.login':
        action = 'has <strong>Logged in</strong>';
        break;
      case 'client.created_login':
        action = 'created login to <strong>Zenfit</strong>';
      case 'client.filled_out_survey':
        action = 'answered the <strong>Questionnaire</strong>';
      case 'client.old_meal_plans':
        action = 'meal plan is now more than <strong>60 days old</strong>';
        clientName += '\'s';
        break;
      case 'client.old_workout_plans':
        action = 'workout plan is now more than <strong>60 days old</strong>';
        clientName += '\'s';
        break;
      case 'client.requires_login':
        action = 'didn\'t create login - want to remind client?'
        break;
      case 'client.requires_invitation':
        action = 'didn\'t receive invitation - want to resend invitation?'
        break;
    }

    return (
      '<strong>' + clientName + '</strong> ' + action +
      '<time>' + message.date_diff + '</time>'
    );
  };

  Notifications.renderMessage = function (message) {
    var picture = message.client_picture ? message.client_picture : '/bundles/app/1456081788_user-01.png';
    var href = '#';
    var attrs = [
      'data-id="' + message.id + '"',
      'data-client-id="' + message.client_id + '"',
      'data-client-name="' + message.client_name + '"',
      'data-client-email="' + message.client_email + '"',
    ];

    if (message.event_name === 'client.requires_invitation' || message.event_name === 'client.requires_login') {
      attrs.push('data-action="inviteClient"');
      attrs.push('data-msg="The invitation email wasn\'t delivered, please check the e-mail and reinvite the client."');
    }

    if (message.event_name === 'client.updated_bodyprogress' || message.event_name === 'client.updated_meal_plans' || message.event_name === 'client.uploaded_image') {
      href = '/dashboard/clientBodyProgress/' + message.client_id;
    } else if (message.event_name === 'client.old_meal_plans') {
      href = '/dashboard/mealPlanOverview/client/' + message.client_id;
    } else if (message.event_name === 'client.old_workout_plans') {
      href = '/dashboard/workoutOverview/client/' + message.client_id;
    }

    return (
      '<a class="dropdown-notifications-message' + (message.seen ? '' : ' is-new') + '" href="' + href + '" ' + attrs.join(' ') + '>' +
      '<img class="img-circle" src="' + picture + '" alt="">' +
      '<div>' + Notifications.messageReducer(message) + '</div>' +
      '</a>'
    );
  };

  Notifications.renderMessagePlaceholder = function () {
    return (
      '<div class="dropdown-notifications-message is-placeholder">' +
        'No notifications...' +
      '</div>'
    );
  };

  Notifications.renderSpinner = function () {
    return (
      '<div class="sk-spinner sk-spinner-fading-circle">' +
      '<div class="sk-circle1 sk-circle"></div>' +
      '<div class="sk-circle2 sk-circle"></div>' +
      '<div class="sk-circle3 sk-circle"></div>' +
      '<div class="sk-circle4 sk-circle"></div>' +
      '<div class="sk-circle5 sk-circle"></div>' +
      '<div class="sk-circle6 sk-circle"></div>' +
      '<div class="sk-circle7 sk-circle"></div>' +
      '<div class="sk-circle8 sk-circle"></div>' +
      '<div class="sk-circle9 sk-circle"></div>' +
      '<div class="sk-circle10 sk-circle"></div>' +
      '<div class="sk-circle11 sk-circle"></div>' +
      '<div class="sk-circle12 sk-circle"></div>' +
      '</div>'
    );
  };

  $.Notifications = Notifications;


  // Notifications Drop Down

  var ddNotifications = new Notifications();
  var windowScrollY = 0;
  var windowScrollBlocked = false;

  var $notifications = $('.metabar-notifications');
  var $messages = $('.dropdown-notifications-messages');
  var $countLabel = $notifications.find('.metabar-notifications-toggle .label');

  ddNotifications.before = function () {
    $messages
      .toggleClass('is-loading', this.fetching)
      .append(Notifications.renderSpinner());
  };

  ddNotifications.after = function () {
    $messages
      .toggleClass('is-loading', this.fetching)
      .find('.sk-spinner')
      .remove();
  };

  ddNotifications.success = function (response) {
    $messages.find('.is-placeholder').remove();

    if (this.isEmpty) {
      $messages.append(Notifications.renderMessagePlaceholder());
    } else {
      $messages.append(response.data.map(Notifications.renderMessage));
    }
  };

  $messages
    .on('scroll', function (e) {
      const target = e.target;

      if (!ddNotifications.isEmpty && (target.scrollTop + target.clientHeight) === target.scrollHeight) {
        ddNotifications.fetch();
      }
    })
    .closest('.metabar-notifications')
    .on('mouseenter', function () {
      windowScrollBlocked = true;
    })
    .on('mouseleave', function () {
      windowScrollBlocked = false;
    });

  $notifications
    .on('notifications:mark', function (event, ids) {
      if (!Array.isArray(ids)) {
        ids = [];
      }

      var count = (parseInt($countLabel.attr('data-count'), 10) || 0) - ids.length;

      if (count < 0 || isNaN(count)) {
        count = 0;
      }

      $countLabel
        .attr('data-count', count)
        .text(count > 9 ? '9+' : count);

      $messages
        .find('.is-new')
        .filter(function () {
          return ids.indexOf(this.getAttribute('data-id'));
        })
        .removeClass('is-new');
    })
    .on('shown.bs.dropdown', function () {
      windowScrollY = window.scrollY;
      windowScrollBlocked = true;
      window.addEventListener('scroll', blockWindowScroll);

      if (ddNotifications.isEmpty) {
        ddNotifications.fetch();
      }
    })
    .on('hidden.bs.dropdown', function () {
      windowScrollBlocked = false;
      window.removeEventListener('scroll', blockWindowScroll);
    })
    .on('click', '.mark-all-read', function (e) {
      e.stopPropagation();
      e.preventDefault();
      markAll();
    });


  $('body').on('click', '.dropdown-notifications-message', function (event) {
    event.preventDefault();

    var $target = $(this);
    var href = $target.attr('href');

    if (href && !/^\#/.test(href)) {
      var ids = [parseInt($target.data('id'), 10)];

      ddNotifications
        .markAll(ids)
        .done(function () {
          $notifications.trigger('notifications:mark', [ids]);
          window.location = href;
        });
    }
  })

  function blockWindowScroll() {
    if (windowScrollBlocked) {
      window.scrollTo(0, windowScrollY);
    }
  }

  function markAll() {
    var $unseen = $messages.find('.is-new');
    var ids = [];

    $unseen.each(function () {
      ids.push(parseInt(this.dataset.id, 10));
    });

    if (!ids.length) {
      return;
    }

    ddNotifications
      .markAll(ids)
      .done(function () {
        $notifications.trigger('notifications:mark', [ids]);
      });
  }
})(jQuery);