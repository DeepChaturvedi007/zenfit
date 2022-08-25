(function($) {
    $('.replace-comma').on('keyup', function() {
        if ($(this).val().indexOf(',') >= 0) {
            $(this).val($(this).val().replace(',',"."));
            toastr.options.preventDuplicates = true;
            toastr.error('Please use . (dot) instead of, (comma)');
        }
    });

    $('body')
        .on('click', '[data-confirm]', function(evt) {
            evt.preventDefault();
            var href = $(this).attr('href');
            bootbox.confirm('Are you sure?', function(result) {
                if (result) {
                    window.location.href = href;
                }
            });

        });

    let $createTag = $('#createTag');

    let $select = $createTag.selectize({
      delimiter: ',',
      persist: false,
      labelField: 'item',
      valueField: 'item',
      sortField: 'item',
      searchField: 'item',
      closeAfterSelect: true,
      create: function(input) {
        $createTag.data('tags', input);
        return {
          item: input
        }
      },
    });

    $.get('/api/trainer/get-tags-by-user')
      .done(res => {
        res.tags.map(tag => $select[0].selectize.addOption({item: tag}));

        let clientTags = res.tags.filter(tag => tag.client == $('#createTag').data('client'))
          .forEach(tag => {
            const silent = true;
            $select[0].selectize.addItem(tag.title, silent);
          });
      });

    autosize($('textarea'));

    $('.duration-time .input-group.date').datepicker({
        setDate: new Date(),
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'd M yyyy',
        ignoreReadonly: true,
        allowInputToggle: true
    });

    var copyToClipboard = '#copy-to-clipboard';
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

    $('[data-toggle="tooltip"]').tooltip();

    $('#clientInfoForm').each(function() {
        var $this = $(this);
        var $clientPhoto = $this.find('#client_photo');
        var $clientPhotoInput = $this.find('#photo_input');
        var s3 = 'http://zenfit-images.s3-website.eu-central-1.amazonaws.com/before-after-images/client/photo/';
        var measuringSystem = $this.find('input[name="measuringSystem"]');
        var startWeight = $this.find('input[name="startWeight"]');
        var goalWeight = $this.find('input[name="goalWeight"]');
        var height = $this.find('input[name="height"]');
        var metricHeight = $this.find('.measuring-system-metric');
        var imperialHeight = $this.find('.measuring-system-imperial');
        var centimeter = $(metricHeight.find('input')[0]);
        var feet = $(imperialHeight.find('input')[0]);
        var inches = $(imperialHeight.find('input')[1]);
        var primaryGoal = $this.find('select[name="primaryGoal"]');
        var age = $this.find('input[name="age"]');
        var activityLevel = $this.find('select[name="activityLevel"]');
        var tooltipSpan = $this.find('.box-info-title i');
        var caloriesHtml = $this.find('.box-info-left');
        var estimateCalorieNeedText = $this.find('p.estimate-calorie-need');
        var gender = $this.find('input[name="gender"]:checked');
        var metricSystem = $this.find('input[name="measuringSystem"]:checked');
        var estimatedCaloriesBoxP = $this.find('.box-info-left p');
        var alertBoxText = 'Client info succesfully changed.';
        var clientType = $this.find('.client-type label');
        var clientFoodPreferences = $this.find('.meal-area label');
        var endDate = $('#endDate');

        clientType.click(function () {
            setTimeout(function(){ $this.trigger('submit'); }, 2000);
        });

        $clientPhotoInput.on('change', function() {
            $this.trigger('submit');
        });

        measuringSystem.change(function() {
            if ($(this).val() == 1) {
                toMetricWeight(startWeight);
                toMetricWeight(goalWeight);
                metricHeight.prop('hidden', false);
                imperialHeight.prop('hidden', true);
            } else {
                toImperialWeight(startWeight);
                toImperialWeight(goalWeight);
                metricHeight.prop('hidden', true);
                imperialHeight.prop('hidden', false);
            }
        });

        metricHeight.find('input').change(function () {
            var cm = 0.032808;
            var fullFeet = centimeter.val() * cm;
            var feetValue = Math.trunc(fullFeet);
            var inchValue = (fullFeet - feetValue) * 12;

            feet.val(feetValue);
            inches.val(precisionRound(inchValue, 1));
            height.val($(this).val());
        });

        imperialHeight.find('input').change(function () {
            var cm = 0.39370;
            var fullInches = feet.val() * 12 + +inches.val();

            centimeter.val(precisionRound(fullInches / cm, 1));
            height.val(precisionRound(fullInches / cm, 1));
        });

        primaryGoal.change(function () {
            primaryGoal.val($(this).val());
            //alertBoxText = alertBoxText + 'Note that Client\'s Estimated Calorie Need can be affected by these changes.';
        });

        let debouncedTimer = null;
        $this.on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);

            var photoInput = $form.find('#photo_input');
            if(!photoInput.val()){
                photoInput.attr('disabled', true);
            }
            var data = new FormData($form[0]);

            //get tags
            let tags = JSON.stringify($("#createTag").val().split(','));
            data.append('tags', tags);
            //end tags

            data.append('feet', feet.val());
            data.append('inches', inches.val());

            toastr.options.preventDuplicates = true;

            fetch($form.attr('action'), {
                credentials: 'same-origin',
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: data,
            }).then(function(data) {
                return data.json();
            }).then(function(data) {
                toastr.success(alertBoxText);

                endDate.text(data.client.endDate ? data.client.endDate : '-');

                if (data.client.photo) {
                    $clientPhoto.each(function() {
                        var src = data.client.photo;
                        this.src = s3 + src + '?"' + new Date().getTime();
                    });
                    $clientPhotoInput.val('');
                }
                photoInput.attr('disabled', false);
            }).catch(function() {
                toastr.error('Client info save failed.');
            });
        }).on('change', function() {
            // Timeouts was added due to fact that the few things like tags populate an a form in a cycle.
            // That specific initiates many "change" events and leads to submitting wrong data.
            clearTimeout(debouncedTimer);
            debouncedTimer = setTimeout(() => {
                gender = $this.find('input[name="gender"]:checked');
                metricSystem = $this.find('input[name="measuringSystem"]:checked');
                var weight = metricSystem.val() == 1 ? startWeight.val() : precisionRound(startWeight.val(), 1) * 0.45359237;

                var estimateCalorieNeedString = estimateCalorieNeed(
                  gender.val(), age.val(), height.val(), weight, primaryGoal.val(), activityLevel.val()
                );
                if (estimateCalorieNeedString) {
                    tooltipSpan.show();
                    caloriesHtml.hide();
                    var name = $this.find('input[name="name"]').val();
                    estimateCalorieNeedText.html(
                      estimateCalorieNeedString + " in order to calculate estimated calorie need for " + name + "."
                    );
                } else {
                    tooltipSpan.hide();
                    caloriesHtml.show();
                    var calories = bmrCalc(
                      gender.val(), age.val(), height.val(), weight, activityLevel.val()
                    );
                    calories = precisionRound(calories, 1);
                    estimateCalorieNeedText.html('BMR: ' + kcalReturn(calories, 0) + 'kcal');
                    estimatedCaloriesBoxHtml(calories, estimatedCaloriesBoxP);
                    estimatedCaloriesBoxP.each(function () {
                        var $this = $(this);
                        if ($this.data('primaryGoal') == primaryGoal.val()) {
                            $this.addClass('selected');
                        } else {
                            $this.removeClass('selected');
                        }
                    });
                }
                $(this).trigger('submit');
            }, 200);
        });

    });

    var kg = 0.45359237;
    function toMetricWeight(el) {
        var val = precisionRound(el.val() * kg, 1);
        el.val(val ? val : '');
        el.parent().find('div.unit').html('kg');
    }

    function toImperialWeight(el) {
        var val = precisionRound(el.val() / kg, 1);
        el.val(val ? val : '');
        el.parent().find('div.unit').html('lbs');
    }

    function precisionRound(number, precision) {
        var factor = Math.pow(10, precision);
        return Math.round(number * factor) / factor;
    }

    function bmrCalc(gender, age, height, startWeight, activityLevel) {
        var bmr;
        var calories;
        if (gender == 1) {
            bmr = 10 * startWeight + 6.25 * height - 5 * age - 161;
        } else {
            bmr = 10 * startWeight + 6.25 * height - 5 * age + 5;
        }
        if (activityLevel == 1) {
            calories = bmr * 1.2;
        } else if (activityLevel == 2) {
            calories = bmr * 1.374874372;
        } else if (activityLevel == 3) {
            calories = bmr * 1.550251256;
        } else if (activityLevel == 4) {
            calories = bmr * 1.725125628;
        } else if (activityLevel == 5) {
            calories = bmr * 1.9;
        } else {
            calories = bmr;
        }

        return calories;
    }

    function estimateCalorieNeed(gender, age, height, startWeight, primaryGoal, activityLevel) {
        var genderString = '';
        var ageString = '';
        var heightString = '';
        var startWeightString = '';
        var primaryGoalString = '';
        var activityLevelString = '';

        if (!gender) {
            genderString = 'Input Gender';
        }
        if (!age) {
            if (!gender && height && startWeight && primaryGoal && activityLevel) {
                ageString = ageString + ' and';
            } else if(gender) {
                ageString = ageString + 'Input';
            } else {
                ageString = ageString + ',';
            }
            ageString = ageString + ' Age';
        }
        if (!height) {
            if ((!gender || !age) && startWeight && primaryGoal && activityLevel) {
                heightString = heightString + ' and';
            } else if(gender && age) {
                heightString = heightString + 'Input';
            } else {
                heightString = heightString + ',';
            }
            heightString = heightString + ' Height';
        }
        if (!startWeight) {
            if ((!gender || !age || !height) && primaryGoal && activityLevel) {
                startWeightString = startWeightString + ' and';
            } else if(gender && age && height) {
                startWeightString = startWeightString + 'Input';
            } else {
                startWeightString = startWeightString + ',';
            }
            startWeightString = startWeightString + ' Current Weight';
        }
        if (!primaryGoal) {
            if ((!gender || !age || !height || !startWeight ) && activityLevel) {
                primaryGoalString = primaryGoalString + ' and';
            } else if(gender && age && height && startWeight) {
                primaryGoalString = primaryGoalString + 'Input';
            } else {
                startWeightString = startWeightString + ',';
            }
            primaryGoalString = primaryGoalString + ' Primary Goal';
        }
        if (!activityLevel) {
            if (!gender || !age || !height || !startWeight || !primaryGoal) {
                activityLevelString = activityLevelString + ' and';
            } else if(gender && age && height && startWeight && primaryGoal) {
                activityLevelString = activityLevelString + 'Input';
            }
            activityLevelString = activityLevelString + ' Activity Level';
        }
            return genderString + ageString + heightString + startWeightString + primaryGoalString + activityLevelString;
    }

    function kcalReturn(calories, primaryGoalChooseNumber) {
        var caloriesRound = precisionRound(calories + primaryGoalChooseNumber, 0);
        return caloriesRound.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    function estimatedCaloriesBoxHtml(calories, elements) {
        var loose500kcal = kcalReturn(calories, -500);
        var loose1000kcal = kcalReturn(calories, -1000);
        var maintain = kcalReturn(calories, 0);
        var gain500kcal = kcalReturn(calories, 500);
        var gain1000kcal = kcalReturn(calories, 1000);
        $(elements.get(0)).html('Client needs '+ loose1000kcal + ' Calories/day to lose 1 kg per week.');
        $(elements.get(1)).html('Client needs '+ loose500kcal + ' Calories/day to lose 0.5 kg per week.');
        $(elements.get(2)).html('Client needs '+ maintain + ' Calories/day to maintain weight.');
        $(elements.get(3)).html('Client needs '+ gain500kcal + ' Calories/day to gain 0.5 kg per week.');
        $(elements.get(4)).html('Client needs '+ gain1000kcal + ' Calories/day to gain 1 kg per week.');
        $(elements.get(5)).html('Client needs '+ loose1000kcal + ' Calories/day to lose 2 lb per week.');
        $(elements.get(6)).html('Client needs '+ loose500kcal + ' Calories/day to lose 1 lb per week.');
        $(elements.get(7)).html('Client needs '+ maintain + ' Calories/day to maintain weight.');
        $(elements.get(8)).html('Client needs '+ gain500kcal + ' Calories/day to gain 1 lb per week.');
        $(elements.get(9)).html('Client needs '+ gain1000kcal + ' Calories/day to gain 2 lb per week.');
    }
}(jQuery));
