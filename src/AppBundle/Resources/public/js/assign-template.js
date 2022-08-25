(function ($) {
  var modals = {
    $assignTemplate: $('#assignTemplate'),
  };

  var firstStep = modals.$assignTemplate.find('#assign-step-one');
  var secondStep = modals.$assignTemplate.find('#assign-step-two');

  var search = firstStep.find('.search');
  var userImageUrl = '/bundles/app/1456081788_user-01.png';
  var subDescription = firstStep.find('.sub-description');
  var assignBtn = firstStep.find('.btn-assign');
  var doneBtn = secondStep.find('.btn-assign');

  firstStep.find('.btn-cancel').on('click', function () {
      modals.$assignTemplate.modal('hide');
  });

  modals.$assignTemplate.on('show.bs.modal', function (e) {
    if(stop) {
        e.preventDefault();
        stop = false;
        modalData = $(e.relatedTarget).data() || modals.$assignTemplate.data();
    }
    subDescription.html('0 Clients Selected');
    fetchClients('');
  });

  modals.$assignTemplate.on('hidden.bs.modal', function () {
      search.val('');
      firstStep.show();
      secondStep.hide();
      stop = 1;
  });

  search.keyup(function (e) {
      var value = $(e.target);
      subDescription.html('0 Clients Selected');
      assignBtn.addClass('disabled');
      fetchClients(value.val());
  });

  function getUsers(url) {
      return new Promise(function(resolve, reject) {
          var xhr = new XMLHttpRequest();
          xhr.open('GET', url, true);
          xhr.onload = function() {
              if (this.status == 200) {
                  resolve(JSON.parse(this.response));
              } else {
                  var error = new Error(this.statusText);
                  error.code = this.status;
                  reject(error);
              }
          };
          xhr.onerror = function() {
              reject(new Error("Network Error"));
          };
          xhr.send();
      });
  }


  var userList = firstStep.find('.user-list');


  function fetchClients (q) {
      getUsers('/api/trainer/clients?q=' + q).then(
          function (response) {
              var clients = response.clients
                  ? response.clients.map(function (client) {
                      return userItem(client);
                  })
                  : '';
              userList.html(clients);
              selectClient();
              var cliHeight = firstStep.find('.assign-client').outerHeight() * clients.length;
              userList.css('height', cliHeight);
          }
      ).then(
          function () {
              if (!stop) {
                  modals.$assignTemplate.modal();
                  stop = true;
              }
          }
      );
  }

  function selectClient() {
      firstStep.find('.assign-client').on('click', function () {
          var input = $(this).find('input');
          var isChecked = !input.is(':checked');
          input.prop('checked', isChecked);
          clientInputs = firstStep.find(".assign-client input[type='checkbox']:checked");
          if (clientInputs.length) {
              assignBtn.removeClass('disabled');
          } else {
              assignBtn.addClass('disabled');
          }
          subDescription.html(clientInputs.length + ' Clients Selected');
      });
  }

  var s3 = 'http://zenfit-images.s3-website.eu-central-1.amazonaws.com/before-after-images/client/photo/';
  function userItem(client) {
      var src = client.photo ? s3 + client.photo : userImageUrl;
      var clientHtml =
          '<div class="assign-client">' +
          '<div class="assign-client-info">' +
          '<div class="assign-client-image">' +
          '<img src="' + src + '"/>' +
          '</div>' +
          '<div class="assign-client-name">' + client.name + '</div>' +
          '</div>' +
          '<input type="checkbox" value="' + client.id + '" name="client[]" onClick="this.checked=!this.checked;">' +
          '</div>'
      ;
      return clientHtml;
  }

  function userLink(client) {
      var clientHtml =
          '<div class="assign-client">' +
          '<div class="assign-client-info">' +
          '<div class="assign-client-name"><a href="/workout/clients/' + client.clientId + '">' + client.clientName + '</a></div>' +
          '</div>' +
          '</div>'
      ;
      return clientHtml;
  }

  assignBtn.click(function () {
      var $this = $(this);
      var btnName = $this.html();
      var clients = {};
      clients.clientsIds = clientInputs.map(function () {
          var $this = $(this);
          $this.prop('checked', false);
          return $this.val();
      }).get();
      clients = JSON.stringify(clients);

      $.post('/api/workout/client/assign-plan/' + modalData.plan, clients, function () {
          $this.html('Assigning...');
      }).done(function (res) {
          clientInputs = [];
          $this.html(btnName).addClass('disabled');
          var clients = res.map(function (client) {
              return userLink(client);
          });
          secondStep.find('.user-list').html(clients);
          firstStep.hide();
          secondStep.show();
      }).fail(function () {
          $this.html(btnName).addClass('disabled');
      });
  });

  doneBtn.click(function () {
      modals.$assignTemplate.modal('hide');
  });


}(jQuery));
