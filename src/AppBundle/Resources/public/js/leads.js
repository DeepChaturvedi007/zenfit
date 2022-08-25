(function($) {
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-left",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "4000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    var emptyTable =
        '<table class="table actions-on-hover">' +
            '<thead>' +
                '<tr>' +
                    '<th class="hidden-xs">Name</th>' +
                    '<th class="hidden-xs">Created</th>' +
                    '<th class="hidden-xs">E-Mail</th>' +
                    '<th class="hidden-xs">Phone</th>' +
                    '<th class="hidden-xs">Status</th>' +
                    '<th class="hidden-xs" width="150">Contact Again?</th>' +
                '</tr>' +
            '</thead>' +
            '<tbody>' +
            '</tbody>' +
        '</table>'
    ;

    var LEAD_NEW = 1;
    var LEAD_IN_DIALOG = 2;
    var LEAD_WON = 3;
    var LEAD_LOST = 4;
    var LEAD_PAYMENT_WAITING = 5;

    var leadsCountData = $('#leadsCountData');
    var allCount = $('.all-count');
    var newCount = $('.new-count');
    var inDialogCount = $('.in-dialog-count');
    var lostCount = $('.lost-count');
    var paymentWaitingCount = $('.payment-waiting-count');

    var modals = {
        addNewLeadModal: $('#addNewLeadModal'),
        deleteLead: $('#deleteLead'),
        preventLeadChanges: $('#preventLeadChanges'),
        leadWon: $('#leadWon'),
        clientAdded: $('#clientAdded'),
        sendEmailToClient: $('#sendEmailToClient'),
        resendPayment: $('#resendPayment'),
        readMore: $('#readMoreQuestionnaire, #readMoreTrackProgress, #readMoreDurationTime, #readMorePayment, #readMoreType')
    };
    var newClient = null;
    var queue = null;
    var isMessageSent = false;
    var sendTextarea = modals.sendEmailToClient.find('.editable');
    var textareaDefault = sendTextarea.html();
    var sendMessage = sendTextarea.html();
    var setDefaultInfo = modals.sendEmailToClient.find('.modal-footer small');
    var currentModal = null;

    var copyToClipboard = '#copy-payment-link';
    var clipboard = new Clipboard(copyToClipboard);
    clipboard.on('success', function() {
        toastr.success('Text copied!');
    });

    $(copyToClipboard).on('click', function () {
        var input = $($(this).data('clipboardTarget'));
        input.focus();
        input.on('focus', function(input) {
            input.each(function () {
                var input = this;
                setTimeout(function () {
                    input.selectionStart = 0;
                    input.selectionEnd = input.value.length;
                }, 100);
            });
        });
    });



    let $createTag = $('#createTagLead');

    let $select = $createTag.selectize({
      delimiter: ',',
      persist: false,
      labelField: 'item',
      valueField: 'item',
      sortField: 'item',
      searchField: 'item',
      create: function(input) {
        $createTag.data('tags', input);
        return {
          item: input
        }
      },
    });

    $.get('/api/trainer/get-tags-by-user')
      .done(res => {
        let userTags = res.tags.map(tag => {
          return { item: tag.title };
        });

        let defaultTags = res.defaultTags.map(tag => {
          return { item: tag };
        });

        let allTags = defaultTags.concat(userTags);

        $select[0].selectize.addOption(allTags);

        let clientTags = res.tags.filter(tag => tag.client == $('#createTag').data('client'))
          .forEach(tag => {
            const silent = true;
            $select[0].selectize.addItem(tag.title, silent);
          });
      });

    $('body').on('click', '.lead-status', function() {
      let status = $(this).data('status');
      if (status == 'won') {
        $('.lead-tag').show();
      } else {
        $('.lead-tag').hide();
      }
    });


    // Lead Info Modal
    var lead;
    var leadData;
    var leadDataNew;
    var leadId;
    var leadStatus;
    var statusRadio = modals.addNewLeadModal.find(".area input[type=radio]");
    var newLeadCheckbox = modals.addNewLeadModal.find(".checkbox input[type=checkbox]");
    var form = modals.addNewLeadModal.find('form');
    var newLeadPicker = modals.addNewLeadModal.find('#followUpAt');
    var statusArea = modals.addNewLeadModal.find('.area');
    var statusView = statusArea.html();
    var paymentStatusView =
        '<div class="col lead-status" data-status="payment-waiting" data-id="5">' +
            '<label for="status-5" class="btn btn-lead current" disabled>' +
                'Payment Waiting' +
                '<input type="radio" id="status-5" disabled>' +
                '<input type="hidden" name="status" value="5">'+
            '</label>' +
        '</div>'
    ;

    $('select.visible-status').on('change', function () {
        location.href = $(this).val();
    });

    newLeadPicker.datepicker({
        todayBtn: 'linked',
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        todayHighlight: true,
        autoclose: true,
        format: 'd M yyyy',
        ignoreReadonly: true,
        allowInputToggle: true
    });
    newLeadPicker.datepicker('setDate', new Date());

    newLeadCheckbox.change(function () {
        var $this = $(this);
        if ($this.is(':checked')) {
            $this.val(1);
            $this.closest('div').find('.box-hidden').show();
        } else {
            $this.val(0);
            $this.closest('div').find('.box-hidden').hide();
        }
    });

    $('.table-container .in-dialog-tooltip i').on('click', function (e) {
        e.stopPropagation();
    });

    $('.in-dialog-tooltip i').each(function () {
        if($(this).closest('tr').data('inDialog')) {
            $(this).tooltip({
                placement: 'bottom',
                title: $(this).closest('tr').data('dialogMessage')
            });
        }
    });

    modals.addNewLeadModal.find('.is-won').tooltip({
        placement: 'bottom',
        title: 'You can’t change info, when lead status is WON. Instead edit client info from Client Overview.'
    });

    modals.addNewLeadModal.on('show.bs.modal', function (e) {
        if($(e.relatedTarget).data()) {
            lead = $(e.relatedTarget);
            leadData = lead.data();
            leadId = leadData.id;
            leadStatus = leadData.status;
            if (!leadData.viewed) {
                $.post('/dashboard/makeLeadViewed/' + leadId).done(function () {
                    $(e.relatedTarget).removeClass('new');
                });
            }
            formPrepare(leadData);
        }
    });

    modals.addNewLeadModal.on('hidden.bs.modal', function () {
        isSending = false;
        modals.addNewLeadModal.find('div').removeClass('alert alert-danger');
        modals.addNewLeadModal.find('.notify').html('');
        statusRadio.map(function () {
            modals.addNewLeadModal.find('.area label').removeClass('current');
        });
        newLeadCheckbox.map(function () {
            var $this = $(this);
            $this.prop('checked', false);
            $this.closest('div').find('.box-hidden').hide();
        });
        modals.addNewLeadModal.find('textarea').val('');
        newLeadPicker.datepicker('setDate', new Date());
    });

    var isSending = false;
    modals.addNewLeadModal.find('button.is-won').on('click', function (e) {
        e.preventDefault();
        if(!isSending){
            form.trigger('submit');
        }
        isSending = true;
    });

    var newLead = false;
    form.on('submit', function (e) {
        e.preventDefault();
        let $btn = $('.add-lead-btn');
        if(!(leadStatus == LEAD_WON)){
            $btn.button('loading');
            var url = leadId ? '/dashboard/leadSave/' + leadId : form.attr('action');
            var $this = $(this);
            var status = $('body').find(".lead-status > label.current").parent().data('id');

            let data = {
              name: $('#leadName').val(),
              email: $('#leadEmail').val(),
              phone: $('#leadPhone').val(),
              status: status,
              followUp: $('#followUp').val(),
              followUpAt: $('#followUpAt').val(),
              inDialog: $('#inDialog').val(),
              dialogMessage: $('[name=dialogMessage]').val(),
              tags: $select[0].selectize.items,
            }
            console.log(data);
            $.post(url, data).done(function (data) {
                isSending = false;
                //if status is won
                console.log(data)
                if (status == 3) {
                    newLead = leadId ? false : true;
                    queue = data.queue;
                    newClient = data.client;
                    leadDataNew = data.lead;
                    modals.addNewLeadModal.modal('hide');
                    modalClientAdded(data);
                } else {
                    // location.reload();
                }
            }).fail(function (err) {
                isSending = false;
                $btn.button('reset');
                var response = JSON.parse(err.responseText);
                modals.addNewLeadModal.find('.notify').addClass('alert alert-danger').html(response.reason);
                $this.find(response.id).closest('div').addClass('alert alert-danger');
                $this.find(response.okId).closest('div').removeClass('alert alert-danger');
            });
        }
    });

    function modalClientAdded(data) {
        modals.clientAdded
            .find('form')
            .removeAttr('action')
            .end()
            .find('input#client')
            .val(data.client)
            .end()
            .find('input#lead')
            .val(data.lead)
            .end()
            .find('input#queue')
            .val(data.queue)
            .end()
            .find('input#path')
            .val(data.path)
            .end()
            .modal();
    }

    function formPrepare(data) {
        var title = data.title ? data.title : 'Add New Lead';
        var created = data.created ? data.created : '';
        var btnName = data.btnName ? data.btnName : 'Add Lead';
        var clientExists = data.client;
        if(leadId) {
            modals.addNewLeadModal.find('.stealthy').show();
            if (clientExists == true) {
              modals.addNewLeadModal.find('#leadQuestionnaire').show();
            } else {
              modals.addNewLeadModal.find('#leadQuestionnaire').hide();
            }
        } else {
            modals.addNewLeadModal.find('#leadQuestionnaire').hide();
            modals.addNewLeadModal.find('.stealthy').hide();
            modals.addNewLeadModal.find('.is-won').tooltip('disable').css('cursor', 'pointer');
        }

        modals.addNewLeadModal.find('.modal-title').html(title);
        modals.addNewLeadModal.find('.created-date').html(created);
        form.find('#leadName').val(data.name);
        form.find('#leadEmail').val(data.email);
        form.find('#leadPhone').val(data.phone);
        form.find('#leadQuestionnaire').data('id',data.id);
        if (data.status == LEAD_PAYMENT_WAITING) {
            statusArea.html(paymentStatusView);
            modals.addNewLeadModal
                .find('.is-won')
                .tooltip('disable')
                .css('cursor', 'pointer');
        } else {
            statusArea.html(statusView);
            statusRadio = modals.addNewLeadModal.find(".area input[type=radio]");
            statusRadio.filter(function () {
                var $this = $(this);
                if ($this.val() == data.status) {
                    $this.prop('checked', true);
                    if($this.val() == LEAD_WON) {
                        modals.addNewLeadModal
                            .find('.is-won')
                            .tooltip('enable')
                            .css('cursor', 'not-allowed');
                    } else {
                        modals.addNewLeadModal
                            .find('.is-won')
                            .tooltip('disable')
                            .css('cursor', 'pointer');
                    }
                    return $this;
                }
            }).closest('label').addClass('current');
        }

        statusRadio.on('change', function () {
            var $this = $(this);
            statusRadio.map(function () {
                if ($this.val() == $(this).val()) {
                    $(this).closest('label').addClass('current');
                } else {
                    $(this).closest('label').removeClass('current');
                }
            });
        });

        if (data.followUp) {
            modals.addNewLeadModal.find('#followUp').prop('checked', true);
            newLeadPicker.datepicker('setDate', new Date(data.followUpAt));
        }
        if (data.inDialog) {
            modals.addNewLeadModal.find('#inDialog').prop('checked', true);
            modals.addNewLeadModal.find('textarea').val(data.dialogMessage);
        } else {
            modals.addNewLeadModal.find('textarea').val('');
        }
        newLeadCheckbox.trigger('change');
        form.find('.btn-success').html(btnName);
    }

    // Lead Delete Modal
    modals.deleteLead.on('show.bs.modal', function () {
        modals.addNewLeadModal.modal('hide');
    });

    modals.deleteLead.on('hidden.bs.modal', function () {
        modals.addNewLeadModal.modal('show');
    });

    modals.deleteLead.find('#delete-lead').on('click', function () {
        $.post('/dashboard/deleteLead/' + leadId).done(function () {
            location.reload();
        });
    });



    // Resend Payment Modal
    var tr;
    var sentInfo = modals.resendPayment.find('#sent-info').html();
    $('[data-target="#resendPayment"]').on('click', function (e) {
        var $this = $(this);
        tr = $this.closest('tr');
        e.stopPropagation();
        $($this.data('target')).modal('show');
        modals.resendPayment.find('[name="email"]').val(tr.data('email'));
        modals.resendPayment.find('[name="resendPayment"]').val($this.data('checkout'));
        payment = tr.data('paymentId');
        paymentAt = tr.data('paymentAt');
        sendMessage = textareaDefault
            .replace('[client]', tr.data('name'))
            .replace('[checkout]', $this.data('checkout'));
        modals.resendPayment.find('#sent-info').html(sentInfo.replace('[sent-info]', calculateDays(paymentAt)));
    });

    modals.resendPayment.each(function () {
        var $this = $(this);
        $this.find('#send-payment').on('click', function () {
            var data = {
                textarea: sendMessage,
                email: tr.data('email')
            };
            $.post($(this).data('path') + payment, data).done(function (data) {
                toastr.success('Payment e-mail successfully resent.');
                tr.data('paymentAt', data.date.date);
                $this.modal('hide');
            });
        });
    });

    // functions
    function urlify(text) {
        text = text.replace(/<a href="(.*)">/g, '');
        text = text.replace(/<\/a>/g, '');

        var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;

        return text.replace(urlRegex, '<a href="$1">$1</a>')
    }

    function unescapeHtml(safe) {
        return safe.replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'");
    }

    function arrayIndexOf(array, rx) {
        for (var i in array) {
            if (array[i].toString().match(rx)) {
                return i;
            }
        }
        return -1;
    }

    function statusChangesPrevent() {
        if (isMessageSent == false && newClient) {
            modals.preventLeadChanges.modal('show');
        }
    }

    function leadWonFormClearing() {
        leadWonCheckbox.map(function () {
            var $this = $(this);
            if (!$this.is(':disabled')) {
                $this.prop('checked', false);
            }
        });
        modals.leadWon.find('select').each(function () {
            var $this = $(this);
            $this.val($this.find('option:first').val());
        });

        modals.leadWon.find('input#signUpFee, input#monthlyAmount').val('')
            .end()
            .find('div').removeClass('alert alert-danger')
            .end()
            .find('.notify').html('');
        leadWonPicker.datepicker('setDate', new Date());
    }

    function statusOverviewChange() {
        var name = leadDataNew.name;
        if (leadDataNew.inDialog) {
            name = leadDataNew.name + '<span class="in-dialog-tooltip"><i class="material-icons">info_outline</i></span>';
        }
        if (leadStatus == LEAD_NEW) {
            leadsCountData.data('newCount', leadsCountData.data('newCount') - 1);
            newCount.html('New (' + leadsCountData.data('newCount') + ')');
        } else if (leadStatus == LEAD_IN_DIALOG) {
            leadsCountData.data('inDialogCount', leadsCountData.data('inDialogCount') - 1);
            inDialogCount.html('In Dialog (' + leadsCountData.data('inDialogCount') + ')');
        } else if (leadStatus == LEAD_LOST) {
            leadsCountData.data('lostCount', leadsCountData.data('lostCount') - 1);
            lostCount.html('Lost (' + leadsCountData.data('lostCount') + ')');
        }
        lead.data('status', LEAD_PAYMENT_WAITING);
        lead.data('name', leadDataNew.name);
        lead.data('email', leadDataNew.email);
        lead.data('phone', leadDataNew.phone);
        lead.data('followUp', leadDataNew.followUp);
        lead.data('inDialog', leadDataNew.inDialog);
        lead.data('dialogMessage', leadDataNew.dialogMessage);
        lead.data('paymentId', payment);
        lead.data('paymentAt', paymentAt);
        lead.find('.status-to-change')
            .removeClass('lost')
            .html('Payment Waiting. <a class="read-more" data-checkout="' + checkout + '" data-target="#resendPayment">Resend.</a>');
        lead.find('.lead-name').html(name);
        lead.find('.phone-to-change').html(leadDataNew.phone);
        lead.find('.email-to-change').html(leadDataNew.email);
        if (leadDataNew.followUpAt) {
            lead.data('followUpAt', leadDataNew.followUpAt.date);
            lead.find('.contact-again-to-change').html(dateDiff(leadDataNew.followUpAt.date));
        }
        leadsCountData.data('paymentWaitingCount', leadsCountData.data('paymentWaitingCount') + 1);
        paymentWaitingCount.html('Payment Waiting (' + leadsCountData.data('paymentWaitingCount') + ')');
        leadsCountData.data('allCount', leadsCountData.data('allCount') + 1);
        allCount.html('All Leads (' + leadsCountData.data('allCount') + ')');

        lead.find('.in-dialog-tooltip i').tooltip({
            placement: 'bottom',
            title: leadDataNew.dialogMessage
        }).on('click', function (e) {
            e.stopPropagation();
        });
        lead.find('[data-target="#resendPayment"]').on('click', function (e) {
            var $this = $(this);
            tr = $this.closest('tr');
            e.stopPropagation();
            $($this.data('target')).modal('show');
            modals.resendPayment.find('[name="email"]').val(tr.data('email'));
            modals.resendPayment.find('[name="resendPayment"]').val($this.data('checkout'));
            payment = tr.data('paymentId');
            paymentAt = tr.data('paymentAt');
            sendMessage = sendTextarea.val()
                .replace('[client]', tr.data('name'))
                .replace('[checkout]', $this.data('checkout'));
            modals.resendPayment.find('#sent-info').html(sentInfo.replace('[sent-info]', calculateDays(paymentAt)));
        });
    }

    function dateDiff(date) {
        var todayDate = new Date();
        todayDate.setHours(0, 0, 0, 0);
        var diffDate = new Date(date.split(' ')[0].replace(/-/g, '/'));
        var difference = Math.floor(todayDate - diffDate);
        var daysDivisor = 1000 * 60 * 60 * 24;
        var days = parseFloat((difference / daysDivisor).toFixed());

        if (days > 0) {
            if (days === 1) {
                return 'Past (' + days + ' day)';
            } else {
                return 'Past (' + days + ' days)';
            }
        } else if (days < 0) {
            days = Math.abs(days);
            if (days === 1) {
                return 'In ' + days + ' day';
            } else {
                return 'In ' + days + ' days';
            }
        } else {
            return 'Today';
        }
    }

    function calculateDays(endDate) {
        var startDate = new Date();
        var endDate = new Date(endDate);
        var start_date = moment(startDate, 'YYYY-MM-DD HH:mm:ss');
        var end_date = moment(endDate, 'YYYY-MM-DD HH:mm:ss');
        var duration = moment.duration(end_date.diff(start_date));
        var diff = duration.humanize();
        return diff;
    }

    function newWonLeadPrepend(lead) {
        var createdAt = getFormattedDate(lead.createdAt.date);
        var followUpAt = '';
        var followUpAtDiff = '-';
        var name = lead.name;
        if (lead.followUpAt) {
            followUpAt = getFormattedDate(lead.followUpAt.date);
            followUpAtDiff = dateDiff(lead.followUpAt.date);
        }
        if (lead.inDialog) {
            name = lead.name + '<span class="in-dialog-tooltip"><i class="material-icons">info_outline</i></span>';
        }

        var emptyLeads = $('.empty-leads');
        emptyLeads.each(function () {
            $(this).removeClass('container empty-leads text-center').addClass('table-container').html(emptyTable);
        });

        $('table tbody').prepend(
            '<tr data-toggle="modal"' +
                'data-btn-name="Save"' +
                'data-id="' + lead.id + '"' +
                'data-title="Client info"' +
                'data-name="' + lead.name + '"' +
                'data-email="' + lead.email + '"' +
                'data-phone="' + lead.phone + '"' +
                'data-status="' + LEAD_PAYMENT_WAITING + '"' +
                'data-follow-up="' + lead.followUp + '"' +
                'data-follow-up-at="' + followUpAt + '"' +
                'data-in-dialog="' + lead.inDialog + '"' +
                'data-payment-id="' + payment + '"' +
                'data-payment-at="' + paymentAt + '"' +
                'data-dialog-message="' + lead.dialogMessage + '"' +
                'data-created="Created: ' + createdAt + '"' +
                'data-target="#addNewLeadModal">' +
                '<td width="25%">' +
                    '<p class="lead-name">' +
                        name +
                    '</p>' +
                    '<p class="hidden-sm hidden-md hidden-lg email-to-change">' +
                        lead.email +
                    '</p>' +
                    '<p class="hidden-sm hidden-md hidden-lg phone-to-change phone">' +
                        lead.phone +
                    '</p>' +
                    '<p class="hidden-sm hidden-md hidden-lg status-to-change">' +
                        'Payment Waiting. <a class="read-more" data-checkout="' + checkout + '" data-target="#resendPayment">Resend.</a>' +
                    '</p>' +
                    '<span class="contact-again hidden-sm hidden-md hidden-lg">' +
                        '<i class="material-icons">keyboard_arrow_right</i>' +
                    '</span>' +
                '</td>' +
                '<td class="no-wrap hidden-xs">' + createdAt + '</td>' +
                '<td width="25%" class="no-wrap hidden-xs email-to-change">' + lead.email + '</td>' +
                '<td class="hidden-xs phone-to-change">' + lead.phone + '</td>' +
                '<td class="no-wrap hidden-xs status-to-change">Payment Waiting. <a class="read-more" data-checkout="' + checkout + '" data-target="#resendPayment">Resend.</a></td>' +
                '<td class="table-actions no-wrap hidden-xs contact-again-to-change">' +
                    followUpAtDiff +
                    '<span class="contact-again"><i class="material-icons">keyboard_arrow_right</i></span>' +
                '</td>' +
            '</tr>'
        );
        if (lead.inDialog) {
            $('tr[data-id="' + lead.id + '"] .in-dialog-tooltip i').tooltip({
                placement: 'bottom',
                title: lead.dialogMessage
            }).on('click', function (e) {
                e.stopPropagation();
            });
        }
        $('tr[data-id="' + lead.id + '"] [data-target="#resendPayment"]').on('click', function (e) {
            var $this = $(this);
            tr = $this.closest('tr');
            e.stopPropagation();
            $($this.data('target')).modal('show');
            modals.resendPayment.find('[name="email"]').val(tr.data('email'));
            modals.resendPayment.find('[name="resendPayment"]').val($this.data('checkout'));
            payment = tr.data('paymentId');
            paymentAt = tr.data('paymentAt');
            sendMessage = sendTextarea.val()
                .replace('[client]', tr.data('name'))
                .replace('[checkout]', $this.data('checkout'));
            modals.resendPayment.find('#sent-info').html(sentInfo.replace('[sent-info]', calculateDays(paymentAt)));
        });
        payment = 'payment';
    }

    function getFormattedDate(date) {
        var monthNames = [
            'Jan', 'Feb', 'Mar',
            'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep',
            'Oct', 'Nov', 'Dec'
        ];

        var date = new Date(date.split(' ')[0].replace(/-/g, '/'));
        var day = ('0' + date.getDate()).slice(-2);
        var monthIndex = date.getMonth();
        var year = date.getFullYear();

        return monthNames[monthIndex] + ' ' + day + ', ' + year;
    }
})(jQuery);
