$("body")
  .on('click', '.set-email-message-default', function() {
    var url = `/api/trainer/set-default-message`;
    var type = $(this).data('type');
    var placeholders = $(this).data('placeholders');
    var conversion = $(this).data('conversion');
    var text = $('#summernote').summernote('code');
    var subject = $("input[name=subject]").val();

    try {
      var message = conversion === 'tags' ?
        prepareMessageUsingTags(text, placeholders) :
        prepareMessageUsingClasses(text, placeholders);
      var data = {
        textarea: message,
        type: type,
        subject: subject
      };
      $.post(url, data)
        .done(function(res) {
          toastr.success(res.reason);
        })
        .error(function(res) {
          toastr.error(res.reason);
        });
    } catch(err) {
      toastr.error(err);
    }


  }).on('change', '.default-message-dropdown', function() {
    var id = $(this).val();
    var data = $(this).find(':selected').data();
    var textarea = $('#summernote');
    var subject = $("input[name=subject]");
    var settings = $(this).parents('form').find('.set-email-message-default');
    var placeholders = settings.data('placeholders');
    var conversion = settings.data('conversion');

    if(data.msg) {
      textarea.summernote('code', conversion === 'tags' ? prepareMessageUsingTags(data.msg, placeholders, false) : prepareMessageUsingClasses(data.msg, placeholders, false));
      subject.val(data.subject)
    } else {
      var url = `/api/trainer/get-default-message/${id}`;
      $.get(url)
        .done(function(res) {
          textarea.summernote('code', conversion === 'tags' ? prepareMessageUsingTags(res.message, placeholders, false) : prepareMessageUsingClasses(res.message, placeholders, false));
          if(res.subject) {
            subject.val(res.subject);
          }
        });
    }
  });

function getUserDefaultMessagesByType(el, type, defaultMsg, client, callback) {
  el.html('');
  el.append(`<option data-msg="${defaultMsg}">Default Message</option>`);

  const url = `/api/trainer/get-default-messages/${type}/${client}`;
  $.get(url)
    .done(res => {
      Object.values(res.defaultMessages).map(msg => {
        el.append(`<option value="${msg.id}">${msg.title}</option>`);
      })
      //set last element as selected
      el.find("option:last").attr("selected", "selected");
      if(res.defaultMessages.length > 0) {
        callback(res.defaultMessages[Object.keys(res.defaultMessages).length-1].message);
      } else {
        callback(defaultMsg);
      }
    });
}

function prepareModal($modal, type, placeholders, action, client, nextStepEnabled) {
  const $setMsgAsDefaultCTA = $modal.find('.set-email-message-default');
  const $clientInput = $modal.find('input[name=client]');
  const $form = $modal.find('form');
  const $nextStep = $modal.find('.client-next-step');

  $setMsgAsDefaultCTA.data('type', type);
  $setMsgAsDefaultCTA.data('placeholders', placeholders);
  $clientInput.val(client);

  if(typeof action != "undefined") {
    $form.attr('action', action);
  }

  if(nextStepEnabled) {
    $nextStep
      .show()
      .find('select').prop('disabled', false);
  } else {
    $nextStep
      .hide()
      .find('select').prop('disabled', true);
  }
}

function prepareMessageUsingClasses(message, placeholders, toActualValues = true) {
  Object.keys(placeholders).forEach(placeholder => {
    var el = document.createElement('div');
    el.innerHTML = message;
    let ph = '.' + placeholder;
    let element = $(ph, el);

    if(toActualValues) {
      if(element[0]) {
        //throw('An error occurred! Engineers have been notified.');
        // we empty the strings for insertion into db
        element.text('');

        if(element[0].tagName == 'A') {
          element.attr('href', 'url');
        }

      }
    } else {
      // we populate the strings
      let val = placeholders[placeholder];
      element.text(val);

      if(element[0] && element[0].tagName == 'A') {
        element.attr('href', val);
      }
    }

    message = el.innerHTML;
  });

  return message;
}

function prepareMessageUsingTags(message, placeholders, toActualValues = true) {
  console.log(placeholders)
  Object.keys(placeholders).forEach(placeholder => {
    for(var i = 0; i < 2; i++) {
      message = toActualValues ? message.replace(placeholders[placeholder], `[${placeholder}]`) : message.replace(`[${placeholder}]`, placeholders[placeholder])
    }
  });
  console.log(message)
  return message;
}
