$(document).ready(function () {
  var page = 1;

  function pad(num) {
    return num < 10 ? '0' + num : num;
  }

  $('#data_1 .input-group.date, #newClientImage .date, #data_2 .input-group.date').datepicker({
    todayBtn: 'linked',
    keyboardNavigation: false,
    forceParse: false,
    calendarWeeks: true,
    autoclose: true,
    format: 'M dd, yyyy',
  });

  $('#data_1 .input-group.date,#newClientImage .date, #data_2 .input-group.date').datepicker('setDate', new Date());

  var previousFeedbackLoaded = false;

  $('#addImage :file').on('fileselect', function (event, numFiles, label) {

    var input = $(this).parent('div').find('input[type=text]'),
      log = numFiles > 1 ? numFiles + ' files selected' : label;
    if (input.length) {
      input.val(log);
    }
  });

  $('body')
    .on('click', '#side-picture, #front-picture, #back-picture, #browse-front, #browse-back, #browse-side', function () {
      $context = $(this);
      $context.parent('div').find('input[type=file]').click();
    }).on('change', '#addImage :file', function () {
    var input = $(this),
      numFiles = input.get(0).files ? input.get(0).files.length : 1,
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);
  }).on('click', '#addImage .cancel', function () {
    $('#addImage').modal('hide');
  }).on('click', '[data-confirm]', function (evt) {
    evt.preventDefault();

    var href = $(this).attr('href');
    bootbox.confirm('Are you sure?', function () {
      window.location.href = href;
    });
  }).on('click', '#previousFeedback .cancel', function (e) {
    e.preventDefault();
    $('#previousFeedback').modal('hide');
  }).on('show.bs.modal', '#previousFeedback', function () {
    if (previousFeedbackLoaded) {
      return;
    }
    $modal = $('#previousFeedback');
    var href = $modal.data('href');

    $.post(href).done(function (response) {
      $modal.find('.feedback-loader').hide();
      $modal.find('.feedback-message').html(response.content);
      previousFeedbackLoaded = true;
    }).fail(function (error) {
      $modal.find('.feedback-loader').show();
      $modal.find('.feedback-message').html('');
      $modal.modal('hide');
    });

  }).on('click', '#giveFeedback .send', function (e) {
    e.preventDefault();
    $modal = $('#giveFeedback');
    $button = $modal.find('.send').button('loading');
    var href = $modal.data('href');
    var content = $modal.find('.editable');
    content = sanitiseHtml(content[0].innerText);

    $.post(href, {
      content: content
    }).done(function (response) {
      $('.bodyProgressAlert').remove();
      toastr.options.preventDuplicates = true;
      toastr.success('Feedback was succesfully given. Message sent to client.', 'Progress viewed');
    }).fail(function (error) {
      toastr.options.preventDuplicates = true;
      toastr.success('Error', 'Progress viewed');
    }).always(function () {
      $button.button('reset');
      $modal.modal('hide');
    });

  }).on('click', '#confirmFeedback .cancel', function (e) {
    e.preventDefault();
    $modal = $('#confirmFeedback').modal('hide');
  }).on('click', '#confirmFeedback .send', function (e) {
    e.preventDefault();
    $modal = $('#confirmFeedback');
    $button = $modal.find('.send').button('loading');
    var href = $modal.data('href');

    $.post(href, {
      alreadyGiven: true
    }).done(function (response) {
      $('.bodyProgressAlert').remove();
      toastr.options.preventDuplicates = true;
      toastr.success('Feedback was marked as already given', 'Progress viewed');
    }).fail(function (error) {
      toastr.options.preventDuplicates = true;
      toastr.success('Error', 'Progress viewed');
    }).always(function () {
      $button.button('reset');
      $modal.modal('hide');
    });
  }).on('click', '.load-more-images', (function (e) {
    e.preventDefault();
    this.remove();
    page++;
    loadImages(page, 10)
  })).on('show.bs.modal', '#addBodyCircum, #addRecord', function (event) {
    var $modal = $(this);
    var $target = $(event.relatedTarget);
    var $form = $modal.find('form');
    var entry = $target.data('entry');

    if (entry) {
      Object.keys(entry).forEach(function (key) {
        var name = key.replace(/([A-Z])/g, function ($1) {
          return '_' + $1.toLowerCase();
        });
        var value = entry[key];
        var $input = $form.find('[name="' + name + '"]');

        if (key === 'date') {
          var date = new Date(entry[key]);
//                          value = pad(pad(date.getDate())  + '-' + date.getMonth() + 1) + '-' + date.getFullYear();
          $input.closest('.input-group').datepicker('setDate', date);
        } else if (value > 0) {
          $input.val(value);
        } else {
          $input.val('');
        }
      });
    }

    $modal.find('.modal-title').text((entry ? 'Edit' : 'Add') + ' ' + $modal.data('titleSuffix'));
    $form.find('[type="submit"]').text(entry ? 'Update record' : 'Add record');
  })
    .on('hidden.bs.modal', '#addBodyCircum, #addRecord', function () {
      $(this)
        .find('form')
        .trigger('reset')
        .find('.input-group.date')
        .datepicker('setDate', new Date());
    });

  function sanitiseHtml(value) {
    return value.replace(/[\t\v\f\r \u00a0\u2000-\u200b\u2028-\u2029\u3000]+/g, ' ')
      .split('\n')
      .clean()
      .map(line => line.trim())
      .join('<br>')
      .trim();
  }

  Array.prototype.clean = function () {
    for (var i = 0; i < this.length; i++) {
      if (this[i] == '') {
        this.splice(i, 1);
        i--;
      }
    }
    return this;
  };

  loadImages(0, 10);

  $('#data_1 .input-group.date, #newClientImage .date, #data_2 .input-group.date').datepicker({
    todayBtn: 'linked',
    keyboardNavigation: false,
    forceParse: false,
    calendarWeeks: true,
    autoclose: true,
    format: 'd M yyyy'
  });
  $('#data_1 .input-group.date,#newClientImage .date, #data_2 .input-group.date').datepicker('setDate', new Date());

  $('#uploadImageForm').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);
    var btn = $form.find('button.send');
    btn.button('loading');

    var frontI = $form.find('input[name="front-img[]"]');
    var backI = $form.find('input[name="back-img[]"]');
    var sideI = $form.find('input[name="side-img[]"]');
    if (!frontI.val()) {
      frontI.attr('disabled', true);
    }
    if (!backI.val()) {
      backI.attr('disabled', true);
    }
    if (!sideI.val()) {
      sideI.attr('disabled', true);
    }
    var formData = new FormData();
    formData.append('front-img', frontI.prop('files')[0])
    formData.append('back-img', backI.prop('files')[0])
    formData.append('side-img', sideI.prop('files')[0])

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function () {
        location.reload();
      },
      error: function (err) {
        $form.find('.msg').text(err.responseJSON.msg);
        btn.button('reset');
      }

    })
  });

  function loadImages(page, limit) {
    var clientId = window.clientId || 0;
    var imgContainer = $('.lightBoxGallery');
    var loadImgData = {
      client: clientId,
      page: page ? page : 0,
      limit: limit ? limit : 0,
    };
    var empty = false;
    var spinner = $('.spinner');

    $.get(loadImagesUrl, loadImgData, function (images) {

      spinner.hide();

      if (!images.length) {
        empty = true;
        var html;

        if (imgContainer.find('img').length > 1) {
          html =
            '<div class="alert alert-info">' +
            'No more images to show' +
            '</div>';
        } else {
          html =
            '<div class="alert alert-info">' +
            'Add before / after pictures of your clients and track their progress! ' +
            'Get started by clicking "Upload" in the right corner!' +
            '</div>';
        }

        imgContainer.append(html);
      }

      images.map((img) => {

        var id = img.id;
        var url = imgUrl + img.name;

        var html =
          '<div class="lightBoxImage">' +
          '<a class="lightBoxImageView" href="' + url + '" title="' + moment(img.date).format('MM/DD/YYYY') + '" data-gallery="">' +
          '<img class="bodyProgressImg" src="' + url + '">' +
          '</a>' +
          '<a class="btn btn-sm btn-danger lightBoxImageDelete" data-bootbox-confirm href="/dashboard/removeImg/' + img.id + '/' + clientId + '">' +
          '<i class="fa fa-trash" aria-hidden="true"></i>' +
          '</a>' +
          '</div>';

        imgContainer.append(html);
      })
    }).then(() => {
      if (!empty) {
        console.log('images length');
        console.log(imgContainer.find('a img').length);
        if (imgContainer.find('a img').length >= 10) {
          var loadMore = '<a href="#"><span class="label label-success load-more-images"><i class="fa fa-rotate-right" /> Load more images</span></a>';
          imgContainer.append(loadMore)
        }
      }
    });
  }


  function initializeChart(selector) {
    var el = document.querySelector(selector);

    if (el) {
      return new Chart(el);
    }
  }

  function dataToArray(value) {
    if (Array.isArray(value)) {
      return value;
    }

    if (typeof value === 'object' && value !== null) {
      return Object.values(value);
    }

    return [];
  }

  if (chartData && chartData.length) {
    chartData = JSON.parse(chartData);
    var datesWeight = dataToArray(chartData['date']['weight']);
    var weightUnits = dataToArray(chartData['weight']);
    var datesMuscleMass = dataToArray(chartData['date']['muscle_mass']);
    var muscleMassUnits = dataToArray(chartData['muscle_mass']);
    var datesCircumference = dataToArray(chartData['date']['circumference']);
    var circumferenceUnits = dataToArray(chartData['circumference']);
    var datesFat = dataToArray(chartData['date']['fat']);
    var fatUnits = dataToArray(chartData['fat']);
    var chartOptions = {
      scaleShowGridLines: true,
      scaleGridLineColor: 'rgba(0,0,0,.05)',
      scaleGridLineWidth: 1,
      bezierCurve: true,
      bezierCurveTension: 0.4,
      pointDot: true,
      pointDotRadius: 4,
      pointDotStrokeWidth: 1,
      pointHitDetectionRadius: 20,
      datasetStroke: true,
      datasetStrokeWidth: 2,
      datasetFill: true,
      responsive: true,
      maintainAspectRatio: false
    }


    function getChartDataset(dataset, label) {
      return {
        label: label,
        data: dataset,
        backgroundColor: 'rgba(40, 149, 241, 0.5)',
        fillColor: 'rgba(40, 149, 241, 0.5)',
        strokeColor: 'rgba(40, 149, 241, 0.7)',
        pointColor: 'rgba(40, 149, 241, 1)',
        pointStrokeColor: '#fff',
        pointHighlightFill: '#fff',
        pointHighlightStroke: 'rgba(40, 149, 241, 1)',
      };
    }

    if (circumferenceUnits.length > 1) {
      new Chart(document.getElementById('circumferenceChart'), {
        type: 'line',
        data: {
          labels: datesCircumference,
          datasets: [getChartDataset(circumferenceUnits, 'Circumference')]
        },
        options: chartOptions
      });
    }

    if (weightUnits.length > 1) {
      new Chart(document.getElementById('weightChart'), {
        type: 'line',
        data: {
          labels: datesWeight,
          datasets: [getChartDataset(weightUnits, 'Weight')]
        },
        options: chartOptions
      });
    }

    if (muscleMassUnits.length > 1) {
      new Chart(document.getElementById('muscleMassChart'), {
        type: 'line',
        data: {
          labels: datesMuscleMass,
          datasets: [getChartDataset(muscleMassUnits, 'Muscle Mass')]
        },
        options: chartOptions
      });
    }

    if (fatUnits.length > 1) {
      new Chart(document.getElementById('fatChart'), {
        type: 'line',
        data: {
          labels: datesFat,
          datasets: [getChartDataset(fatUnits, 'Fat-%')]
        },
        options: chartOptions
      });
    }
  }

  $('body')
    .on('click', '[data-bootbox-confirm]', function (evt) {
      evt.preventDefault();
      var href = $(this).attr('href');
      console.log(href);
      bootbox.confirm('Are you sure?', function (result) {
        console.log(result);
        if (result == true) {
          window.location.href = href;
        }
      });

    });

  function progressHeight() {
    var progressHeight = 0;
    $('.js-progress-item').each(function () {
      if ($(this).height() > progressHeight) {
        progressHeight = $(this).height()
      }
    });
    $('.js-progress-item').find('>div').css('min-height', progressHeight + 'px');
  }

  $(window).on('resize', function () {
    progressHeight();
  });

  progressHeight();
});
