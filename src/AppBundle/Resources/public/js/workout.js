/**
 * Polyfill
 */

if (!NodeList.prototype.toArray) {
  NodeList.prototype.toArray = function() {
    return [].slice.call(this);
  };
}

if (!NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

if (!String.prototype.trim) {
  String.prototype.trim = function() {
    return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
  };
}

if (window.Element && !Element.prototype.closest) {
  Element.prototype.closest =
    function(s) {
      var matches = (this.document || this.ownerDocument).querySelectorAll(s),
        i,
        el = this;
      do {
        i = matches.length;
        while (--i >= 0 && matches.item(i) !== el) {};
      } while ((i < 0) && (el = el.parentElement));
      return el;
    };
}

// Source: https://github.com/Alhadis/Snippets/blob/master/js/polyfills/IE8-child-elements.js
if(!('nextElementSibling' in document.documentElement)) {
  Object.defineProperty(Element.prototype, 'nextElementSibling', {
    get: function() {
      var e = this.nextSibling;
      while(e && 1 !== e.nodeType) {
        e = e.nextSibling;
      }
      return e;
    }
  });
}

// from:https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
(function (arr) {
  arr.forEach(function (item) {
    item.remove = item.remove || function () {
      this.parentNode.removeChild(this);
    };
  });
})([Element.prototype, CharacterData.prototype, DocumentType.prototype]);

if (typeof Object.assign != 'function') {
  Object.assign = function(target, varArgs) { // .length of function is 2
    'use strict';
    if (target == null) { // TypeError if undefined or null
      throw new TypeError('Cannot convert undefined or null to object');
    }

    var to = Object(target);

    for (var index = 1; index < arguments.length; index++) {
      var nextSource = arguments[index];

      if (nextSource != null) { // Skip over if undefined or null
        for (var nextKey in nextSource) {
          // Avoid bugs when hasOwnProperty is shadowed
          if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
            to[nextKey] = nextSource[nextKey];
          }
        }
      }
    }
    return to;
  };
}


(function($, global) {
  var IS_TOUCH = (('ontouchstart' in window)
    || (navigator.maxTouchPoints > 0)
    || (navigator.msMaxTouchPoints > 0));
  var IS_FIREFOX = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
  var COMMENT_DROP_SET_RE = /^this is a drop set/i;
  var POPOVER_PLAN_CHART = {
    placement: 'right',
    html: true,
    trigger: 'hover',
    content: function() {
      return (
        '<div class="plan-popover-chart">' +
        '<canvas width="60" height="60"></canvas>' +
        '</div>' +
        '<div class="plan-popover-list"></div>'
      );
    },
    title: function() {
      return this.textContent;
    }
  };
  var POPOVER_PRODUCT_AMOUNTS = {
    placement: 'right',
    html: true,
    trigger: 'hover',
    content: function() {
      return (
        '<div class="plan-popover-list"></div>'
      );
    },
    title: function() {
      return this.textContent;
    }
  };

  /**
   * Temp
   */

  var planChart;
  var planCharDelay = 0;

  /**
   * Plans
   */

  var Plans = {
    WORKOUT: 'workout',
    MEAL: 'meal',
  };

  Plans.type = null;
  Plans.sortables = {};
  Plans.dragable = null;
  Plans.dragableItems = [];
  Plans.options = {
    saveUrl: '/',           // saveUrl
    createPlanUrl: '/',     // addWorkoutDayUrl
    deletePlanUrl: '/',     // deleteWorkoutDayUrl
    deleteEntityUrl: '/',   // deleteUrl,
    previewEntityUrl: '/',  // path
    checkOutMobileAppModal: '/', //path
    isTemplate: false,
    executeItemLog: '/'
  }

  /**
   * @return {boolean}
   */
  Plans.isWorkout = function() {
    return this.type === Plans.WORKOUT;
  };

  Plans.isMeal = function() {
    return this.type === Plans.MEAL;
  };

  /**
   * @param {Element} node
   * @return {boolean}
   */
  Plans.isSideBarEntities = function(node) {
    return node.classList.contains('sidebar-exercises');
  };

  /**
   * @param {number} weight
   * @param {number} kcal
   * @return {number}
   */
  Plans.calcKcal = function(weight, kcal) {
    return (kcal * weight) / 100;
  }

  /**
   * @TODO Save plans
   */
  Plans.save = function() {
    var data = this.getDeskData();
    var query = {
      type: 'POST',
      url: this.options.saveUrl,
      data: {
        array: data
      },
      success: function(response) {
        if (Plans.isMeal()) {
          response.plans.forEach(Plans.updatePlanSidebar);
          response.meals.forEach(function(meal) {
            var planNode = document.querySelector('#plan_' + meal.id);
            var entityNodes = planNode.querySelectorAll('.exercise-item');

            Plans.updateMealTotals(meal);

            meal.products.forEach(function(id, index) {
              if (entityNodes[index]) {
                entityNodes[index].dataset.entityId = id;
              }
            });
          });
        } else {
          response.forEach(function(plan) {
            var planNode = document.querySelector('#day_' + plan.workout_day_id);
            var entityNodes = Plans.getPlanEntityNodes(planNode);

            plan.workouts.forEach(function(entity) {
              var index = Number(entity.position) - 1;
              var entityNode = entityNodes[index].entity;
              var childrenNodes = entityNodes[index].children;

              entityNode.dataset.workoutId = entity.workout_id;

              entity.sub_workouts.forEach(function(superEntity) {
                var childrenIndex = Number(superEntity.position) - 1;
                childrenNodes[childrenIndex].dataset.workoutId = superEntity.workout_id;
              });
            });
          });
        }

        $(document.body).trigger('plans.updated', 'save');
      }
    };
    var exercises = this.getDeskData();
    var job = function(next) {
      $.ajax(query)
        .done(function() {
          var toastrTitlePrefix;

          toastrTitlePrefix = Plans.isWorkout() ? 'Workout Plan' : 'Meal Plan';

          var saveDescription = '';

          if (Plans.isWorkout()) {
            if (Plans.options.isTemplate) {
              saveDescription = 'Workout Plan is ready to be assigned to your clients!'
            } else {
              saveDescription = 'Changes will appear automatically in your client Zenfit Mobile App.'
            }
          } else if (Plans.isMeal()) {
            if (Plans.options.isTemplate) {
              saveDescription = 'Meal Plan is ready to be assigned to your clients!';
            } else {
              saveDescription = 'Click <strong>Save and Send</strong> to upload newest meal plan to the App of this client.';
            }
          }

          toastr.options.preventDuplicates = true;
          toastr.success(saveDescription, toastrTitlePrefix + ' updated!');
        })
        .fail(function(xhr) {
          if (xhr.status > 0) {
            var toastrTitlePrefix;

            if (Plans.options.isTemplate) {
              toastrTitlePrefix = 'Template changes';
            } else {
              toastrTitlePrefix = Plans.isWorkout() ? 'Workout Plan' : 'Meal Plan';
            }

            toastr.options.preventDuplicates = true;
            toastr.error(toastrTitlePrefix + ' Plan save failed');
          }
        })
        .always(next);
    };

    stack.addToQueue(job);
  };

  /**
   * @TODO Delete plan entity by id
   *
   * @param {number} id
   * @return {jQuery.Deferred}
   */
  Plans.deleteEntity = function(id) {
    var url = this.options.deleteEntityUrl.replace('entityId', id);
    var xhr = $.post(url, 'json');

    xhr
      .done(function(response) {
        var unitName = Plans.isWorkout() ? 'Exercise' : 'Product';

        if (Plans.isMeal()) {
          Plans.updatePlanSidebar(response.plan);
          Plans.updateMealTotals(response.meal);
        }

        toastr.options.preventDuplicates = true;
        toastr.success(unitName + ' successfully deleted');

        $(document.body).trigger('plans.updated', 'delete.entity');
      })
      .fail(function() {
        var unitName = Plans.isWorkout() ? 'exercise' : 'product';

        toastr.options.preventDuplicates = true;
        toastr.error('Cannot delete ' + unitName + ', please try later.');
      });

    return xhr;
  };

  /**
   * @param {Object} data
   * @return {jQuery.Deferred}
   */
  Plans.createPlan = function(data) {
    var xhr = $.ajax({
      type: 'POST',
      url: this.options.createPlanUrl,
      data: data,
      dataType: 'json'
    });

    xhr.done(function() {
      $(document.body).trigger('plans.updated', 'create.plan');
    });

    return xhr;
  };

  Plans.initSortable = function() {
    this.destroySortable();

    var selector = '#plans-container';

    if (Plans.isMeal()) {
      selector += ' > .plan-box';
    }

    var prevScrollY = null;
    var container = document.querySelector(selector);

    if (!container) {
      return;
    }

    var sortableOptions = {
      handle:'.workout-day-drag-handle',
      draggable: '.workout-day',
      forceFallback: IS_FIREFOX,
      chosenClass: 'sortable-mirror',

      onStart: function() {
        $('.js-choose-amout').popover('hide');
      },

      onSort: function() {
        Plans.save();
      },

      setData: function(dataTransfer, el) {
        el.classList.add('is-collapsed');
      }
    };


    function findPos(obj) {
      var curtop = 0;
      if (obj.offsetParent) {
        do {
          curtop += obj.offsetTop;
        } while (obj = obj.offsetParent);
        return [curtop];
      }
    }

    var plansSortableOptions = Object.assign({
      onStart: function(event) {
        $('.workout-day', container).addClass('is-collapsed');
        window.scrollTo(0, 0);
      },
      onEnd: function(event) {
        $('.workout-day', container).each(function() {
          var $el = $(this);
          $el.toggleClass('is-collapsed', Boolean($el.data('isCollapsed')));
        });

        window.scrollTo(0, event.item.offsetTop - 140);
      }
    }, sortableOptions);

    this.sortables.plans = Sortable.create(container, plansSortableOptions);

    if (Plans.isMeal()) {
      var mealsSortableOptions = Object.assign({}, sortableOptions, {
        draggable: '.workout-day-children'
      });

      document
        .querySelectorAll('.plan-box-meals')
        .forEach(function(mealContainer, index) {
          mealsSortableOptions.onStart = function(event) {
            $('.workout-day-children', mealContainer).addClass('is-collapsed');
            window.scrollTo(0, findPos(mealContainer) - 196);
          };

          mealsSortableOptions.onEnd = function(event) {
            $('.workout-day-children', mealContainer).each(function() {
              var $el = $(this);
              $el.toggleClass('is-collapsed', Boolean($el.data('isCollapsed')));
            });

            window.scrollTo(0, event.item.offsetTop);
          };

          Plans.sortables['meals' + index] = Sortable.create(mealContainer, mealsSortableOptions);
        });
    }
  };

  Plans.destroySortable = function() {
    for (var name in this.sortables) {
      if (this.sortables.hasOwnProperty(name)) {
        this.sortables[name].destroy();
        this.sortables[name] = null;
      }
    }
  };

  /**
   * @param {string} selector
   */
  Plans.attachDragable = function(selector) {
    var elements = document.querySelectorAll(selector).toArray();

    if (elements.length) {
      elements.forEach(function(element) {
        Plans.addDragable(element);
      });
    }
  };

  /**
   * @param {Element} element
   */
  Plans.addDragable = function(element) {
    var index = this.dragableItems.indexOf(element);

    if (index === -1) {
      this.dragableItems.push(element);
    }
  };

  /**
   * @param {Element} element
   */
  Plans.detachDragable = function(element) {
    var index = this.dragableItems.indexOf(element);

    if (index > -1) {
      this.dragableItems.splice(index, 1);
    }
  };

  Plans.initDragable = function() {
    this.destroyDragable();

    this.attachDragable('.workout-hint-dropzone-area');

    if (this.type === this.WORKOUT) {
      this.attachDragable('#plans-container .workout-day > .exercises');
      this.attachDragable('#plans-container .superset-item .exercises');
    }

    if (this.type === this.MEAL) {
      this.attachDragable('#plans-container .workout-day-children > .exercises');
    }

    this.dragable = dragula(this.dragableItems, {
      copy: Plans.dragCopy,
      accepts: Plans.dragAccepts,
      moves: Plans.dragMoves
    });

    this.dragable
      .on('drag', function(el, source) {
        Plans.toggleGhostZone(true);
        $('.js-choose-amout').popover('hide');
      })
      .on('drop', this.onDragDrop)
      .on('cancel', this.onDragCancel)
      .on('out', this.onDragOut)
      .on('over', this.onDragOver);
  };

  Plans.destroyDragable = function() {
    if (this.dragable) {
      this.dragable.destroy();
      this.dragable = null;
    }

    if (this.dragableItems.length) {
      this.dragableItems.length = 0;
    }
  };

  /**
   * @param {Element} element
   * @param {boolean} isRemove
   */
  Plans.initSuperSets = function(element, isRemove) {
    var superSetNode = element.closest('.superset-item');
    var entityNodes = [];
    // var inputNodes = [];
    var removableNode;

    if (superSetNode) {
      entityNodes = superSetNode.querySelectorAll('.exercise-item').toArray();

      if (isRemove) {
        removableNode = entityNodes.pop();
      }
    } else {
      entityNodes.push(element);
    }

    var setsSelector = 'js-input-sets';
    var restSelector = 'js-input-rest';
    var values = (function() {
      var node;

      if (removableNode) {
        node = removableNode;
      } else {
        node = entityNodes.length > 1 ? entityNodes[entityNodes.length - 2] : entityNodes[0];
      }

      var setsNode = node.querySelector('.' + setsSelector);
      var restNode = node.querySelector('.' + restSelector);

      return {
        sets: setsNode.value || 0,
        rest: restNode.value || 0
      };
    })();

    var inputNodes = entityNodes
      .slice(0, entityNodes.length - 1)
      .reduce(function(list, node) {
        return list.concat(node.querySelectorAll('.' + setsSelector + ', ' + '.' + restSelector).toArray());
      }, []);

    inputNodes.forEach(function(node) {
      node.disabled = true;

      if (node.classList.contains(setsSelector)) {
        node.removeEventListener('change', Plans.onCopySets);
      } else if (node.classList.contains(restSelector)) {
        node.value = 0;
      }
    });

    var lastEntityNode = entityNodes.pop();
    var lastSetsNode = lastEntityNode.querySelector('.' + setsSelector);

    lastSetsNode.value = values.sets;
    lastSetsNode.disabled = false;

    if (entityNodes.length) {
      lastEntityNode
        .querySelector('.js-input-sets')
        .addEventListener('change', Plans.onCopySets);
    } else {
      lastEntityNode
        .querySelector('.js-input-sets')
        .removeEventListener('change', Plans.onCopySets);
    }

    var lastRestNode = lastEntityNode.querySelector('.' + restSelector);
    lastRestNode.value = values.rest;
    lastRestNode.disabled = false;

    if (isRemove) {
      element.remove();
    } else {
      inputNodes
        .filter(function(node) {
          return node.classList.contains(restSelector);
        })
        .forEach(function(node) {
          node.value = 0;
        });
    }
  };

  /**
   * @param {Element} node
   */
  Plans.initSidebarChart = function(node) {
    var totals = JSON.parse(node.dataset.chart);
    var data = {
      labels: ["Carbohydrates", "Protein", "Fat"],
      datasets: [{
        data: [
          (totals.carbohydrate * 4),
          (totals.protein * 4),
          (totals.fat * 9)
        ],
        backgroundColor: ["#FB4A4A", "#16A4F0", "#FB924A"],
        hoverBackgroundColor: ["#FF6384", "#36A2EB", "#F88231"]
      }]
    };

    var ctx = node.querySelector('canvas').getContext('2d');
    var chart = node.chartInstance = new Chart(ctx,{
      type: 'pie',
      data: data,
      options: {
        legend: {
          display: false
        },
      }
    });
  }

  /**
   * @param {Element} node
   */
  Plans.initPlanChartPopovers = function(node) {
    $('.js-plan-chart', node)
      .on('shown.bs.popover', onPlanChartPopoverShown)
      .on('hidden.bs.popover', onPlanChartPopoverHidden)
      .popover(POPOVER_PLAN_CHART);
  };

  /**
   * @param {Element} node
   */
  Plans.initAmountsPopovers = function(node) {
    $('.js-product-amounts', node)
      .on('shown.bs.popover', onProductsAmontsPopoverShown)
      .popover(POPOVER_PRODUCT_AMOUNTS);
  };

  /**
   * @param {Element} container
   */
  Plans.initAmoutChooser = function(container) {
    $('.js-choose-amout', container || document.body).popover({
      placement: 'bottom',
      html: true,
      trigger: 'click',
      container: 'body',
      content: function() {
        return (
          '<div class="plan-product-popover-weights"></div>'
        );
      },
      title: 'Choose amount',
      template: '<div class="popover plan-weights-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    });
  };

	Plans.refreshMealComments = function() {
		document
			.querySelectorAll('.meal-comment')
			.forEach(function(node) {
				var linkNode = node.querySelector('.js-meal-comment-toggle');
				var inputNode = node.querySelector('textarea');
				var isEmpty = !inputNode.value.length;

				linkNode.classList.toggle('hidden', !isEmpty);
				inputNode.classList.toggle('hidden', isEmpty);
			});
	};

  /**
   * @param {Element} element
   * @return {Object}
   */
  Plans.getEntityData = function(element) {
    var data = {
      id: element.dataset.id,
      // name:  element.dataset.name || element.querySelector('.exercise-item-title').textContent,
      name: element.querySelector('.exercise-item-title').textContent,
    };

    if (this.isWorkout()) {
      var info = element.querySelector('.exercise-item-info').textContent.split(/\s?-\s?/);

      data.href = this.options.previewEntityUrl.replace('entityId', data.id);
      data.img = element.querySelector('img').getAttribute('src');
      data.type = info[0];
      data.exerciseType = info.pop();
      data.workoutType = element.dataset.workoutType;
    }

    if (this.isMeal()) {
      data.brand = element.dataset.brand;
      data.kcal= element.dataset.kcal;
      data.protein= element.dataset.protein;
      data.carbohydrates= element.dataset.carbohydrates;
      data.fat= element.dataset.fat;
      data.weights = element.dataset.weights;
    }

    return data;
  };

  /**
   * @param {Element} target
   * @param {Element} source
   * @return {boolean}
   */
  Plans.canCreateDragItem = function(target, source) {
    return target !== null && Plans.isSideBarEntities(source);
  };

  /**
   * @param {Event} event
   */
  Plans.onCopySets = function(event) {
    var targetNode = event.target;
    var superSetNode = targetNode.closest('.superset-item');
    var inputNodes = superSetNode.querySelectorAll('.js-input-sets').toArray();

    inputNodes
      .filter(function(node) {
        return node !== targetNode;
      })
      .forEach(function(node) {
        node.value = targetNode.value;
      });
  };

  /**
   * @param {Element} element
   * @param {Element} source
   * @return {boolean}
   */
  Plans.dragCopy = function(element, source) {
    return Plans.isSideBarEntities(source);
  };

  /**
   * @param {Element} element
   * @param {Element} target
   * @return {boolean}
   */
  Plans.dragAccepts = function(element, target) {
    var isValid =  !Plans.isSideBarEntities(target) &&
      !(element.classList.contains('superset') && target.classList.contains('rightsb'));

    if (isValid) {
      $(document.body).trigger('plans.dragAccepts');
    }

    return isValid;
  };

  /**
   * @param {Element} element
   * @return {boolean}
   */
  Plans.dragMoves = function(element, container, handle) {
    if (IS_TOUCH) {
      return handle.classList.contains('handle');
    }

    return element.classList.contains('exercise-item') ||
      element.classList.contains('superset-item');
  };

  function executeItemLog(entityData)
  {
    $.post(Plans.options.executeItemLog, entityData);
  }

  /**
   * @param {Element} element
   * @param {Element} target
   * @param {Element} source
   */
  Plans.onDragDrop = function(element, target, source) {
    Plans.toggleGhostZone(false);

    if (!target) {
      return;
    }

    $(".notification-bar").show(function(elm) {
      elm.slideDown("slow")
    });

    var entityData = Plans.getEntityData(element);
    executeItemLog(entityData);
    var isSuperSet = target.classList.contains('super-exercises');
    var entityNode;

    var openMealAmountChooser = function(node) {
      var target = node.querySelector('.js-choose-amout');

      if (target) {
        setTimeout(function() {
          target.click();
        }, 200);
      }
    };

    if (Plans.canCreateDragItem(target, source)) {
      entityNode = Plans.createEntityNode(entityData, isSuperSet);

      if (target.classList.contains('workout-hint-dropzone-area')) {
        var newPlan = Plans.isWorkout() ?
          { name: 'Day 1' } :
          { name: 'Plan 1' };

        Plans.createPlan(newPlan)
          .done(function(plan) {
            if (Plans.isWorkout() && !plan.name) {
              plan.name = 'Day 1';
            }

            var planNode = Plans.createPlanNode(plan);
            var exercisesNode = planNode.querySelector('.exercises');
            var hintNode = target.closest('.workout-hint');
            var headerNode = document.querySelector('.workouts-header');

            exercisesNode.appendChild(entityNode);
            hintNode.parentNode.replaceChild(planNode, hintNode);
            element.remove();

            if (Plans.isMeal()) {
              plan.totals = {
                protein: 0,
                carbohydrate: 0,
                fat: 0,
                weight: 0,
                kcal: 0
              };

              Plans.initPlanChartPopovers(planNode);
              Plans.initAmountsPopovers(planNode);
              Plans.initSidebarChart(planNode.querySelector('.plan-sidebar-chart'));
              Plans.updatePlanSidebar(plan);
              Plans.initAmoutChooser(planNode);

              openMealAmountChooser(entityNode);
            }

            planNode
              .querySelectorAll('.exercises')
              .forEach(function(node) {
                Plans.addDragable(node);
              });

            Plans.initSortable();
            Plans.save();

            headerNode.classList.remove('hidden');

            if (Plans.isWorkout()) {
              var headerRightNode = headerNode.querySelector('.workouts-header-right');
              var saveTemplateButton = document.createElement('button');

              saveTemplateButton.className = 'btn btn-default js-save-template';
              saveTemplateButton.type = 'button';
              saveTemplateButton.textContent = 'Save as Template';

              headerRightNode.insertBefore(saveTemplateButton, headerRightNode.firstChild);
            }
          })
          .fail(function() {
            toastr.options.preventDuplicates = true;
            toastr.error('Something was wrong, please try again.');
          });
      } else {
        element.parentNode.replaceChild(entityNode, element);
        element = entityNode;

        if (Plans.isMeal()) {
          Plans.initAmountsPopovers(entityNode);
          Plans.initAmoutChooser(entityNode);
        }
      }
    } else {
      if (Plans.isWorkout()) {
        if (isSuperSet && element.classList.contains('superset-item')) {
          element
            .querySelectorAll('.js-switch-superset')
            .forEach(function(node) {
              node.remove();
            });

          var exercisesFragment = document.createDocumentFragment();
          var exercisesNodeList = element
            .querySelectorAll('.exercise-item')
            .toArray();

          exercisesNodeList.forEach(function(node) {
            node.classList.add('is-super');
            exercisesFragment.appendChild(node);
          });

          element.parentNode.replaceChild(exercisesFragment, element);
          element = exercisesNodeList[exercisesNodeList.length - 1];
        } else {
          var superSetLinkNode = element.querySelector('.js-switch-superset');

          if (isSuperSet) {
            if (superSetLinkNode) {
              superSetLinkNode.remove();
            }
          } else if (!superSetLinkNode) {
            var addDropsetLinkNode = element.querySelector('.js-add-dropset');

            superSetLinkNode = document.createElement('a');
            superSetLinkNode.href = '#';
            superSetLinkNode.className = 'add-link js-switch-superset';
            superSetLinkNode.dataset.state = 0;
            superSetLinkNode.dataset.titleAdd = 'Create Super Set';
            superSetLinkNode.dataset.titleRemove = 'Cancel Super Set';
            superSetLinkNode.textContent = 'Create Super Set';

            addDropsetLinkNode.parentNode.insertBefore(superSetLinkNode, addDropsetLinkNode.nextSibling);
          }

          element.classList.toggle('is-super', isSuperSet);
        }
      }
    }

    var hintElement = document.querySelector('.workout-hint');

    if (!hintElement) {
      element.classList.remove('removable');

      if (Plans.isWorkout()) {
        Plans.initSuperSets(element);
      }

      Plans.save();
    }
  };

  /**
   * @param {Element} element
   * @param {Element} container
   */
  Plans.onDragCancel = function(element, container, source) {
    Plans.toggleGhostZone(false);

    if (element.classList.contains('removable')) {
      var entityId = 0;

      // @TODO Refactor this
      if (element.classList.contains('superset')) {
        entityId = 0; // $el.find('.main-workout td:nth-child(2) a').data('workout-id');
      } else {
        entityId = 0; // $el.find("td:nth-child(2) a").data('workout-id');
      }

      element.remove();

      if (entityId*1 > 0) {
        Plans
          .deleteEntity(entityId)
          .fail(function() {
              console.log("FAILED TO DELETE");
              Plans.save();
          });
      } else {
        Plans.save();
      }
    } else {
      element.classList.remove('removable');
    }
  };

  /**
   * @param {Element} element
   * @param {Element} container
   */
  Plans.onDragOut = function(element, container, source) {
    if (container.classList.contains('workout-hint-dropzone-area')) {
      container.parentNode.classList.remove('has-item');
    }

    element.classList.remove('removable');
  };

  /**
   * @param {Element} element
   * @param {Element} container
   */
  Plans.onDragOver = function(element, container, source) {
    if (container.classList.contains('workout-hint-dropzone-area')) {
      container.parentNode.classList.add('has-item');
    }

    element.classList.remove('removable');
  };

  /**
   * @param {Object} plan
   * @return {Element}
   */
  Plans.createPlanNode = function(plan) {
    var deleteUrl = this.options.deletePlanUrl.replace('planId', plan.id);
    var type = Plans.isWorkout() ? 'day' : 'plan';
    var dragTitle = Plans.isWorkout() ?
      'You can drag workout days up and down to adjust order of days' :
      'You can drag meal plans up and down to adjust order of plans';

    var exercisesTpl = function() {
      return '<div class="exercises"></div>'
    };

    var titleTpl = function(data, renderMealActions) {
      var deleteUrl = Plans.options.deletePlanUrl.replace('planId', data.id);
      var actions = '';
      var name = '';

      if (renderMealActions) {
        actions += (
          '<button class="workout-day-collapse js-clone-plan" type="button" data-title="' + data.name + '" data-id="' + data.id + '">' +
            '<i class="fa fa-clone" aria-hidden="true"></i>' +
          '</button>' +
          '<button class="workout-day-collapse js-delete-plan" type="button" data-toggle="modal" data-target="#confirm-delete" data-href="' + deleteUrl + '">' +
            '<i class="fa fa-trash" aria-hidden="true"></i>' +
          '</button>'
        );

        name = '<span class="js-plan-chart" data-totals=\'' + getTotalsJSON() + '\'>' + data.name + '</span>';
      } else {
        actions += (
          '<a class="delete-link js-delete-plan" href="#" data-toggle="modal" data-target="#confirm-delete" data-href="' + deleteUrl + '">' +
            'Delete ' + (Plans.isMeal() ? 'Meal Plan' : 'Workout Day') +
          '</a>'
        );

        name = data.name;
      }

      return (
        '<div class="workout-day-title js-collapse-plan">' +
          '<buttton class="workout-day-drag-handle handle" type="button" title="' + dragTitle + '">' +
            '<i class="fa fa-arrows" aria-hidden="true"></i>' +
          '</buttton>' +
          '<h5>' +
              name + ' - ' +
              '<a class="js-add-plan" data-title="' + data.name + '" data-id="' + data.id + '">Edit title</a>' +
          '</h5>' +
          '<div class="workout-day-tools">' +
              actions +
              '<buttton class="workout-day-collapse js-collapse-plan" type="button">' +
                  '<i class="fa fa-angle-up" aria-hidden="true"></i>' +
              '</buttton>' +
          '</div>' +
        '</div>'
      );
    };

    var ghostTpl = function(title) {
      return '<div class="exercise-ghost" data-drop-title="' + title + '"></div>';
    };

    var commentTpl = function (placeholder, triggerType, className, isMeal) {
			if (!className) {
				className = '';
			}

      return (
        '<div class="workout-day-comment ' + className + '">' +
					(isMeal ? '<a href="#" class="add-link js-meal-comment-toggle">Comment for Meal</a>' : '') +
          '<textarea placeholder="Comment for this ' + placeholder + '" class="form-control js-' + triggerType + '-comment" rows="2"></textarea>' +
        '</div>'
      );
    };

    var totalsTpl = function() {
      return (
        '<div class="plan-totals">' +
          '<div class="plan-totals-col">' +
            '<span>Protein</span>' +
            '<var class="js-total-protein">0</var>g' +
          '</div>' +
          '<div class="plan-totals-col">' +
            '<span>Carbohydrate</span>' +
            '<var class="js-total-carbohydrate">0</var>g' +
          '</div>' +
          '<div class="plan-totals-col">' +
            '<span>Fat</span>' +
            '<var class="js-total-fat">0</var>g' +
          '</div>' +
          '<div class="plan-totals-col">' +
            '<var class="js-total-weight">0</var>g' +
          '</div>' +
          '<div class="plan-totals-col">' +
            '<var class="js-total-kcal">0</var>kcal' +
          '</div>' +
        '</div>'
      );
    };

    var getTotalsJSON = function() {
      return JSON.stringify({
        "protein": 0,
        "carbohydrate": 0,
        "fat": 0,
        "weight": 0,
        "kcal": 0
      });
    };

    var body = '';

    if (Plans.isWorkout()) {
      body += exercisesTpl();
      body += ghostTpl('Drop exercise here to Add it');
      body += commentTpl(type, type);
    }

    if (Plans.isMeal()) {
      body += '<div class="plan-box-meals">';

      plan.meals.forEach(function(meal) {
        body += (
          '<div class="workout-day-children" id="plan_' + meal.id + '" data-id="' + meal.id + '" data-parent-id="' + plan.id + '">' +
            titleTpl(meal, true) +
            totalsTpl() +
            exercisesTpl() +
            ghostTpl('Drop Food Item here to add it to a Meal') +
            commentTpl('meal plan', 'day', 'meal-comment', true) +
          '</div>'
        );
      });

      body += '</div>';
      body += (
        '<div class="plan-box-actions">' +
            '<button class="btn btn-success js-add-plan" data-parent="' + plan.id + '">+ Add New Meal</button>' +
        '</div>'
      );
      body += commentTpl(type, type);
    }

    var content = (
      '<div class="workout-day" id="' + type + '_' + plan.id + '" data-id="' + plan.id + '">' +
        titleTpl(plan) +
        body +
      '</div>'
    );

    var node = document.createElement('div');

    node.id = 'plans-container';
    node.className = 'list-group workout-scroll scroll' + (Plans.isMeal() ? ' plan-meal-list' : '');

    if (Plans.isMeal()) {
      node.innerHTML = (
        '<div class="plan-box">' + content + '</div>' +
        '<div class="plan-sidebar">' +
          '<div class="plan-sidebar-area">' +
            '<div class="plan-sidebar-box hidden" data-id="' + plan.id + '">' +
              '<h2 class="plan-sidebar-title">Total Calorie Counter</h2>' +
              '<div class="plan-popover-list"></div>' +
              '<div class="plan-sidebar-chart" data-chart=\'' + getTotalsJSON() + '\'>' +
                '<canvas width="100" height="100"></canvas>' +
              '</div>' +
            '</div>' +
          '</div>' +
        '</div>'
      );
    } else {
      node.innerHTML = content;
    }

    return node;
  };

  /**
   * @param {Object} data
   * @param {boolean} isSuperSet
   * @return {Element}
   */
  Plans.createEntityNode = function(data, isSuperSet) {
    var content = '<div class="exercise-item-move handle"></div>';
    var info = '';
    var actions = '';
    var title = '';

    if (this.isWorkout()) {
      content += (
        '<div class="exercise-item-thumb">' +
          '<a data-toggle="modal" data-target="#exerciseModal" href="' + data.href + '">' +
            '<img alt="image" src="' + data.img + '">' +
          '</a>' +
        '</div>'
      );

      info = (
        '<a href="#" data-state="0" data-title-add="Add Comment" data-title-remove="Remove Comment" class="add-link js-add-comment">Add Comment</a>' +
        '<a href="#" data-state="0" data-title-add="Drop Set" data-title-remove="Remove Drop Set" class="add-link js-add-dropset">Drop Set</a>' +
        (isSuperSet ? '' : '<a href="#" data-state="0" data-title-add="Create Super Set" data-title-remove="Cancel Super Set" class="add-link js-switch-superset">Create Super Set</a>')
      );

      actions = (
        '<div class="form-group">' +
          '<label>Set</label>' +
          '<input type="text" placeholder="Sets" value="3" class="form-control js-input-sets">' +
        '</div>' +
        '<div class="form-group">' +
          '<label>' + data.workoutType + '</label>' +
          '<input type="text" placeholder="' + data.workoutType + '" value="12" class="form-control js-input-' + data.workoutType.toLowerCase() + '">' +
        '</div>' +
        '<div class="form-group">' +
          '<label>Rest in sec</label>' +
          '<input type="number" min="0" placeholder="Rest" value="60" class="form-control js-input-rest">' +
        '</div>'
      );

      title = '<a data-toggle="modal" data-target="#exerciseModal" href="' + data.href + '" class="exercise-item-title">' + data.name + '</a>';
    }

    if (this.isMeal()) {
      info = (data.kcal || 0) + 'kcal / 100g ';
      actions = (
        '<div class="form-group js-input-weight" data-weights="' + data.weights + '" data-total-weight="0" data-weight-id="0" data-weight-units="0">' +
          '<a class="js-choose-amout" role="button" tabindex="0">Choose amount</a>' +
        '</div>' +
        '<div class="form-group">' +
          // '<span class="js-product-kcal">' + (data.kcal || 0) + '</span>kcal' +
          '<span class="js-product-kcal">0</span>kcal' +
        '</div>'
      );

      title = '<span class="exercise-item-title js-product-amounts">' + data.name + '</span>';
    }

    content += (
      '<div class="exercise-item-details">' +
        title +
        (Plans.isWorkout() ? '<small class="exercise-item-info">' + info + '</small>' : '') +
      '</div>' +
      '<div class="exercise-item-actions">' +
        actions +
        '<button class="exercise-item-delete" data-action="delete-exercise" type="button">' +
            '<i class="fa fa-times"></i>' +
        '</button>' +
      '</div>'
    );

    if (this.isWorkout()) {
      content += (
        '<div class="exercise-item-comment hidden">' +
          '<textarea class="form-control comment" placeholder="Comment"></textarea>' +
        '</div>'
      );
    }

    var node = document.createElement('div');

    node.className = 'exercise-item';
    node.dataset.id = data.id;

    if (isSuperSet) {
      node.className += ' is-super';
    }

    if (this.isWorkout()) {
      node.dataset.workoutId = data.workoutId || '';
      node.dataset.workoutType = data.workoutType || '';
    }

    if (this.isMeal()) {
      node.dataset.kcal = data.kcal || 0;
      node.dataset.weight = data.weight || 0;
      node.dataset.protein = data.protein || 0;
      node.dataset.carbohydrates = data.carbohydrates || 0;
      node.dataset.fat = data.fat || 0;
    }

    node.innerHTML = content;

    return node;
  };

  /**
   * @param {Object} totals
   * @param {Number?} weight
   * @return {DocumentFragment}
   */
  Plans.createAmountsList = function(totals, weight) {
    var fragment = document.createDocumentFragment();

    if (isNaN(weight) || typeof weight !== 'number') {
      weight = 50;
    }

    /**
     * @param {Object} data
     */
    var addListRow = function(data) {
      var node = document.createElement('div');
      var unit = data.unit || '';
      var content = (
        '<span>' + (data.title || '') + '</span>' +
        '<span>' + data.min + unit + '</span>' +
        '<span>' + data.max + unit + '</span>'
      );

      node.className = 'plan-popover-col';
      node.innerHTML = content;

      if (data.color) {
        node.style.color = data.color;
      }

      if (data.italic) {
        node.style.fontStyle = 'italic';
      }

      fragment.appendChild(node);
    };

    addListRow({
      title: 'In the portion',
      min: weight + 'g',
      max: 'per 100g',
      color: '#ACAEB1',
      italic: true
    });

    [{
      title: 'Energy',
      prop: 'kcal',
      unit: 'kcal'
    }, {
      title: 'Protein',
      prop: 'protein',
      color: "#16A4F0",
      unit: 'g'
    }, {
      title: 'Carbohydrates',
      prop: 'carbohydrate',
      color: "#FB4A4A",
      unit: 'g'
    }, {
      title: 'Fat',
      prop: 'fat',
      color: "#FB924A",
      unit: 'g'
    }].forEach(function(row) {
      var value = parseFloat(totals[row.prop]) || 0;
      var minValue = Math.max(0, (weight / 100) * value)
        .toLocaleString('en-US', {
          minimumFractionDigits: 0,
          maximumFractionDigits: 1
        });

      var maxValue = value.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 1
      });

      addListRow({
        title: row.title,
        min: minValue,
        max: maxValue,
        color: row.color,
        unit: row.unit
      });
    });

    return fragment;
  };

  /**
   * @param {Element} node
   * @param {number} order
   * @return {Object}
   */
  Plans.serializeEntity = function(node, order) {
    var result = {
      id: node.dataset.id,
      order: order || 0
    };

    var secureNodeValue = function(selector) {
      return (node.querySelector(selector) || {}).value;
    };

    var securedNodeData = function(selector) {
      return (node.querySelector(selector) || {}).dataset || {};
    }

    if (this.isWorkout()) {
      result.workout_id = node.dataset.workoutId;
      result.comment = secureNodeValue('.comment');
      result.info = secureNodeValue('.info');
      result.reps = secureNodeValue('.js-input-reps');
      result.time = secureNodeValue('.js-input-time');
      result.sets = secureNodeValue('.js-input-sets');
      result.rest = secureNodeValue('.js-input-rest');
    } else if (this.isMeal()) {
      result.totalWeight = securedNodeData('.js-input-weight').totalWeight;
      result.weightId = securedNodeData('.js-input-weight').weightId;
      result.weightUnits = securedNodeData('.js-input-weight').weightUnits;
      result.entity_id = node.dataset.entityId || null;
    }

    return result;
  };

  /**
   * @param {Element} element
   * @return {{ main: [Element], super: [Element] }}
   */
  Plans.getPlanEntityNodes = function(element) {
    var nodeList = element.querySelectorAll('.exercise-item')
      .toArray()
      .filter(function(node) {
        return !node.classList.contains('is-super');
      })
      .map(function(node) {
        var children = [];

        if (node.parentNode.classList.contains('superset-item')) {
          children = nodeList = node.parentNode.querySelectorAll('.super-exercises .exercise-item').toArray();
        }

        return {
          entity: node,
          children: children
        }
      });

    return nodeList;
  };

  /**
   * @param {Object} plan
   */
  Plans.updatePlanSidebar = function(plan) {
    var sidebarBox = document.querySelector('.plan-sidebar-box[data-id="' + plan.id + '"]');
    var chartContainerNode = sidebarBox.querySelector('.plan-sidebar-chart');
    var chart = chartContainerNode.chartInstance;
    var totals = plan.totals;

    chart.data.datasets[0].data = [
      (totals.carbohydrate * 4),
      (totals.protein * 4),
      (totals.fat * 9)
    ];

    var chartCanvas = chartContainerNode.querySelector('canvas');

    if (chartCanvas) {
      var totalValue = chart.data.datasets[0].data.reduce(function (accumulator, currentValue) {
        return accumulator + currentValue;
      }, 0);

      chartCanvas.style.visibility = totalValue > 0 ? 'visible' : 'hidden';
    }

    chart.update();

    var listContent = (
      '<div class="plan-popover-col">' +
        '<span>Total</span>' +
        '<span>' + Math.round(totals.kcal) + 'kcal</span>' +
      '</div>' +
      '<div class="plan-popover-col" style="color: rgb(22, 164, 240);">' +
        '<span>Protein</span>' +
        '<span>' + Math.round(totals.protein) + 'g</span>' +
      '</div>' +
      '<div class="plan-popover-col" style="color: rgb(251, 74, 74);">' +
        '<span>Carbohydrates</span>' +
        '<span>' + Math.round(totals.carbohydrate) + 'g</span>' +
      '</div>' +
      '<div class="plan-popover-col" style="color: rgb(251, 146, 74);">' +
        '<span>Fat</span>' +
        '<span>' + Math.round(totals.fat) + 'g</span>' +
      '</div>'
    );

    sidebarBox.querySelector('.plan-popover-list').innerHTML = listContent;
  };

  /**
   * @param {Object} plan
   */
  Plans.updateMealTotals = function(plan) {
    var planNode = document.querySelector('#plan_' + plan.id);
    var planChartNode = planNode.querySelector('.js-plan-chart');

    var normalizeValue = function(value) {
      return Math.round((value / plan.totals.weight) * 100);
    };

    var datasetTotals = {
      protein: normalizeValue(plan.totals.protein),
      carbohydrate: normalizeValue(plan.totals.carbohydrate),
      fat: normalizeValue(plan.totals.fat),
      kcal: normalizeValue(plan.totals.kcal),
      weight: plan.totals.weight,
    };

    planChartNode.dataset.totals = JSON.stringify(datasetTotals);

    for (var name in plan.totals) {
      if (plan.totals.hasOwnProperty(name)) {
        updateElementText('.js-total-' + name, Math.round(plan.totals[name]), planNode);
      }
    }
  };

  /**
   * @return {Array}
   */
  Plans.getDeskData = function() {
    var array = [];
    var planNodes = document.querySelectorAll('.workout-day').toArray();

    if (this.isWorkout()) {
      planNodes.forEach(function(planNode) {
        var plan = {
          day_id: planNode.id,
          workoutDayComment: planNode.querySelector('.js-day-comment').value,
          exercises: []
        };

        Plans
          .getPlanEntityNodes(planNode)
          .forEach(function(node, index) {
            var entityOrder = index + 1;
            var entity = Plans.serializeEntity(node.entity, entityOrder);

            entity.superset = [];

            node.children.forEach(function(children, childrenIndex) {
              var superOrder = childrenIndex + 1;
              entity.superset.push(Plans.serializeEntity(children, superOrder));
            });

            plan.exercises.push(entity);
          });

        array.push(plan);
      });
    }

    if (this.isMeal()) {
      planNodes.forEach(function(planNode) {
        var plan = {
          id: planNode.dataset.id,
          meals: [],
          comment: planNode.querySelector('.js-plan-comment').value
        };

        planNode
          .querySelectorAll('.workout-day-children')
          .forEach(function(mealNode, mealIndex) {
            var meal = {
              id: mealNode.dataset.id,
              parent: plan.id,
              order: mealIndex + 1,
              comment: mealNode.querySelector('.js-day-comment').value,
              products: []
            };

            Plans
              .getPlanEntityNodes(mealNode)
              .forEach(function(node, productIndex) {
                var order = productIndex + 1;

                meal.products.push(Plans.serializeEntity(node.entity, order));
              });

            plan.meals.push(meal);
          });

        array.push(plan);
      });
    }

    return array;
  };

  Plans.addListeners = function() {
    if (this.isWorkout()) {
      document
        .querySelectorAll('.super-exercises')
        .forEach(function(node) {
          var entityNodes = node.querySelectorAll('.exercise-item').toArray();

          if (entityNodes.length) {
            entityNodes[entityNodes.length - 1]
              .querySelector('.js-input-sets')
              .addEventListener('change', Plans.onCopySets);
          }
        });
    }
  };

  /**
   * @return {string}
   */
  Plans.getLocale = function() {
    return window.Cookies.get('meal_products_locale') || 'en';
  };

  /**
   * @param {boolean} isVisible
   */
  Plans.toggleGhostZone = function(isVisible) {
    document.querySelectorAll('.exercise-ghost')
      .forEach(function(node) {
        var prevNode = node.previousElementSibling;
        var hasGhost = false;

        if (prevNode.classList.contains('exercises')) {
          hasGhost = !prevNode.querySelectorAll('.exercise-item').length;
          prevNode.classList.toggle('has-ghost', hasGhost && isVisible);
        }

        node.classList.toggle('is-visible', hasGhost && isVisible);
      });

    // document.querySelectorAll('.workout-day')
    //   .forEach(function(node) {
    //
    //
    //     node.classList.toggle('show-ghost', isVisible);
    //   });
  };

  Plans.debounce = debounce;

  /**
   * @param {string} type
   * @param {Object?} options
   * @throws TypeError
   */
  Plans.init = function(type, options) {
    if (!type) {
      throw new TypeError('You must define plan type');
    }

    this.type = type;

    if (typeof options === 'object' && options !== null) {
      for (var prop in options) {
        if (options.hasOwnProperty(prop)) {
          this.options[prop] = options[prop];
        }
      }
    }

    this.initDragable();
    this.initSortable();
    this.addListeners();
  };


  global.Plans = Plans;

/*
function showCheckOutMobileAppModal()
{
    $("#checkOutMobileApp").modal();
    checkOutMobileAppModal = 1;
    setShowCheckOutMobileAppModal();
}

function setShowCheckOutMobileAppModal()
{
    $.ajax({
        type: "POST",
        url: setCheckOutMobileAppUrl,
        success: function (res) {
            console.log(res);
        }
    });
}*/

  // Helpers

  /**
   * @param {string} selector
   * @param {string} text
   * @param {Element} container
   */
  function updateElementText(selector, text, container) {
    var node = (container || document).querySelector(selector);

    if (node) {
      node.textContent = text;
    }
  };

  /**
   * @param {string} value
   * @returns {Array.<string>}
   */
  function filterCommentDropSet(value) {
    return value
      .trim()
      .split("\n")
      .filter(function(str) {
        return !COMMENT_DROP_SET_RE.test(str);
      });
  }

  /**
   * @param {Element} node
   * @param {boolean} state
   */
  function toggleExerciseLinkState(node, state) {
    node.classList.toggle('delete-link', !state);
    node.classList.toggle('add-link', state);
    node.dataset.state = state ? 0 : 1;
    node.textContent = state ?
      node.dataset.titleAdd :
      node.dataset.titleRemove;
  }

  function debounce(func, wait, immediate) {
    var timeout;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  }

  /**
   * @param {Object} event
   */
  function onPlanChartPopoverShown(event) {
    var totals = JSON.parse(this.dataset.totals);
    var listNode = document.querySelector('.plan-popover-list');
    var ctx = document
      .querySelector('.plan-popover-chart canvas')
      .getContext('2d');

    var data = {
      labels: [
        "Carbohydrates",
        "Protein",
        "Fat"
      ],
      datasets: [{
        data: [
          (totals.carbohydrate * 4),
          (totals.protein * 4),
          (totals.fat * 9)
        ],
        backgroundColor: [
          "#FB4A4A",
          "#16A4F0",
          "#FB924A"
        ],
        hoverBackgroundColor: [
          "#FF6384",
          "#36A2EB",
          "#F88231"
        ]
      }]
    };

    planChart = new Chart(ctx,{
      type: 'pie',
      data: data,
      options: {
        legend: {
          display: false
        }
      }
    });

    listNode.appendChild(Plans.createAmountsList(totals, totals.weight));
  }

  function onPlanChartPopoverHidden(event) {
    if (planChart) {
      planChart.destroy();
      planChart = null;
    }
  }

  function onProductsAmontsPopoverShown(event) {
    var entityNode = this.closest('.exercise-item');
    var listNode = document.querySelector('.plan-popover-list');
    var weightInputNode = entityNode.querySelector('.js-input-weight');
    var weight = weightInputNode ? parseInt(weightInputNode.dataset.totalWeight, 10) : 0;
    var totals = {
      kcal: parseInt(entityNode.dataset.kcal, 10),
      protein: parseFloat(entityNode.dataset.protein),
      carbohydrate: parseFloat(entityNode.dataset.carbohydrates),
      fat: parseFloat(entityNode.dataset.fat),
    };

    listNode.appendChild(Plans.createAmountsList(totals, weight));
  }

  // Global jQuery Listeners

  var $body = $('body');

  $body.on('click', '.js-save-plans', function(event) {
    event.preventDefault();
    Plans.save();
  });

  $body.on('hidden.bs.modal', '#exerciseModal', function() {
    $(this).removeData('bs.modal');
    $('video').trigger('pause');
  });

  $body.on('hidden.bs.modal', '#saveAsPdf', function() {
    $(this)
      .find('.modal-success, a[role=button]')
        .remove()
        .end()
      .find('.modal-title, .modal-body-main, button[type=submit]')
        .show();
  });

  $body.on('click', '.js-save-template', function() {
    $("#saveAsTemplate").modal('show');
  });

  $body.on('click', '.js-save-pdf', function() {
    $("#saveAsPdf").modal('show');
  });

  $body.on('submit', '#saveAsTemplate form', function(event) {
    event.preventDefault();

    var $form = $(this);
    var $modal = $('#saveAsTemplate');
    var $submit = $form
      .find('button[type=submit]')
      .button('loading');

    var xhr = $.ajax({
      type: 'POST',
      url: $form.attr('action'),
      data: $form.serialize(),
      dataType: 'json'
    });

    xhr
      .done(function() {
        var templatesUrl = $modal.data('templatesUrl');

        $modal
          .find('.modal-title')
          .remove();

        var title = Plans.isMeal() ? 'Meal' : 'Workout';

        $modal
          .find('.modal-body')
          .html(
            '<div class="modal-success">' +
              '<div class="modal-success-icon" />' +
              '<h4 class="modal-success-title">Save ' + title + ' as Template</h4>' +
              '<p class="modal-success-description">You can find all your templates in main menu' + "\n" + ' section called "' + title + ' Templates".</p>' +
            '</div>'
          );

        $submit
          .replaceWith('<a href="' + templatesUrl + '" class="btn btn-block btn-default" role="button">See Your Templates</a>');
      })
      .fail(function() {
        $submit.button('reset');
      });
  });

  $body.on('submit', '#saveAsPdf form', function(event) {
    event.preventDefault();

    var $form = $(this);
    var $modal = $('#saveAsPdf');
    var $submit = $form
      .find('button[type=submit]')
      .button('loading');

    var xhr = $.ajax({
      type: 'POST',
      url: $form.attr('action'),
      data: $form.serialize(),
      dataType: 'json'
    });

    xhr
      .done(function(response) {
        var documentsUrl = $modal.data('documentsUrl');

        $modal
          .find('.modal-title, .modal-body-main')
          .hide();

        $modal
          .find('.modal-body')
          .append(
            '<div class="modal-success">' +
              '<div class="modal-success-icon" />' +
              '<h4 class="modal-success-title">Meal Plan has been sent to your client\'s App!</h4>' +
              '<p class="modal-success-description">The Meal Plan will appear in the Documents folder. We have also saved a copy on your PC' + "\n" + ':)</p>' +
            '</div>'
          );

        $submit
          .button('reset')
          .hide()
          .after('<a href="' + documentsUrl + '" class="btn btn-block btn-default" role="button">See All Your Meal Plans</a>');

        if(response.url) {
          setTimeout(function() {
            window.location = response.url;
          }, 100);
        }
      })
      .fail(function() {
        $submit.button('reset');
      });
  });

  $body.on('click', '.js-use-template', function(event) {
    event.preventDefault();

    $("#addWorkoutTemplate").modal("show");
    // var title = $(this).attr("data-title");
    // var id = $(this).attr("data-id");

    /*if(title) {
        $("#addWorkoutDay #title").val(title);
        $("#addWorkoutDay .modal-title").text("Edit workout day");
        $("#addWorkoutDay #workout_day_id").val(id);
    } else {
        $("#addWorkoutDay #title").val("");
        $("#addWorkoutDay .modal-title").text("Add new workout day");
        $("#addWorkoutDay #workout_day_id").val(0);
    }*/
  });

  $body.on('change', '#musclegroup, #equipment', function() {
    var equipment = $("#equipment").val();
    var muscleGroup = $("#musclegroup").val();

    $('[muscle_group_id]').addClass('hidden');

    if (muscleGroup != 0 && equipment != 0) {
      $('[muscle_group_id="'+ muscleGroup +'"][equipment_id="' + equipment + '"]').removeClass('hidden');
      return;
    }

    if (equipment != 0) {
      $('[equipment_id="' + equipment + '"]').removeClass('hidden');
      return;
    }

    if (muscleGroup != 0) {
      $('[muscle_group_id="'+ muscleGroup +'"]').removeClass('hidden');
      return;
    }
  });

  $body.on('change', '#plans-container textarea', function() {
    if (this.parentNode.classList.contains('exercise-item-comment')) {
      var value = this.value.trim();

      if (value) {
        var linkNode = this.closest('.exercise-item').querySelector('.js-add-comment');

        if (linkNode) {
          toggleExerciseLinkState(linkNode, false);
        }
      }
    }

    Plans.save();
  });

  $body.on('click', '.js-add-plan', function(event) {
    event.stopPropagation();
    event.preventDefault();

    var title = this.dataset.title;
    var id = this.dataset.id;
    var parentId = this.dataset.parent;
    var $modal = $('#addPlanModal');
    var $submit = $modal.find('button[type=submit]');
    var type = Plans.isWorkout() ? 'workout day' : 'meal plan';
    var placeholder = 'Title of workout day';

    if (Plans.isMeal()) {
      placeholder = parentId ? 'Title of meal' : 'Title of meal plan';
      $modal.find('label[for=mealTitle]').text(parentId ? 'Title of meal' : 'Title of meal plan');
    }

    $modal.modal('show');
    $modal.find('input[name=id]').val(id || null);
    $modal
      .find('input[name=name]')
      .val(title || '')
      .attr('placeholder', placeholder);

    var submitTitle;

    if (id) {
      if (Plans.isMeal()) {
        submitTitle = parentId ? 'Save Meal' : 'Save Meal Plan';
      } else {
        submitTitle = 'Save Workout Day'
      }
    } else {
      if (Plans.isMeal()) {
        submitTitle = parentId ? 'Add new Meal' : 'Add new Meal Plan';
      } else {
        submitTitle = 'Add new Workout Day'
      }
    }

    $submit.text(submitTitle);

    if (Plans.type === Plans.MEAL) {
      $modal.find('input[name=parent_id]').val(parentId || null);
    }

    var addTitle = '';

    if (Plans.isMeal()) {
      addTitle = parentId ? 'Create new meal' : 'Create new meal plan';
    } else {
      addTitle = 'Create new workout day';
    }

    $modal
      .find('.modal-title')
      .text(id ? 'Edit ' + type : addTitle);
  });

  $body.on('click', '[data-action="delete-exercise"]', function(event) {
    event.preventDefault();

    var entityNode = this.closest('.exercise-item');
    var entityId = Plans.isWorkout() ?
      entityNode.dataset.workoutId :
      entityNode.dataset.entityId;

    Plans
      .deleteEntity(entityId)
      .done(function() {
        if (Plans.isWorkout()) {
          var nextNode = entityNode.nextElementSibling;

          if (nextNode && nextNode.classList.contains('super-exercises')) {
            Plans.detachDragable(nextNode);
            entityNode.parentNode.remove();
          } else {
            Plans.initSuperSets(entityNode, true);
          }
        }

        if (Plans.isMeal()) {
          entityNode.remove();
        }
      });
  });

  $body.on('click', '.js-add-comment, .js-add-dropset', function(event) {
    event.preventDefault();

    var $el = $(this);
    var $target = $el.closest('.exercise-item').find('.exercise-item-comment');
    var $targetInput = $target.find('textarea');
    var isDropSet = $el.hasClass('js-add-dropset');
    var isActive = this.dataset.state == 1;
    var hasComment = isActive;
    var removeSibling = false;
    var nextValue = filterCommentDropSet($targetInput.val());

    if (isDropSet) {
      if (isActive) {
        hasComment = nextValue.length === 0;

        if (hasComment) {
          removeSibling = '.js-add-comment';
        }
      }
    } else {
      if (isActive) {
        nextValue = [];
        removeSibling = '.js-add-dropset';
      }
    }

    $target
      .toggleClass('hidden', hasComment);

    if (isActive) {
      $targetInput
        .val(nextValue.join("\n"))
        .trigger('change');
    } else {
      if (isDropSet) {
        nextValue = ['This is a drop set. Drop the weights X time(s)'].concat(nextValue);
        $targetInput.val(nextValue.join("\n"));
      }

      $targetInput
        .trigger('change')
        .focus();
    }

    toggleExerciseLinkState($el.get(0), isActive);

    if (removeSibling) {
      var $item = $el
        .siblings(removeSibling)
        .filter('.delete-link');

      if ($item.length) {
        toggleExerciseLinkState($item.get(0), true);
      }
    }
  });

  $body.on('click', '.js-switch-superset', function(event) {
    event.preventDefault();

    var $el = $(this);
    var $exercise = $el.closest('.exercise-item');
    var isSuperSet = this.dataset.state == 1;

    var switchState = function() {
      if (isSuperSet) {
        var container = $exercise.next('.super-exercises').get(0);

        Plans.detachDragable(container);

        $exercise
          .next('.super-exercises')
          .find('.exercise-item')
          .each(function() {
            Plans.deleteEntity(this.dataset.workoutId);
          });

        $exercise
          .siblings('.exercises, .superset-divider, .exercise-ghost')
            .remove()
            .end()
          .find('input[disabled]')
            .prop('disabled', false)
            .end()
          .unwrap();
      } else {
        var exercises = $exercise
          .wrap('<div class="superset-item" />')
          .parent()
          .prepend(
            '<buttton class="superset-item-drag-handle handle" type="button">' +
              '<i class="fa fa-arrows" aria-hidden="true"></i>' +
            '</buttton>' +
            '<div class="superset-divider">Super Set</div>'
          )
          .append('<div class="exercises super-exercises" />' +
            '<div class="exercise-ghost" data-drop-title="Drop exercise here to Add it to a Super Set" />' +
            '<div class="superset-divider">End of Super Set</div>')
          .find('.exercises')
          .get(0);

        Plans.addDragable(exercises);
      }

      toggleExerciseLinkState($el.get(0), isSuperSet);
    };

    if (isSuperSet) {
      bootbox.confirm(
        "Are you sure? " +
        "By undoing a super set all exercises in super set will be removed. " +
        "Please drop exercises out of super set if you want to keep them in the workout plan",
        function(result) {
          if (result) {
            switchState();
            Plans.save();
          }
        });
    } else {
      switchState();
    }
  });

  $body.on('click', '.js-collapse-plan', function(event) {
    event.stopPropagation();
    event.preventDefault();

    var $el = $(this);
    var $target = $();

    if (Plans.isMeal()) {
      $target = $el.closest('.workout-day-children');
    }

    if (!$target.length) {
      $target = $el.closest('.workout-day');
    }

    var isCollapsed = $target.hasClass('is-collapsed');

    console.log(isCollapsed);

    if ($el.data('single')) {
      $('.workout-day')
        .not($target)
        .addClass('is-collapsed')
        .data('isCollapsed', true)
        .find('.js-collapse-plan .workout-day-collapse:last .fa')
          .toggleClass('fa-angle-up', false)
          .toggleClass('fa-angle-down', true);
    }

    $target
      .toggleClass('is-collapsed', !isCollapsed)
      .data('isCollapsed', !isCollapsed);

    var iconSelector = $el.hasClass('workout-day-collapse') ?  '.fa' : '.workout-day-collapse:last .fa';

    $el
      .find(iconSelector)
      .toggleClass('fa-angle-up', isCollapsed)
      .toggleClass('fa-angle-down', !isCollapsed);

    $body.trigger('plans.collapsed');
  });

  $body.on('click', '.js-clone-plan', function(event) {
    event.stopPropagation();
    event.preventDefault();

    var $modal = $('#clonePlanModal');
    var data = $(this).data();

    $modal.modal("show");
    $modal.find('input[name=name]').val('Copy of ' + data.title);
    $modal.find('input[name=id]').val(data.id);
  });

  $body.on('updated', '.js-input-weight', debounce(function() {
    var entityNode = this.closest('.exercise-item');
    var value = Plans.calcKcal(parseInt(this.dataset.totalWeight, 10), parseInt(entityNode.dataset.kcal, 10));

    entityNode.querySelector('.js-product-kcal').textContent = Math.round(value);
    Plans.save();
  }, 200));

  $body.on('change', '.js-input-sets, .js-input-reps, .js-input-rest, .js-input-time', debounce(function(event) {
    if (!$(event.target).prop('disabled')) {
      Plans.save();
    }
  }, 300));

	$body.on('click', '.js-meal-comment-toggle', function(event) {
		event.preventDefault();

		$(this)
			.addClass('hidden')
			.next('textarea')
			.removeClass('hidden')
			.focus();
	});

	$body.on('click', '.js-delete-plan', function(event) {
    event.stopPropagation();
	  event.preventDefault();
    $('#confirm-delete').modal('show', this);
  });

	$body.on('plans.updated', function() {
		if (Plans.isMeal()) {
			Plans.refreshMealComments();
		}
	});

  $body.on('show.bs.modal', '#confirm-delete', function(event) {
    $(this).find('.btn-ok').attr('href', $(event.relatedTarget).data('href'));
  });

  $('.js-plan-chart')
    .on('shown.bs.popover', onPlanChartPopoverShown)
    .on('hidden.bs.popover', onPlanChartPopoverHidden)
    .popover(POPOVER_PLAN_CHART);

  $('.js-product-amounts')
    .on('shown.bs.popover', onProductsAmontsPopoverShown)
    .popover(POPOVER_PRODUCT_AMOUNTS);


  var isAmoutChooserOpen = false;

  $body
    .on('click', function(event) {
      var $target = $(event.target);

      if (isAmoutChooserOpen && !($target.is('.plan-weights-popover') || $target.closest('.plan-weights-popover').length)) {
        $('.js-choose-amout').popover('hide');
      }
    })
    .on('click', '.js-close-amount', function(event) {
      event.preventDefault();
      $('.js-choose-amout').popover('hide');
    })
    .on('submit', '.product-weights-form', function(event) {
      event.preventDefault();
      var $form = $(this);
      var relatedTarget = $form.data('relatedTarget');

      var $radio = $form.find('input[type=radio]:checked');
      var totalWeight = $form
        .find('input[type=number]:first')
        .val();

      var weightId = parseInt($radio.val(), 10);

      if (weightId < 0 || isNaN(weightId)) {
        weightId = 0;
      }

      var $row = $radio.closest('.product-weights-type');
      var weightUnits = weightId ? $row.find('input[type=number]').val() : 0;

      relatedTarget.parentNode.dataset.totalWeight = parseFloat(totalWeight);
      relatedTarget.parentNode.dataset.weightId = weightId;
      relatedTarget.parentNode.dataset.weightUnits = parseFloat(weightUnits);
      relatedTarget.textContent = weightId ? parseFloat(weightUnits) + ' x ' + $row.find('label').text() : totalWeight + 'g';

      $(relatedTarget)
        .popover('hide')
        .closest('.js-input-weight')
        .trigger('updated');
    })
    .on('change', '.product-weights-form input[name=weight_type]', function() {
      $(this)
        .closest('.product-weights-type')
          .find('.product-weights-amount')
            .addClass('is-visible')
            .end()
          .find('input[type=number]')
            .trigger('keyup')
            .end()
        .siblings()
          .find('.product-weights-amount')
          .removeClass('is-visible');
    })
    .on('keyup', '.product-weights-form input[type=number]', debounce(function() {
      var $input = $(this);
      var relatedTarget = $input.closest('form').data('relatedTarget');
      var $entity = $(relatedTarget).closest('.exercise-item');
      var $weightInput = $input
        .closest('form')
        .find('input[type=number]:first');

      var isWeightInput = $input[0] === $weightInput[0];
      var totalWeight;

      if (isWeightInput) {
        totalWeight = parseFloat(this.value);
      } else {
        var a = parseFloat(this.value);
        var b = parseFloat(this.dataset.weight);

        totalWeight = a * b;
      }

      var totalKcal = (parseFloat($entity.data('kcal')) / 100) * totalWeight;

      if (!isWeightInput) {
        $weightInput.val(totalWeight);
      }

      $input
        .parent()
        .find('var')
        .text(Math.round(totalKcal));

      relatedTarget.parentNode.dataset.totalKcal = totalKcal;
    }, 200))
    .on('show.bs.popover', '.js-choose-amout', function() {
      $('.js-choose-amout').not(this).popover('hide');
    })
    .on('shown.bs.popover', '.js-choose-amout', function() {
      var locale = Plans.getLocale();
      var data = $(this).parent().get(0).dataset;
      var weights = [{
        id: 0,
        name: 'Gram'
      }].concat(JSON.parse(decodeURI(data.weights)));

      weights = weights.filter(function(x) {
        return !x.hasOwnProperty('locale') || (x.locale && x.locale === locale);
      });

      var $form = $('<form class="product-weights-form"/>')
        .data('relatedTarget', this)
        .html(
          (weights.map(function(row) {
            var value = 1;
            var id = parseInt(row.id, 10);
            var isBase = id === 0;
            var isChecked = parseInt(data.weightId, 10) === id;

            if (isBase) {
              value = parseInt(data.totalWeight, 10) || 100;
            } else {
              if (isChecked) {
                value = data.weightUnits || 1;
              }
            }

            var html = (
              '<div>' +
                '<input type="radio" name="weight_type" value="' + id + '" id="weight_type_' + id + '"' + (isChecked ? ' checked' : '') + '>' +
              '</div>'
            );

            html += '<label class="control-label" for="weight_type_' + id + '">' + row.name;

            if (!isBase) {
              html += ' (' + Math.round(row.weight) + 'g)';
            }

            html += '</label>';

            if (!isBase) {
              html += '<div class="product-weights-amount' + (isChecked ? ' is-visible' : '') + '">';
            }

            html += '<input type="number" value="' + parseFloat(value) + '" min="0" step="0.1" class="form-control text-right"' + (isBase ? '' : ' data-weight="' + row.weight + '"') + '>';

            if (isBase) {
              html += '<div class="product-weights-amount' + (isChecked ? ' is-visible' : '') + '">';
            }

            if (row.id !== 0) {
              html += 'pcs. ';
            }

            html += '(<var>'+ data.totalKcal +'</var> kcal)';
            html += '</div>';


            return '<div class="product-weights-type">' + html + '</div>';
          })).join('') +
          '<div class="pull-right">' +
            '<a class="product-weights-cancel js-close-amount" href="#">Cancel</a>' +
          '</div>' +
          '<button class="btn btn-success" type="submit">Save amount</button>'
        );

      isAmoutChooserOpen = true;

      setTimeout(function() {
        $('.plan-product-popover-weights')
          .html($form)
          .find('input[name=weight_type]:checked')
          .closest('.product-weights-type')
          .find('input[type=number]')
          .trigger('keyup');
      }, 10);
    })
    .on('hidden.bs.popover', '.js-choose-amout', function() {
      isAmoutChooserOpen = false;
      $('.plan-product-popover-weights').empty();
    });

  Plans.initAmoutChooser();
})(jQuery, window);
