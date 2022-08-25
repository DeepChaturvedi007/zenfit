/**
 * @param {Element} el
 * @param {Object} options
 */
import './polyfill';
import './index.scss';
import 'custom-event-polyfill';
import Cookies from 'js-cookie';
import Sortable from 'sortablejs';
import Drop from 'tether-drop';
import * as delegate from 'delegated-events';
import {autobind} from 'core-decorators';
import {html} from 'common-tags';
import _ from 'underscore';
import fastdom from 'fastdom';
import Queue from "./libs/Queue";
import api from './api';
import * as Templates from './templates';
import { percent } from './helpers';

global.Drop = Drop;

import {
  LOCALE_KEY,
  TYPE_MEAL,
  TYPE_WORKOUT,
  TYPE_RECIPE,
  IS_FIREFOX,
  IS_TOUCH,
  DROP_SET_TEXT,
  DROP_SET_RE
} from './constants';


const $ = global.jQuery || {};
const sortables = new Map();
const charts = new Map();
const drops = new Map();


const SORTABLE_SCROLL_SENSITIVITY = 120;

$.if = function (expression, truthy) {
  if (expression) {
    return {
      else() {
        return truthy();
      }
    };
  } else {
    return {
      else(falsey) {
        return falsey();
      }
    }
  }
};

function randId() {
  return Math.random().toString(36).substr(2, 10);
}

function addSortable(el, options) {
  if (el && !sortables.has(el)) {
    sortables.set(el, Sortable.create(el, options));
  }
}

/**
 * @param {Element} el
 */
function removeSortable(el) {
  if (sortables.has(el)) {
    sortables.get(el).destroy();
    sortables.delete(el);
  }
}

/**
 * @param {Element} el
 * @param {Object} options
 */
function addDrop(el, options = {}) {
  if (!drops.has(el)) {
    drops.set(el, new Drop(options));
  }
  return drops.get(el);
}

/**
 * @param {Element} el
 */
function removeDrop(el) {
  if (charts.has(el)) {
    charts.get(el).destroy();
    charts.delete(el);
  }
}

function closeAllDrops() {
  Drop.drops.forEach(x => x.close());
}

/**
 * @param {string} html
 * @return {Node}
 */
function htmlToElement(html) {
  const template = document.createElement('template');
  template.innerHTML = html.trim();

  return template.content || template.firstChild;
}

/**
 * @param {Event} e
 */
function stopEvent(e) {
  e.stopPropagation();
  e.preventDefault();
}

function insertAfter(newNode, referenceNode) {
  referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

/**
 * @param {Element} target
 * @param {String} selector
 * @return {string|*}
 */
function secureNodeValue(target, selector) {
  return (target.querySelector(selector) || {}).value;
}

/**
 * @param {Element} target
 * @param {String} selector
 * @return {string|*}
 */
function securedNodeData(target, selector) {
  return (target.querySelector(selector) || {}).dataset || {};
}

/**
 * @param {Element} el
 * @param {Number?} order
 * @return {{id, order: number, workout_id: *, comment: (string|*), reps: (string|*), time: (string|*), sets: (string|*), rest: (string|*)}}
 */
function serializeExerciseItem(el, order = 0) {
  return {
    id: parseInt(el.dataset.exerciseId, 10) || 0,
    order,
    workout_id: parseInt(el.dataset.id, 10) || 0,
    comment: secureNodeValue(el, '.js-plan-item-comment'),
    reps: secureNodeValue(el, '.plan-item-input-reps'),
    time: secureNodeValue(el, '.plan-item-input-time'),
    sets: secureNodeValue(el, '.plan-item-input-sets'),
    rest: secureNodeValue(el, '.plan-item-input-rest'),
    start_weight: secureNodeValue(el, '.plan-item-input-weight'),
    tempo: secureNodeValue(el, '.plan-item-input-tempo'),
    rm: secureNodeValue(el, '.plan-item-input-rm'),
    superset: [],
  };
}

/**
 *
 * @param {Element} el
 * @param {Number?} order
 * @return {{id, order: number, totalWeight: (Number|number), weightId: (Number|number), weightUnits: Number, entity_id: (*|null)}}
 */
function serializeProductItem(el, order = 0) {
  return {
    id: parseInt(el.dataset.productId, 10) || 0,
    order,
    totalWeight: parseFloat(el.dataset.totalWeight) || 0,
    weightId: parseInt(el.dataset.weightId, 10) || 0,
    weightUnits: parseFloat(el.dataset.weightUnits),
    entity_id: parseInt(el.dataset.id, 10) || 0,
  };
}

var mealPlanTitle = null;
var mealPlanComment = null;
var duration = null;
var level = null;
var gender = null;
var location = null;
var workoutsPerWeek = null;

/**
 * Plans
 */
class Plans {
  static WORKOUT = TYPE_WORKOUT;
  static MEAL = TYPE_MEAL;
  static RECIPE = TYPE_RECIPE;

  /**
   * @type {Element}
   */
  container = null;
  /**
   * @type {TYPE_WORKOUT|TYPE_MEAL|TYPE_RECIPE}
   */
  type = TYPE_WORKOUT;
  /**
   * @type {boolean}
   */
  options = {
    client: undefined,
    updatedAt: undefined,
    locale: Cookies.get(LOCALE_KEY),
    template: null,
    empty: undefined,
    plan: undefined,
    settings: {},
    tour: undefined,
    youtubeApiKey: '',
  };
  data = {
    equipments: [],
    muscles: [],
  };

  searchParams = {
    q: '',
    page: 1,
    limit: 10,
  };

  youtubeParams = {
    nextPageToken: null,
    prevPageToken: null,
    pageInfo: {},
  };

  /**
   * @param {Element} container
   * @param {TYPE_WORKOUT|TYPE_MEAL|TYPE_RECIPE} type
   * @param {Object?} options
   * @throws TypeError
   */
  constructor(container, type, options = {}) {
    if (!container) {
      throw new TypeError('You must define plans container element');
    }

    if (!type) {
      throw new TypeError('You must define plans type');
    }

    this.save = _.debounce(this.save, 200);
    this.updatePlan = _.debounce(this.updatePlan, 200);
    this.onSearchScroll = _.debounce(this.onSearchScroll, 200);
    this.onProductAmountUpdate = _.debounce(this.onProductAmountUpdate, 50);
    this.onCopySets = _.debounce(this.onCopySets, 200);

    const body = document.body;

    if (body) {
      body.classList.add('no-scroll');
    }

    this.container = container;
    this.type = type;
    this.saveQueue = new Queue(true);

    if (this.isWorkout) {
      this.searchParams.equipmentId = '';
      this.searchParams.muscleId = '';
    }

    if (window.location.hash) {
      this.searchParams.q = decodeURI(window.location.hash.substring(1))
        .replace(/\+/g, ' ');
    }

    this.container.classList.add('plans');
    this.container.classList.add(IS_TOUCH ? 'plans-touch' : 'plans-no-touch');
    this.container.dataset.type = this.type;

    this.setOptions(options);
    //this.setOptions({ locale: Cookies.get('plans_locale')})
    this.initiatePlan();
  }

  initiatePlan() {
    this.fetchFilters()
      .then(() => {
        this.fetchPlans()
          .then((res) => {
            const data = this.isRecipe ? res.data : _.values(res.data);

            this.empty = _.isEmpty(data);
            this.createLayout(data);
            this.addListeners();

            if (this.isRecipe) {
              this.addPlan(res.data);
            } else {
              Object.keys(data).map(plan => this.addPlan(data[plan]));
            }

            this.search();

            fastdom.mutate(() => {
              $('[data-toggle="tooltip"]').tooltip({
                container: 'body',
              })
            });
          });
      });
  }

  /**
   * @param {Object} options
   */
  setOptions(options = {}) {
    if (typeof options !== 'object' || options === null) {
      throw new TypeError('Plans options is not a Object');
    }

    for (let prop in options) {
      if (options.hasOwnProperty(prop)) {
        this.options[prop] = options[prop];
      }
    }

  }

  /**
   * @param {Array<Object>} data
   */
  createLayout(data = []) {
    this.container.innerHTML = Templates.Layout({
      data,
      type: this.type,
      locale: this.locale,
      equipments: this.data.equipments,
      muscles: this.data.muscles,
      updatedAt: this.options.updatedAt,
      empty: this.empty,
      client: this.client,
      q: this.searchParams.q,
      plan: this.plan,
      options: this.options,
      isWorkout: this.isWorkout
    }, this.isTemplate);
  }

  addListeners() {
    const board = this.board = this.container.querySelector('.plans-board');
    const boardComment = this.boardComment = this.container.querySelector('.plans-board-comment textarea');
    const boardList = this.boardList = this.container.querySelector('.plans-board-list');
    const searchResults = this.searchResults = this.container.querySelector('.plans-search-result');

    addSortable(searchResults, this.sortableSearchOptions);
    if(this.isWorkout) {
      addSortable(boardList, this.sortablePlansOptions);
    }

    if (boardComment && autosize) {
      autosize(boardComment);
    }

    if (searchResults) {
      searchResults
        .addEventListener('scroll', this.onSearchScroll);
    }

    const searchForm = this.container.querySelector('.plans-search-form');

    if (searchForm) {
      searchForm
        .addEventListener('submit', this.onSearchFormSubmit);

      searchForm
        .querySelectorAll('select')
        .forEach(el => el.addEventListener('change', this.onSearchFilterChange));
    }

    const delegateCtx = {context: this};

    this.on('change', '#own-exercise', function (e, el) {
      this.search(e.target.checked);

    }, delegateCtx);

    this.on('click', '.js-plan-toggle', function (e, el) {
      stopEvent(e);
      this.togglePlan(el);
    }, delegateCtx);

    this.on('click', '.js-plan-delete', function (e, el) {
      stopEvent(e);

      let message;

      if (this.isWorkout) {
        message = 'Are you sure you want to remove this workout day?';
      } else {
        message = el.closest('.plans-meal-plan') ?
          'Are you sure you want to remove this meal?' :
          'Are you sure you want to remove this meal plan?';
      }

      this.confirmDelete(message, (confirmed) => {
        if (confirmed) {
          this.removePlan(el);
        }
      });
    }, delegateCtx);

    this.on('click', '.js-plan-item-remove', function (e, el) {
      stopEvent(e);

      if (this.isWorkout) {
        if (el.classList.contains('delete-superset')) {
          this.confirmDelete('Are you sure you want to cancel this superset?', (confirmed) => {
            if (confirmed) {
              this.removePlanExercise(el);
            }
          });
        } else {
          this.removePlanExercise(el);
        }
      } else {
        this.removePlanProduct(el);
      }
    }, delegateCtx);

    this.on('change', '.plans-box-comment textarea', function () {
      this.saveQueue.add(this.save);
    }, delegateCtx);

    this.on('change', '.plans-board-comment textarea', this.updatePlan, delegateCtx);

    this.on('click', '.plans-box-comment a', function (e, el) {
      stopEvent(e);

      el.classList.add('hidden');
      el.nextElementSibling.classList.remove('hidden');
    }, delegateCtx);

    this.on('submit', '#addPlanModal form', this.onAddEditPlan, delegateCtx);
    this.on('submit', '#editTemplateText form', this.onAddEditPlanInfo, delegateCtx);

    if (this.isMeal || this.isRecipe) {
      this.on('click', '.js-plans-locale', this.onSearchLocaleChange, delegateCtx);
      this.on('click', '.product-weights-cancel', this.onProductAmountCancel, delegateCtx);
      this.on('change', '.plan-item-amount-radio', this.onProductAmountSwitch, delegateCtx);
      this.on('keyup', '.plan-item-amount-value', this.onProductAmountUpdate, delegateCtx);
      this.on('submit', '.plan-item-amount-form', this.onProductAmountSubmit, delegateCtx);
      this.on('plan:calc', '.plans-box', this.onPlanCalculate, delegateCtx);
      this.on('product:calc', '.plan-item', this.onProductCalculate, delegateCtx);
    }

    if (this.isMeal) {
      this.on('meal:calc', '.plans-meal-plan', this.onMealCalculate, delegateCtx);
    }

    if (this.isRecipe) {
      this.on('meal:calc', '.plans-box-item-list', this.onMealCalculate, delegateCtx);
    }

    if (this.isWorkout) {
      this.on('plan:calc', '.plans-box', this.onPlanWorkoutCalculate, delegateCtx);
      this.on('click', '.js-plan-item-set-comment', function (e, el) {
        stopEvent(e);
        this.toggleItemComment(el);
      }, delegateCtx);

      this.on('click', '.js-plan-item-set-dropset', function (e, el) {
        stopEvent(e);

        this.toggleItemComment(el, undefined, true);
        this.saveQueue.add(this.save);
      }, delegateCtx);

      this.on('change', '.js-plan-item-comment', function (e, el) {
        if (!el.value) {
          this.toggleItemComment(el, true);
        }

        this.saveQueue.add(this.save);
      }, delegateCtx);

      this.on('click', '.js-plan-item-set-superset', function (e, el) {
        stopEvent(e);
        this.switchItemSuperSet(el);
      }, delegateCtx);


      const itemInputsSelector = ['reps', 'rest', 'sets', 'time', 'weight', 'tempo', 'rm']
        .map(input => `.plan-item-input-${input}`)
        .join(', ');

      this.on('change', itemInputsSelector, function (e, el) {
        this.saveQueue.add(this.save);
      }, delegateCtx);

      this.on('click', '.js-more-youtube-results', function (e, el) {
        stopEvent(e);
        this.search();
      }, delegateCtx);
    }
  }
  on(eventName, selector, fn, options = {}) {
    delegate.on(eventName, selector, function (e) {
      return fn.apply(options.context || this, [e, this]);
    }, options);
  }

  @autobind
  save(reload = false) {
    let promise;

    if (this.isWorkout) {
      promise = api.saveWorkout(this.plan.id, this.workoutFormData);
    } else if (this.isMeal) {
      promise = api.saveMeals(this.plan.id, this.mealFormData, {locale: this.locale});
    } else if (this.isRecipe) {
      const data = Object.assign(this.recipeFormData, {'locale': this.locale});
      promise = api.syncRecipe(this.plan.id, data)
    }

    if (!promise) {
      return;
    }

    promise
      .then((response) => {
        let titlePrefix;

        if (this.isTemplate) {
          titlePrefix = 'Template changes';
        } else {
          if (this.isRecipe) {
            titlePrefix = 'Recipe';
          } else {
            titlePrefix = this.isWorkout ? 'Workout Plan' : 'Meal Plan';
          }
        }

        let description = '';

        if (this.isWorkout) {
          description = this.isTemplate ?
            'Workout Template is ready to be assigned to your clients!' :
            'Changes will appear automatically in your client\'s Zenfit Mobile App.';
        }

        if (this.isMeal) {
          this.updateMealIds(response.data);
        } else if (this.isRecipe) {
          this.updateRecipeIds(response.data);
        } else if (this.isWorkout) {
          try {
            this.updateWorkoutIds(response.data);
            const itemList = this.boardList.querySelector('.plans-box-item-list');
          } catch (e) {
            console.log('Plans::save(error):'.e);
          }
        }

        toastr.options.preventDuplicates = true;
        toastr.success(description, titlePrefix + ' updated!');

        if(reload) {
          window.location.reload();
        }

      })
      .catch((error) => {
        let titlePrefix;

        if (this.isTemplate) {
          titlePrefix = 'Template changes';
        } else {
          if (this.isRecipe) {
            titlePrefix = 'Recipe';
          } else {
            titlePrefix = this.isWorkout ? 'Workout Plan' : 'Meal Plan';
          }
        }

        toastr.options.preventDuplicates = true;
        toastr.error(titlePrefix + ' Plan save failed');
      });

    return promise;
  }

  @autobind
  updatePlan() {
    let promise = null;
    let data = {};

    if (this.client) {
      data.client = this.client.id;
    }

    if (this.isWorkout) {
      data.comment = this.boardComment.value;

      promise = api.updateWorkoutPlan(this.plan.id, data);
      promise
        .then(() => {
          toastr.options.preventDuplicates = true;
          toastr.success('Workout Plan successfully updated.', 'Workout Plan');
        })
        .catch(() => {
          toastr.options.preventDuplicates = true;
          toastr.error('Workout plan update failed.', 'Workout Plan');
        });
    }

    return promise;
  }

  search(showOnlyOwn) {

    if (this.searchLoading) {
      return;
    }

    this.searchParams.limit = this.searchParams.page > 1 ? 10 : 25;
    if(showOnlyOwn !== undefined){
      this.searchParams.showOnlyOwn = showOnlyOwn
    }

    const params = {...this.searchParams, ...{locale: this.locale}};

    const method = this.type === TYPE_WORKOUT ? api.getExercises : api.getMealProducts;
    const plansSearch = this.container.querySelector('.plans-search');
    const searchResults = {
      local: [],
      youtube: [],
    };

    if (plansSearch) {
      plansSearch.classList.add('is-loading');
    }

    this.searchLoading = true;
    method(params)
      .then(response => {
        searchResults.local = response.data;

        if (this.isWorkout && this.searchParams.q && response.data.length < this.searchParams.limit) {
          return api.searchYoutube(this.searchParams.q, this.options.youtubeApiKey, 20, 'snippet', this.youtubeParams);
        }

        return response;
      })
      .then(response => {
        let nextYoutubeParams = {
          ...this.youtubeParams,
        };

        if (this.isWorkout && Array.isArray(response.data.items)) {
          if (this.searchParams.page === 1 && !searchResults.local.length && !this.youtubeParams.nextPageToken) {
            this.searchResults.innerHTML = '';
          }

          nextYoutubeParams = {
            ...nextYoutubeParams,
            nextPageToken: response.data.nextPageToken,
            prevPageToken: response.data.prevPageToken,
            pageInfo: response.data.prevPageToken,
          };

          searchResults.youtube = response.data.items
            .filter(item => item.id.kind === 'youtube#video')
            .slice(0, 10);
        }

        this.searchLoading = false;
        this.renderSearchResults(searchResults, params.q);

        if (plansSearch) {
          plansSearch.classList.remove('is-loading');
        }

        this.youtubeParams = nextYoutubeParams;
      })
      .catch(error => {
        this.searchLoading = false;

        if (plansSearch) {
          plansSearch.classList.remove('is-loading');
        }
      });
  }

  renderSearchResults(items, q) {
    let content = '';
    let hasItems = items.local.length;

    if (hasItems && this.isWorkout) {
      hasItems = !this.youtubeParams.nextPageToken;
    }

    if (hasItems) {
      let template = this.isWorkout ? Templates.SearchExerciseItem : Templates.SearchProductItem;

      content = items.local
        .map(item => template(item, this.locale, this.options, this.searchParams))
        .join(`\n`);
    }

    if (this.isWorkout && items.youtube.length) {
      if (!this.searchResults.querySelector('.plans-search-youtube-header')) {
        content += `${Templates.SearchYoutubeHeader()}\n`;
      }

      content += items.youtube
        .map(item => Templates.SearchExerciseYoutubeItem(item, this.locale, this.options, this.searchParams))
        .join(`\n`);

      const youtubeFooter = this.searchResults.querySelector('.plans-search-youtube-footer');
      const emptyState = this.searchResults.querySelector('.search-result-empty-state');

      if (youtubeFooter) {
        youtubeFooter.parentNode.removeChild(youtubeFooter);
      }

      if (emptyState) {
        emptyState.parentNode.removeChild(emptyState);
      }

      content += `${Templates.SearchYoutubeFooter()}\n`;
    }

    if (this.searchParams.page > 1 || this.youtubeParams.nextPageToken) {
      this.searchResults.appendChild(htmlToElement(content));
    } else {
      this.searchResults.innerHTML = content;
      this.searchResults.scrollTop = 0;
    }

    if (!content || items.local.length < this.searchParams.limit) {
      if (this.searchResults.querySelector('.search-result-empty-state')) {
        return;
      }

      const itemCount = this.searchResults.querySelectorAll('.plan-item').length;

      content = Templates.EmptyStateSearch(this.type, q, itemCount);

      if (itemCount) {
        this.searchResults.appendChild(htmlToElement(content));
      } else {
        this.searchResults.innerHTML = content;
      }
    }
  }

  /**
   * @param {Object} plan
   */
  addPlan(plan) {
    this.boardList.appendChild(htmlToElement(Templates.Plan(plan, this.type, this.plan, this.options)));

    const el = this.boardList.querySelector(`.plans-box[data-id="${plan.id}"]`);
    const list = el.querySelector('.plans-box-item-list');
    const job = {
      sortable: null,
      entities: [],
    };

    if (this.isWorkout) {
      job.sortable = this.sortableItemsOptions;
      job.entities = ['workouts', 'addPlanExercise'];
    } else if (this.isMeal) {
      job.sortable = this.sortableMealsOptions;
      job.entities = ['meals', 'addPlanMeal'];
    } else if (this.isRecipe) {
      job.sortable = this.sortableItemsOptions;
      job.entities = ['products', 'addPlanProduct'];
    }

    if (job.sortable) {
      addSortable(list, job.sortable);
    }

    fastdom.mutate(() => {
      const [entities, action] = job.entities;

      if (Array.isArray(plan[entities])) {
        plan[entities].forEach(item => this[action](list, item));
      }

      fastdom.mutate(() => {
        el
          .querySelectorAll('textarea')
          .forEach(node => autosize && autosize(node));
      });
    });
  }

  confirmDelete(message, callback) {
    bootbox.confirm(message, callback);
  }

  /**
   * @param {Element} el
   */
  removePlan(el) {
    let plan;
    let planBox;
    let selectors = {
      workout: '.plans-box',
      meal: '.plans-meal-plan'
    };

    if (this.isMeal) {
      plan = el.closest(selectors.meal);

      if (plan) {
        planBox = plan.closest('.plans-box');
      }
    }

    if (!plan) {
      plan = el.closest(selectors.workout)
    }

    if (plan) {
      plan.querySelectorAll('.plans-box-item-list')
        .forEach(list => removeSortable(list));

      plan.querySelectorAll('textarea')
        .forEach(node => autosize && autosize.destroy(node));

      if (this.isMeal) {
        removeDrop(plan);
      }

      if (plan.parentNode) {
        plan.remove();
      }

      if (planBox) {
        delegate.fire(planBox, 'plan:calc');
      }

      const checkCount = () => {
        const totalPlans = document.querySelectorAll(this.isMeal ? selectors.meal : selectors.workout).length;

        if (!totalPlans) {
          window.location.reload();
        }
      };

      this.saveQueue.add(() => {
        const promise = this.save();

        if (promise instanceof Promise) {
          promise.then(checkCount);
          return promise;
        } else {
          checkCount();
        }
      });
    }
  }

  addPlanMeal(target, meal) {
    try {
      // const content = Templates.PlanMeal(meal, this.type, this.locale);
      // const el = htmlToElement(content);
      target.appendChild(htmlToElement(Templates.PlanMeal(meal, this.type, this.locale)));

      const el = target.querySelector(`.plans-meal-plan[data-id="${meal.id}"]`);
      const list = el.querySelector('.plans-box-item-list--products');

      addSortable(list, this.sortableItemsOptions);

      fastdom.mutate(() => {
        meal.products.forEach(product => this.addPlanProduct(list, product));
      });

      fastdom.mutate(() => {
        const headerTitle = el.querySelector('.plans-box-header-main > h5 > span');
        const drop = addDrop(el, {
          target: headerTitle,
          classes: 'drop-theme-arrows plan-meal-totals-drop',
          content(e) {
            const plan = e.target.closest('.plans-meal-plan');
            const weight = parseFloat(plan.dataset.weight);

            const getBaseValue = value => (parseFloat(value) / weight) * 100;

            const totals = e.mealTotals = {
              fat: getBaseValue(plan.dataset.fat),
              protein: getBaseValue(plan.dataset.protein),
              carbohydrate: getBaseValue(plan.dataset.carbohydrate),
              kcal: getBaseValue(plan.dataset.kcal),
            };

            return Templates.PlanTotalsDrop({
              title: meal.name,
              ...totals
            }, weight, true);
          },
          position: 'right center',
          openOn: 'hover',
          constrainToWindow: false,
          constrainToScrollParent: true,
          remove: true,
        });
      });
    } catch (e) {
      console.log('Plans::addPlanMeal(error):', e);
    }
  }

  /**
   * @param {Element} target
   * @param {Object} workout
   * @param {Boolean=} replace
   */
  addPlanExercise(target, workout, replace = false) {
    const isSuperSet = Array.isArray(workout.supers) && Boolean(workout.supers.length);
    let content = Templates.PlanExercise(workout, this.options);

    if (isSuperSet) {
      content = Templates.PlanSuperSet({
        id: workout.id,
        children: content
      }, this.type);
    }

    const el = htmlToElement(content);
    let refreshSuperSet = null;

    if (isSuperSet) {
      const childrenItemList = el.querySelector('.plans-box-item-list');

      if (childrenItemList) {
        childrenItemList.dataset.count = workout.supers.length;
        workout.supers.forEach((childrenWorkout) => this.addPlanExercise(childrenItemList, childrenWorkout));
      }

      this.enableSuperSet(el);
    } else {
      const superSetLink = el.querySelector('.js-plan-item-set-superset');
      const isChildren = Boolean(target.closest('[data-children]'));

      if (superSetLink) {
        superSetLink.classList.toggle('is-hidden', isChildren);
      }

      if (isChildren) {
        let lastRest;
        let lastSets;

        if (target.previousElementSibling && !target.nextElementSibling) {
          lastRest = lastSets = target.previousElementSibling;
        } else if (target.nextElementSibling) {
          lastSets = target.nextElementSibling;
        } else {
          lastRest = lastSets = target.closest('.plan-superset').querySelector('.plan-item');
        }

        try {
          if (lastRest) {
            lastRest = lastRest.querySelector('.plan-item-input-rest').value
          }

          if (lastSets) {
            lastSets = lastSets.querySelector('.plan-item-input-sets').value
          }

          refreshSuperSet = [
            target.closest('.plan-superset'),
            lastRest,
            lastSets
          ];
        } catch (e) {
        }
      }
    }

    if (replace) {
      target.parentNode.replaceChild(el, target);
    } else {
      target.appendChild(el);
    }

    if (refreshSuperSet) {
      this.enableSuperSet(...refreshSuperSet);
    }
  }

  removePlanExercise(el) {
    const item = el.classList.contains('plan-item') ? el : el.closest('.plan-item');

    if (!item) {
      return;
    }

    const planBox = item.closest('.plans-box');
    const parent = item.parentNode;
    const itemList = item.closest('.plans-box-item-list');

    if (parent.classList.contains('plan-superset')) {
      const list = parent.querySelector('.plans-box-item-list');

      removeSortable(list);

      parent
        .querySelectorAll('.plan-item-input-sets')
        .forEach(node => node.removeEventListener('change', this.onCopySets));

      parent.querySelectorAll('textarea')
        .forEach(node => autosize && autosize.destroy(node));

      if (parent.parentNode) {
        parent.remove();
      }

      itemList.dataset.count = itemList.children.length;

    } else {
      const superSet = item.closest('.plan-superset');
      const restValue = superSet ? item.querySelector('.plan-item-input-rest').value : null;


      item.querySelectorAll('textarea')
        .forEach(node => autosize && autosize.destroy(node));

      if (item.parentNode) {
        item.remove();
      }

      itemList.dataset.count = itemList.children.length;

      if (superSet) {
        this.enableSuperSet(superSet, restValue)
      }
    }

    if (planBox) {
      delegate.fire(planBox, 'plan:calc');
    }

    this.saveQueue.add(this.save);
  }

  /**
   * ]
   * @param {Element} target
   * @param {Object} product
   * @param {Boolean=} replace
   * @param {Boolean=} openAmountChooser
   */
  addPlanProduct(target, product, replace = false, openAmountChooser = false) {
    let content = Templates.PlanProduct(product, this.locale, this.options);
    const el = htmlToElement(content);
    const title = el.querySelector('.plan-item-title');
    const amountHandler = el.querySelector('.js-plans-choose-amount');

    if (replace) {
      target.parentNode.replaceChild(el, target);
    } else {
      target.appendChild(el);
    }

    fastdom.mutate(() => {
      const amountDrop = addDrop(amountHandler, {
        target: amountHandler,
        classes: 'drop-theme-arrows plan-item-amount-drop',
        content: Templates.PlanMealAmountDrop(product, this.locale),
        position: 'bottom center',
        openOn: 'click',
        constrainToWindow: true,
        constrainToScrollParent: true,
        remove: true,
      });

      const that = this;

      amountDrop.on('open', function onDropOpen() {
        that.amountEntity = this;

        const form = this.content.querySelector('.plan-item-amount-form');

        if (form) {
          const radioInput = form.querySelector('input:checked');

          if (radioInput) {
            delegate.fire(radioInput, 'change');
          }
        }
      });

      // amountDrop.on('close', function onDropClose() {
      //   that.amountEntity = null;
      // });

      addDrop(title, {
        target: title,
        classes: 'drop-theme-arrows plan-meal-totals-drop',
        content(e) {
          const plan = e.target.closest('.plan-item');
          return Templates.PlanTotalsDrop({
            title: product.product.name,
            fat: parseFloat(plan.dataset.fat),
            protein: parseFloat(plan.dataset.protein),
            carbohydrate: parseFloat(plan.dataset.carbohydrates),
            kcal: parseFloat(plan.dataset.kcal),
          }, parseFloat(plan.dataset.totalWeight));
        },
        position: 'right center',
        openOn: 'hover',
        constrainToWindow: false,
        constrainToScrollParent: true,
        remove: true,
      });

      if (openAmountChooser) {
        amountDrop.open();
      }
    });
  }

  removePlanProduct(el) {
    const product = el.closest('.plan-item');

    if (product) {
      const itemList = product.closest('.plans-box-item-list');
      const mealPlan = product.closest('.plans-meal-plan');
      const box = product.closest('.plans-box');

      removeDrop(el.querySelector('.plan-item-title'));
      removeDrop(el.querySelector('.js-plans-choose-amount'));

      if (product.parentNode) {
        product.remove();
      }

      itemList.dataset.count = itemList.children.length;

      if (this.isMeal) {
        delegate.fire(mealPlan, 'meal:calc');
      } else if (this.isRecipe) {
        delegate.fire(itemList, 'meal:calc');
      }

      delegate.fire(box, 'plan:calc');
    }

    this.saveQueue.add(this.save);
  }

  /**
   *
   * @param {Element|Node} el
   * @param {String?} restValue
   */
  enableSuperSet(el, restValue, setsValue) {
    const list = el.querySelector('.plans-box-item-list');

    if (list) {
      addSortable(list, this.sortableItemsOptions);
    }

    const setsInputs = el.querySelectorAll('.plan-item-input-sets');
    const totalSets = setsInputs.length;
    const lastSetsIndex = totalSets - 1;

    setsInputs.forEach((node, index) => {
      node.disabled = index !== lastSetsIndex;

      if (totalSets <= 1 || index !== lastSetsIndex) {
        node.removeEventListener('change', this.onCopySets);
      } else if (index === lastSetsIndex) {
        node.addEventListener('change', this.onCopySets);

        if (setsValue) {
          node.value = setsValue;
        }
      }
    });

    const restInputs = el.querySelectorAll('.plan-item-input-rest');
    const lastRestIndex = restInputs.length - 1;

    restInputs.forEach((node, index) => {
      if (index === lastRestIndex) {
        node.disabled = false;

        if (restValue) {
          node.value = restValue;
        }
      } else {
        node.value = 0;
        node.disabled = true;
      }
    });
  }

  disableSuperSet(el) {
    const list = el.querySelector('.plans-box-item-list');
    removeSortable(list);
  }

  togglePlan(el, collapsed) {
    const plan = el.classList.contains('plans-box') ? el : el.closest('.plans-box');

    if (plan) {
      if (typeof collapsed !== 'boolean') {
        collapsed = !plan.classList.contains('is-collapsed');
      }

      const icon = $
        .if(/button/i.test(el.tagName), () => el.querySelector('span'))
        .else(() => plan.querySelector('button.js-plan-toggle > span'));

      plan.classList.toggle('is-collapsed', collapsed);

      this.updateCollapsedDescription(plan, this.type);

      if (icon) {
        icon.textContent = collapsed ? 'expand_more' : 'expand_less';
      }

      if (!collapsed) {
        plan.querySelectorAll('textarea')
          .forEach(node => autosize && autosize.update(node));
      }
    }
  }

  updateCollapsedDescription(el, type) {
    const collapsedDescription = el.querySelector('.plans-box-collapsed-description');

    if (type === TYPE_WORKOUT) {
      const supersets = el.querySelectorAll('.plans-box-item-list > div.plan-superset').length;
      const items = el.querySelectorAll('.plans-box-item-list div.plan-item').length;

      collapsedDescription.innerHTML = `
      <span>${items} Exercises in Total</span>
      <span>${supersets} Super Sets</span>`;
    } else {
      const meals = [...el.querySelectorAll('.plans-box-item-list div.plans-meal-plan')];
      let kcal = meals
        .reduce((total, node) => (total + (parseInt(node.getAttribute('data-kcal'), 10) || 0)), 0);

      if (el.dataset.containsAlternatives === 'true') {
        kcal = kcal / el.dataset.meals;
      }

      collapsedDescription.innerHTML = el.dataset.containsAlternatives === 'true' ?
      `${meals.length} alternatives with ${Math.round(kcal)} kcals on average.` :
      `${meals.length} meals with ${Math.round(kcal)} kcal.`;
    }

  }

  toggleItemComment(el, isHidden, isDropSet = false) {
    const item = el.classList.contains('plan-item') ? el : el.closest('.plan-item');

    if (item) {
      const commentHandle = item.querySelector('.js-plan-item-set-comment');
      const dropSetHandle = item.querySelector('.js-plan-item-set-dropset');
      const comment = item.querySelector('.plan-item-comment');
      const input = comment.querySelector('textarea');
      let value = input.value.trim();

      if (isDropSet) {
        value = (DROP_SET_RE.test(value) ? value.replace(DROP_SET_TEXT, '') : `${DROP_SET_TEXT}\n${value}`).trim();
      }

      if (typeof isHidden !== 'boolean') {
        isHidden = isDropSet ? !Boolean(value) : !comment.classList.contains('is-hidden');
      }

      const switchHandle = (handle, isActive) => {
        handle.classList.toggle('is-active', isActive);
        handle.textContent = handle.dataset[isActive ? 'on' : 'off'];
      };

      comment.classList.toggle('is-hidden', isHidden);
      input.value = isHidden ? '' : value;

      switchHandle(commentHandle, !isHidden);
      switchHandle(dropSetHandle, DROP_SET_RE.test(input.value));
    }
  }

  switchItemSuperSet(el) {
    let item = el.closest('.plan-item');

    if (item) {
      let parent = item.parentNode;
      let isSuperSet = parent.classList.contains('plan-superset');
      let handle = item.querySelector('.js-plan-item-set-superset');

      const handleSave = (isActive) => {
        handle = item.querySelector('.js-plan-item-set-superset');
        handle.classList.toggle('is-active', isActive);
        handle.textContent = isActive ? handle.dataset.on : handle.dataset.off;

        this.saveQueue.add(this.save);
      };

      if (isSuperSet) {
        this.confirmDelete('Are you sure you want to cancel this superset?', (confirmed) => {
          if (confirmed) {
            this.disableSuperSet(parent);

            parent.parentNode.replaceChild(item, parent);

            item
              .querySelectorAll('input[type="text"]')
              .forEach((node) => {
                node.removeEventListener('change', this.onCopySets);
                node.disabled = false;
              });

            handle.classList.remove('is-active');
            handle.textContent = handle.dataset.off;

            handleSave(false);
          }
        });
      } else {
        const id = item.dataset.id || randId();

        parent = htmlToElement(Templates.PlanSuperSet({id}, this.type));
        insertAfter(item.cloneNode(true), parent.querySelector('.plan-superset-divider'));

        item.parentNode.replaceChild(parent, item);
        item = this.boardList.querySelector(`.plan-superset[data-id="${id}"]`);

        this.enableSuperSet(item);

        handleSave(true);
      }
    }
  }

  updateMealIds(data) {
    data.meals.forEach((meal) => {
      const mealPlan = this.board.querySelector(`.plans-meal-plan[data-id="${meal.id}"]`);

      if (mealPlan) {
        const planProducts = mealPlan.querySelectorAll('.plan-item');

        meal.products.forEach((id, index) => {
          const planProduct = planProducts[index];

          if (planProduct) {
            planProduct.dataset.id = id;
          }
        });
      }
    });
  }

  updateRecipeIds(data) {
    const recipeProducts = this.board.querySelectorAll('.plan-item');

    data.products.forEach((id, index) => {
      const recipeProduct = recipeProducts[index];

      if (recipeProduct) {
        recipeProduct.dataset.id = id;
      }
    });
  }

  updateWorkoutIds(data) {
    if (!Array.isArray(data)) {
      return;
    }

    data.forEach((day) => {
      const workoutDay = this.board.querySelector(`.plans-box[data-id="${day.workout_day_id}"] .plans-box-item-list`);

      if (workoutDay) {
        const dayExercises = workoutDay.children;

        day.workouts.forEach((exercise, index) => {
          let dayExercise = dayExercises[index];
          let daySuperExercises = [];

          if (dayExercise) {
            if (dayExercise.classList.contains('plan-superset')) {
              daySuperExercises = dayExercise.querySelectorAll('.plans-box-item-list > .plan-item');
              dayExercise = dayExercise.querySelector('.plan-item');
            }

            dayExercise.dataset.id = exercise.workout_id;

            exercise.sub_workouts.forEach((superExercise, superIndex) => {
              const daySuperExercise = daySuperExercises[superIndex];

              if (daySuperExercise) {
                daySuperExercise.dataset.id = superExercise.workout_id;
              }
            });
          }
        });
      }
    });
  }

  @autobind
  onSearchScroll(e) {
    const target = e.target;

    if ((target.scrollTop + target.clientHeight)+5 > target.scrollHeight) {
      if (this.searchResults.querySelector('.search-result-empty-state')) {
        return;
      }

      this.searchParams.page += 1;
      this.search();
    }
  }

  @autobind
  onSearchFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const input = form.querySelector('input[name="q"]');

    if (!input.value || input.value !== this.searchParams.q) {
      this.searchParams.equipmentId = '';
      this.searchParams.muscleId = '';

      form.querySelectorAll('select').forEach(el => el.selectedIndex = 0)
    }

    this.searchParams.q = input.value;
    this.searchParams.page = 1;
    this.youtubeParams = {
      nextPageToken: null,
      prevPageToken: null,
      pageInfo: {},
    };

    this.search();
  }

  @autobind
  onSearchFilterChange(e) {
    const target = e.target;
    const key = target.getAttribute('data-key');

    this.searchParams[key] = target.options[target.selectedIndex].value;
    this.searchParams.page = 1;
    this.search();
  }

  @autobind
  onSearchLocaleChange(e, el) {
    stopEvent(e);
    this.locale = el.dataset.lang;
    Cookies.set(LOCALE_KEY, this.locale);
    this.saveQueue.add(this.save.bind(this, true));
  }

  @autobind
  onProductAmountSubmit(e, el) {
    stopEvent(e);

    // const drop = el.relatedTarget;
    const radioInput = el.querySelector('input:checked');
    const row = radioInput.closest('.plan-item-amount-row');
    const weight = parseFloat(el.querySelector('.plan-item-amount-value').value);
    const id = parseInt(el.elements.weight_type.value, 10) || 0;
    const units = id ? (parseFloat(row.querySelector('.plan-item-amount-value').value) || 0) : 0;
    const target = this.amountEntity.target; // drop.target;
    const product = target.closest('.plan-item');

    product.dataset.totalWeight = weight;
    product.dataset.weightId = id;
    product.dataset.weightUnits = units;

    $.if(id, () => {
      const label = row.querySelector('label');
      target.textContent = `${units} x ${label.textContent}`;
    }).else(() => {
      target.textContent = `${weight}g`;
    });

    //drop.close();
    this.amountEntity.close();
    delegate.fire(product, 'product:calc');

    this.saveQueue.add(this.save);
  }

  @autobind
  onProductAmountCancel(e, el) {
    stopEvent(e);

    if (this.amountEntity) {
      this.amountEntity.close();
    }
  }

  @autobind
  onProductAmountSwitch(e, el) {
    const row = el.closest('.plan-item-amount-row');
    const total = row.querySelector('.plan-item-amount-total');
    const input = row.querySelector('.plan-item-amount-value');

    if (total) {
      total.classList.add('is-visible');
    }

    if (input) {
      delegate.fire(input, 'keyup');
    }

    row.parentNode
      .querySelectorAll('.plan-item-amount-total')
      .forEach((node, index) => {
        if (node !== total) {
          node.classList.remove('is-visible');
        }
      });
  }

  @autobind
  onProductCalculate(e, el) {
    const target = el.querySelector('.js-plans-product-kcal');

    if (target) {
      target.textContent = Math.round((el.dataset.kcal / 100) * el.dataset.totalWeight) || 0;
    }

    if (this.isMeal) {
      delegate.fire(el.closest('.plans-meal-plan'), 'meal:calc');
    } else if (this.isRecipe) {
      delegate.fire(el.closest('.plans-box-item-list'), 'meal:calc');
    }
  }

  @autobind
  onProductAmountUpdate(e, el) {
    const form = el.closest('form');
    // const product = form.relatedTarget.target.closest('.plan-item');
    const product = this.amountEntity.target.closest('.plan-item');
    const weightInput = form.querySelector('.plan-item-amount-value');
    const isWeightInput = el === weightInput;
    const totalWeight = isWeightInput ?
      parseFloat(el.value) :
      (parseFloat(el.value) * parseFloat(el.dataset.weight));

    const totalEnergy = (parseFloat(product.dataset.kcal) / 100) * totalWeight;

    if (!isWeightInput) {
      weightInput.value = totalWeight || 0;
    }

    const totalNode = el.parentNode.querySelector('var');

    if (totalNode) {
      totalNode.textContent = Math.round(totalEnergy);
    }

    product.dataset.totalKcal = totalEnergy;
  }

  @autobind
  onMealCalculate(e, el) {
    const items = el.querySelectorAll('.plan-item');
    const totalsNodeList = el.querySelectorAll('.plans-meal-totals var');
    const totals = {
      carbohydrate: 0,
      protein: 0,
      fat: 0,
      weight: 0,
      kcal: 0,
    };

    items.forEach((item) => {
      const weight = parseFloat(item.dataset.totalWeight) || 0;
      const kcal = parseFloat(item.dataset.kcal) || 0;
      const energy = Math.round((kcal / 100) * weight) || 0;

      totals.carbohydrate += (weight / 100) * parseFloat(item.dataset.carbohydrates);
      totals.protein += (weight / 100) * parseFloat(item.dataset.protein);
      totals.fat += (weight / 100) * parseFloat(item.dataset.fat);
      totals.weight += weight;
      totals.kcal += energy;
    });

    Object.keys(totals).forEach((key, index) => {
      const target = totalsNodeList[index];

      if (target) {
        target.textContent = Math.round(totals[key]);
      }

      el.dataset[key] = totals[key];
    });

    const box = el.closest('.plans-box');

    if (box) {
      delegate.fire(box, 'plan:calc');
    }
  }

  @autobind
  onPlanWorkoutCalculate(e, el) {
    const collapsedDescription = el.querySelector('.plans-box-collapsed-description');
    const exercises =
      el.querySelectorAll('.plans-box-plan > .plans-box-item-list > .plan-item').length +
      el.querySelectorAll('.plan-superset > .plan-item').length;
    const supersets = el.querySelectorAll('.plan-superset').length;

    if (collapsedDescription) {
      collapsedDescription.innerHTML = html`
        <span>${exercises} Single Exercises</span>
        <span>${supersets} Super Sets</span>
      `;
    }
  }

  @autobind
  onPlanCalculate(e, el) {
    let totals = {
      kcal: 0,
      protein: 0,
      carbohydrate: 0,
      fat: 0,
    };

    let keys = Object.keys(totals);

    const updateHeaderTotals = () => {
      const containsAlternatives = !!parseInt(this.plan.contains_alternatives, 10) || 0;
      const max = totals.protein * 4 + totals.carbohydrate * 4 + totals.fat * 9;

      if (this.isRecipe || !containsAlternatives) {
        const totalKcalNode = this.board.querySelector('.js-header-total-kcal');

        if (totalKcalNode) {
          totalKcalNode.textContent = totals.kcal;
        }
      } else {
        const avgKcalNode = this.board.querySelector('.js-header-avg-kcal');

        if (avgKcalNode) {
          avgKcalNode.textContent = totals.kcal;
        }
      }

      let macros = {
        'carbohydrate': 4,
        'protein': 4,
        'fat': 9
      }

      for (var macro in macros) {
        const node = this.board.querySelector(`.js-header-${macro}`);

        if (node) {
          node.textContent = `${Math.round(totals[macro])}g (${percent(totals[macro] * macros[macro] , max)}%)`;
        }
      }
    };

    const updatePlanDiff = (planNode, diff) => {
      if(+this.plan.desired_kcals > 0 && +this.plan.contains_alternatives === 1) {
        const diffNode = planNode.querySelector('.diff-indicator');
        diffNode.textContent = diff > 30 ? `${diff} kcals off` : '';
      }
    };

    if (this.isMeal) {
      const containsAlternatives = !!parseInt(this.plan.contains_alternatives, 10) || 0;

      document
        .querySelectorAll('.plans-box')
        .forEach((plan) => {
          let mealTotals = {
            kcal: 0,
            carbohydrate: 0,
            protein: 0,
            fat: 0,
          }
          plan
            .querySelectorAll('.plans-meal-plan')
            .forEach((meal) => {
              keys.forEach((key) => {
                mealTotals[key] += parseFloat(meal.dataset[key]);
              });
              const idealKCals = meal.dataset.idealKcals;
              const totalKCals = meal.dataset.kcal;
              const diff = Math.abs(idealKCals - totalKCals);
              updatePlanDiff(meal, diff);
            });

            let alternatives = plan.querySelectorAll('.plans-meal-plan').length;
            keys.forEach((key) => {
              totals[key] = containsAlternatives ?
                Math.round(totals[key] + (mealTotals[key] / alternatives)) :
                Math.round(totals[key] + mealTotals[key]);
            });
        });

      updateHeaderTotals();
    } else if (this.isRecipe) {
      let itemList = el.querySelector('.plans-box-item-list');
      let recipeTotalsNodeList = el.querySelectorAll('.plans-meal-totals var');

      totals.weight = 0;

      if (itemList) {
        [...keys, 'weight'].forEach((key) => {
          totals[key] += parseFloat(itemList.dataset[key]);
        });
      }

      updateHeaderTotals();

      [
        'carbohydrate',
        'protein',
        'fat',
        'weight',
        'kcal',
      ].forEach((key, index) => {
        const target = recipeTotalsNodeList[index];

        if (target) {
          target.textContent = Math.round(totals[key]);
        }
      });
    }

    // let totalsNodeList = el.querySelectorAll('.plans-box-side .plans-box-totals-list var');
    //
    // keys.forEach((key, index) => {
    //   const target = totalsNodeList[index];
    //   if (target) {
    //     if(el.dataset.containsAlternatives === 'true') {
    //       target.textContent = Math.round(totals[key] / el.dataset.meals);
    //     } else {
    //       target.textContent = Math.round(totals[key]);
    //     }
    //   }
    // });

    // const chart = charts.get(el.querySelector('.plans-box-side-chart'));
    //
    // if (chart) {
    //   chart.series[0].setData([
    //     ['Protein', totals.protein * 4],
    //     ['Carbohydrates', totals.carbohydrate * 4],
    //     ['Fat', totals.fat * 9]
    //   ]);
    // }
  }

  @autobind
  onCopySets(e) {
    const target = e.target;
    const inputs = target
      .closest('.plan-superset')
      .querySelectorAll('.plan-item-input-sets');

    inputs
      .toArray()
      .slice(0, inputs.length - 1)
      .forEach(node => node.value = target.value);

    this.saveQueue.add(this.save);
  }

  @autobind
  onAddEditPlan(e) {
    stopEvent(e);

    const $form = $(e.target);
    const $submit = $form
      .find('[type=submit]')
      .button('loading');

    const promise = $.post($form.attr('action'), $form.serialize(), 'json');
    const notification = {
      title: '',
      description: '',
    };

    promise
      .done((plan) => {
        let target = plan.parent_id ?
          this.boardList.querySelector(`.plans-meal-plan[data-id="${plan.id}"] .plans-box-header-main > h5`) :
          this.boardList.querySelector(`.plans-box[data-id="${plan.id}"] .plans-box-header > h5`);

        if (this.isWorkout) {
          notification.title = 'Workout Plan updated';
        } else {
          notification.title = plan.parent_id ? 'Meal Plan updated.' : 'Meal updated.';
        }

        if (target) {
          target.querySelector('span').textContent = plan.name;
          target.querySelector('a').dataset.title = plan.name;

          if (this.isWorkout) {
            notification.description = 'Workout plan day successfully renamed.';
          } else {
            notification.description = plan.parent_id ? 'Meal successfully renamed.' : 'Meal plan meal successfully renamed.';
          }
        } else {
          if (plan.parent_id) {
            target = document.querySelector(`.plans-box[data-id="${plan.parent_id}"] .plans-box-item-list`);

            if (target) {
              this.addPlanMeal(target, plan);

              notification.description = 'Meal successfully created.';
            }
          } else {
            const planData = {};

            if (this.isWorkout) {
              planData.workouts = [];
            }

            this.boardList
              .querySelectorAll('.plans-box')
              .forEach(el => el.classList.add('is-collapsed'));

            this.addPlan({...planData, ...plan});

            if (this.isWorkout) {
              notification.description = 'Workout day successfully created.';
            } else {
              notification.description = 'Meal plan successfully created.';
            }
          }
        }

        $form.closest('.modal').modal('hide');

        toastr.options.preventDuplicates = true;
        toastr.success(notification.description, notification.title);

      })
      .fail((...args) => {
        console.log('#addPlanModal::fail', args);
      })
      .always(() => {
        $submit.button('reset');
      });
  }

  @autobind
  onAddEditPlanInfo(e) {
    stopEvent(e);

    const $form = $(e.target);
    const $submit = $form
      .find('[type=submit]')
      .button('loading');
    mealPlanTitle = $('#mealPlanTitle').val();
    mealPlanComment = $('#mealPlanComment').val();

    const promise = $.post($form.attr('action'), $form.serialize(), 'json');
    const notification = {
      title: '',
      description: '',
    };

    promise
      .done((plan) => {
        if (this.isWorkout) {
          notification.title = 'Workout Plan updated';
          notification.description = 'Workout plan successfully renamed.';
        } else {
          notification.title = 'Meal Plan updated.';
          notification.description = 'Meal plan successfully renamed.';
        }

        let target = this.board.querySelector('.plans-board-title');
        let handler = this.board.querySelector('.js-template-title');

        if (target) {
          target.querySelector('span').textContent = plan.name;
        }

        if (handler) {
          handler.dataset.name = plan.name;
          handler.dataset.comment = plan.comment;
        }

        $form.closest('.modal').modal('hide');

        toastr.options.preventDuplicates = true;
        toastr.success(notification.description, notification.title);
      })
      .fail((...args) => {
        console.log('#addPlanModal::fail', args);
      })
      .always(() => {
        $submit.button('reset');
      });
  }

  async fetchPlans() {
    if (this.isWorkout) {
      return api.getWorkout(this.plan.id);
    } else if (this.isRecipe) {
      return api.getRecipe(this.plan.id);
    }
    return api.getMeals(this.plan.id, this.locale, this.plan.meal);
  }

  async fetchFilters() {
    if (!this.isWorkout) return;

    try {
      const [equipments, muscles] = await Promise.all([
        api.getEquipments(),
        api.getMuscles(),
      ]);

      this.data.equipments = equipments.data;
      this.data.muscles = muscles.data;
    } catch (e) {
    }
  }

  tempElements = [];

  @autobind
  sortableDragImage(dataTransfer, dragEl) {
    const crt = dragEl.cloneNode(true);

    crt.classList.add('is-collapsed');
    this.tempElements.push(crt);

    document.querySelector('.plans-temp').appendChild(crt);
    dataTransfer.setDragImage(crt, 0, 0);
  }

  clearTempElements() {
    this.tempElements.forEach(node => node.remove());
    this.tempElements.length = 0;
  }

  refreshExercises(item) {
    const isChildren = Boolean(item.closest('[data-children]'));
    const superSetLink = item.querySelector('.js-plan-item-set-superset');
    let refreshSuperSet = null;

    if (superSetLink) {
      superSetLink.classList.toggle('is-hidden', isChildren);
    }

    if (isChildren) {
      let lastRest;
      let lastSets;

      if (item.previousElementSibling && !item.nextElementSibling) {
        lastRest = lastSets = item.previousElementSibling;
      } else if (item.nextElementSibling) {
        lastSets = item.nextElementSibling;
      } else {
        lastRest = lastSets = item.closest('.plan-superset').querySelector('.plan-item');
      }

      try {
        if (lastRest) {
          lastRest = lastRest.querySelector('.plan-item-input-rest').value;
        }

        if (lastSets) {
          lastSets = lastSets.querySelector('.plan-item-input-sets').value;
        }

        refreshSuperSet = [
          item.closest('.plan-superset'),
          lastRest,
          lastSets
        ];
      } catch (e) {
      }

      if (refreshSuperSet) {
        this.enableSuperSet(...refreshSuperSet);
      }
    } else {
      item.querySelectorAll('input[type="text"]').forEach((el) => {
        el.disabled = false;
        el.removeEventListener('change', this.onCopySets);
      });
    }
  }

  get locale() {
    return this.options.locale
  };

  set locale(value) {
    this.options.locale = value;
  }

  get plan() {
    return this.options.plan;
  }

  get client() {
    return this.options.client;
  }

  get template() {
    return this.options.template;
  }

  get tour() {
    return this.options.tour;
  }

  get isWorkout() {
    return this.type === TYPE_WORKOUT;
  }

  get isMeal() {
    return this.type === TYPE_MEAL;
  }

  get isRecipe() {
    return this.type === TYPE_RECIPE;
  }

  get isTemplate() {
    if (this.plan.hasOwnProperty('template')) {
      return !!this.plan.template;
    }

    return this.template !== null;
  }

  get sortablePlansOptions() {
    const options = {
      sort: true,
      scroll: this.board,
      scrollSensitivity: SORTABLE_SCROLL_SENSITIVITY,
      group: {
        name: 'plans',
        pull: false,
        put: false
      },
      animation: 150,
      onStart: (e) => {
        [e.item, e.clone].forEach(node => node && node.classList.add('is-collapsed'));

        e.from.children.forEach((plan) => {
          plan.dataset.wasCollapsed = String(plan.classList.contains('is-collapsed'));
          this.togglePlan(plan, true);
        });

        if (this.isMeal) {
          closeAllDrops();
        }
      },
      onEnd: (e) => {
        [e.item, e.clone].forEach(node => node && node.classList.remove('is-collapsed'));

        e.from.children.forEach((plan) => {
          this.togglePlan(plan, plan.dataset.wasCollapsed === 'true');
          plan.dataset.wasCollapsed = undefined;
        });

        this.clearTempElements();
      },
      onUpdate: (e) => {
        this.saveQueue.add(this.save);
      },
    };

    if (!IS_FIREFOX) {
      options.setData = this.sortableDragImage;
    }

    // if (IS_TOUCH) {
    options.handle = '.plans-box-handle';
    // }

    return options;
  }

  get sortableMealsOptions() {
    let collapsedMeals = [];

    const options = {
      sort: true,
      dataIdAttr: 'data-id',
      scroll: this.board,
      scrollSensitivity: SORTABLE_SCROLL_SENSITIVITY,
      // group: {
      //   name: 'meals',
      //   pull: true,
      //   put: true
      // },
      group: 'meals',
      animation: 150,
      onStart: (e) => {
        [e.item, e.clone].forEach(node => node && node.classList.add('is-collapsed'));

        closeAllDrops();

        collapsedMeals = this.board.querySelectorAll('.plans-meal-plan')
          .toArray()
          .filter(el => el !== e.item);

        collapsedMeals
          .forEach(el => el.classList.add('plans-meal-plan--collapsed'));
      },
      onEnd: (e) => {
        [e.item, e.clone].forEach(node => node && node.classList.remove('is-collapsed'));

        collapsedMeals
          .forEach(el => el.classList.remove('plans-meal-plan--collapsed'));

        collapsedMeals.length = 0;

        this.clearTempElements();
      },
      onUpdate: (e) => {
        this.saveQueue.add(this.save);
      },
      onAdd: (e) => {
        this.saveQueue.add(this.save);

        const fromPlanBox = e.from.closest('.plans-box');
        const toPlanBox = e.to.closest('.plans-box');

        delegate.fire(toPlanBox, 'plan:calc');

        if (fromPlanBox !== toPlanBox) {
          delegate.fire(fromPlanBox, 'plan:calc');
        }
      }
    };

    if (!IS_FIREFOX) {
      options.setData = this.sortableDragImage;
    }

    // if (IS_TOUCH) {
    options.handle = '.plans-handle';
    // }

    return options;
  }

  get sortableItemsOptions() {
    const options = {
      sort: true,
      dataIdAttr: 'data-id',
      scroll: this.board,
      scrollSensitivity: SORTABLE_SCROLL_SENSITIVITY,
      // group: {
      //   name: 'advanced',
      //   pull: true,
      //   put: true
      // },
      group: 'items',
      animation: 150,
      onStart: () => {
        this.container.classList.add('plans-dragging');
      },
      onEnd: () => {
        this.container.classList.remove('plans-dragging');
      },
      onUpdate: (e) => {
        if (e.from.classList.contains('plans-box-item-list') && this.isWorkout) {
          this.refreshExercises(e.item);
        }

        this.saveQueue.add(this.save);
      },
      onRemove: (e) => {
        //e.target.dataset.count = e.target.children.length;

        if (this.isWorkout) {
          delegate.fire(e.target.closest('.plans-box'), 'plan:calc');
        }

        if (this.isMeal || this.isRecipe) {
          closeAllDrops();
        }
      },
      onAdd: (e) => {
        if (this.isMeal || this.isRecipe) {
          closeAllDrops();
        }

        if (e.from.classList.contains('plans-search-result')) {
          const data = JSON.parse(decodeURI(e.item.dataset.item));

          if (this.isWorkout) {
            data.sets = 3;
            data[data.type.name.toLowerCase()] = 12;
            data.rest = 60;

            let obj = [
              {id: e.item.dataset.id}
            ];

            this.addPlanExercise(e.item, data, true);
          } else if (this.isMeal || this.isRecipe) {
            if (e.target.querySelector(`[data-product-id="${data.product.id}"]`)) {
              bootbox.alert(`You can't have the same food item twice in the same meal!`);

              if (e.item.parentNode) {
                e.item.remove();
              }
              return;
            }

            this.addPlanProduct(e.item, data, true, true);
          }

          if (e.item.parentNode) {
            e.item.remove();
          }
        } else if (e.from.classList.contains('plans-box-item-list')) {
          if (this.isWorkout) {
            this.refreshExercises(e.item);
          }

          if (this.isMeal) {
            delegate.fire(e.from.closest('.plans-meal-plan'), 'meal:calc');
            delegate.fire(e.to.closest('.plans-meal-plan'), 'meal:calc');
          }

          delegate.fire(e.from.closest('.plans-box'), 'plan:calc');
          e.from.dataset.count = e.from.children.length;
        }

        delegate.fire(e.target.closest('.plans-box'), 'plan:calc');
        e.target.dataset.count = e.target.children.length;

        this.saveQueue.add(this.save);
      }
    };

    // if (IS_TOUCH) {
    options.handle = '.plans-handle';
    // }

    return options;
  }

  get sortableSearchOptions() {
    let dropZones = [];

    const options = {
      sort: false,
      scroll: this.board,
      scrollSensitivity: SORTABLE_SCROLL_SENSITIVITY,
      filter: '.no-drag',
      group: {
        // name: 'advanced',
        name: 'items',
        pull: 'clone',
        put: false,
      },
      animation: 150,
      onStart: (e) => {
        this.container.classList.add('plans-dragging');

        (dropZones = document.querySelectorAll('.plan-box-drop-zone').toArray())
          .forEach(el => el.classList.add('is-highlighted'));

        if (IS_TOUCH) {
          const target = e.item.closest('.plans-search-result');

          if (target) {
            target.classList.remove('has-touch-scrolling');
          }
        }
      },
      onEnd: (e) => {
        this.container.classList.remove('plans-dragging');

        dropZones.forEach(el => el.classList.remove('is-highlighted'));
        dropZones.length = 0;

        if (IS_TOUCH) {
          const target = e.item.closest('.plans-search-result');

          if (target) {
            target.classList.add('has-touch-scrolling');
          }
        }
      }
    };

    if (IS_TOUCH) {
      options.handle = '.plans-handle';
    }

    return options;
  }

  get workoutFormData() {
    const results = [];

    this.boardList.querySelectorAll('.plans-box').forEach(function (planBox, planIndex) {
      const plan = {
        day_id: planBox.dataset.id,
        workoutDayComment: planBox.querySelector('.plans-box-comment textarea').value,
        order: planIndex + 1,
        exercises: []
      };

      const entityList = planBox.querySelector('.plans-box-item-list').children;

      entityList.forEach((node, index) => {
        const isSuperSet = node.classList.contains('plan-superset');
        const item = isSuperSet ?
          node.querySelector('.plan-item') :
          node;

        const exercise = serializeExerciseItem(item, index + 1);

        if (isSuperSet) {
          item.parentNode
            .querySelector('.plans-box-item-list')
            .children
            .forEach((childrenNode, childrenIndex) => {
              exercise.superset.push(serializeExerciseItem(childrenNode, childrenIndex + 1))
            });
        }

        plan.exercises.push(exercise);
      });

      results.push(plan);
    });

    return {results};
  }

  get mealFormData() {
    const results = [];

    this.boardList.querySelectorAll('.plans-box').forEach(function (planBox, planIndex) {
      const plan = {
        id: planBox.dataset.id,
        meals: [],
        order: planIndex + 1,
        comment: planBox.querySelector('.plans-box-plan > .plans-box-comment textarea').value,
      };

      planBox
        .querySelectorAll('.plans-meal-plan')
        .forEach((mealNode, mealIndex) => {
          const meal = {
            id: mealNode.dataset.id,
            parent: plan.id,
            order: mealIndex + 1,
            comment: mealNode.querySelector('.plans-box-comment textarea').value,
            products: []
          };

          mealNode
            .querySelectorAll('.plan-item')
            .forEach((node, productIndex) => meal.products.push(serializeProductItem(node, productIndex + 1)));

          plan.meals.push(meal);
        });

      results.push(plan);
    });

    return {results, locale: this.locale};
  }

  get recipeFormData() {
    const planBox = this.boardList.querySelector('.plans-box');
    const recipe = {
      id: planBox.dataset.id,
      comment: planBox.querySelector('.plans-box-plan > .plans-box-comment textarea').value,
      products: [],
      order: 1,
    };

    planBox
      .querySelectorAll('.plan-item')
      .forEach((node, productIndex) => recipe.products.push(serializeProductItem(node, productIndex + 1)));

    return recipe;
  }
}

global.Plans = Plans;

// Global jQuery Listeners
const $body = $('body');

$body.on('hidden.bs.modal', '#saveAsPdf', function () {
  $(this)
    .find('.modal-success, a[role=button]')
    .remove()
    .end()
    .find('.modal-title, .modal-body-main, button[type=submit], .description, .sub-description')
    .show();
});

$body.on('click', '.js-save-pdf', function () {
  $('#saveAsPdf').modal('show');
});

$body.on('submit', '#saveAsPdf form', function (event) {
  event.preventDefault();
  const $form = $(this);
  const $modal = $('#saveAsPdf');
  const $submit = $form
    .find('button[type=submit]')
    .button('loading');

  const xhr = $.ajax({
    type: 'POST',
    url: $form.attr('action'),
    data: $form.serialize(),
    dataType: 'json'
  });

  xhr
    .done((response) => {
      const documentsUrl = $modal.data('documentsUrl');

      $modal
        .find('.modal-title, .modal-body-main')
        .hide();

      $modal
        .find('.modal-body')
        .append(
          Templates.ModalSuccessMessage(
            `Your PDF is being generated`,
            `Once finished, it will be sent to your email`
          )
        );

      $submit
        .button('reset')
        .hide()
        .after(`<a data-dismiss="modal" class="btn btn-block btn-default" role="button">OK, I got it!</a>`);

      if(response.url) {
        setTimeout(() => {
          window.location = response.url;
        }, 100);
      }
    })
    .fail((err) => {
      $submit.button('reset');

      var json = JSON.parse(err.responseText);

      if (json.subscription) {
        toastr.options.preventDuplicates = true;
        toastr.options.progressBar = true;
        toastr.info('Please <a href="/dashboard/subscription">upgrade your plan</a> in order to save plans as PDF.', {timeOut: 5000})
        return;
      }
    });
});

$body.on('click', '.js-save-template', function () {
  $('#saveAsTemplate').modal('show');
});

$body.on('click', '.js-use-template', function (event) {
  event.preventDefault();
  let $modal = $('#addMealTemplate');

  if (!$modal.length) {
    $modal = $('#addWorkoutTemplate');
  }

  $modal.modal('show');
});

$body.on('click', '.js-add-plan', function (event) {
  stopEvent(event);

  const title = this.dataset.title;
  const id = this.dataset.id;
  const parentId = this.dataset.parent;
  const isMeal = this.dataset.type === TYPE_MEAL;
  const isRecipe = this.dataset.type === TYPE_RECIPE;
  const $modal = $(isRecipe ? '#recipeModal' : '#addPlanModal');
  const $submit = $modal.find('button[type=submit]');
  let type = 'workout day';

  if (isMeal) {
    type = 'meal plan';
  } else if (isRecipe) {
    type = 'recipe';
  }

  let placeholder = 'Title of workout day';

  if (isMeal) {
    placeholder = parentId ? 'Title of meal' : 'Title of meal plan';
    $modal.find('label[for=mealTitle]').text(parentId ? 'Title of meal' : 'Title of meal plan');
  } else if (isRecipe) {
    if (id) {
      let types = JSON.parse(this.dataset.recipeType).map(t => t.type);
      let recipeMeta = JSON.parse(this.dataset.recipeMeta);

      $modal
        .find('select[name=locale]')
        .val(this.dataset.locale)
        .end()
        .find('select[name=macro_split]')
        .val(this.dataset.macroSplit)
        .end()
        .find('select[name=cooking_time]')
        .val(this.dataset.cookingTime)
        .end()
        .find('[name=type]')
        .selectpicker("val", types);


      //set recipe meta
      Object.keys(recipeMeta).forEach(ingredient => {
        // if ingredient is chosen
        if(recipeMeta[ingredient]) {
          let el;
          if(ingredient.substring(0,2) === 'is') {
            el = 'input#is_' + ingredient.substring(2).toLowerCase();
          } else {
            el = 'input#avoid_' + ingredient;
          }

          setTimeout(function() {
            $(el).parent('label').addClass('current');
          },500)

        }
      });

    }
    placeholder = 'Name of recipe';
  }

  $modal.modal('show');
  $modal.find('input[name=id]').val(id || null);
  $modal.find('input[name=parent_id]').val(parentId || null);
  $modal.find('input[name=name]')
    .val(title || '')
    .attr('placeholder', placeholder);

  let submitTitle;

  if (id) {
    if (isMeal) {
      submitTitle = parentId ? 'Save Meal' : 'Save Meal Plan';
    } else if (isRecipe) {
      submitTitle = 'Save Recipe'
    } else {
      submitTitle = 'Save Workout Day'
    }
  } else {
    if (isMeal) {
      submitTitle = parentId ? 'Add new Meal' : 'Add new Meal Plan';
    } else if (isRecipe) {
      submitTitle = 'Add new Recipe'
    } else {
      submitTitle = 'Add new Workout Day'
    }
  }

  $submit.text(submitTitle);

  if (isMeal) {
    $modal.find('input[name=parent_id]').val(parentId || null);
  }

  let addTitle = '';

  if (isMeal) {
    addTitle = parentId ? 'Create new meal' : 'Create new meal plan';
  } else if (isRecipe) {
    addTitle = 'Create new recipe';
  } else {
    addTitle = 'Create new workout day';
  }

  $modal
    .find('.modal-title')
    .text(id ? 'Edit ' + type : addTitle);
});

$body.on('click', '.js-plan-clone', function (event) {
  stopEvent(event);

  const $modal = $('#clonePlanModal');
  const data = $(this).data();
  const plans = this.closest('.plans');

  let title;
  let submit;

  if (plans && plans.dataset.type === TYPE_WORKOUT) {
    title = 'Copy this workout plan';
    submit = 'Copy workout plan';
  } else {
    const isMeal = Boolean(this.closest('.plans-meal-plan'));

    title = isMeal ? 'Copy this meal' : 'Copy this meal plan';
    submit = isMeal ? 'Copy meal' : 'Copy meal plan';
  }


  $modal.modal('show');
  $modal.find('input[name=name]').val('Copy of ' + data.title);
  $modal.find('input[name=id]').val(data.id);
  $modal.find('.modal-title').text(title);
  $modal.find('button[type="submit"]').text(submit);

  $modal.on('click', '.btn-success', function (e) {
    stopEvent(e);


    let $form = $modal.find('form');
    const $submit = $form.find('[type=submit]').button('loading');

    let data = {
      'id': $form.find('input[name=id]').val(),
      'name': $form.find('input[name=name]').val()
    };


    $.post($form.attr('action'), data, function (response) {
      toastr.options.preventDuplicates = true;
      toastr.success('Successfully', 'Cloning');

      window.location.reload();
    }).fail(function (error) {
      toastr.options.preventDuplicates = true;
      toastr.error('Cloning failed');
    }).always(function () {
      $submit.button('reset');
      $modal.modal('hide');
    });

  });
});

$body.on('click', '.js-plan-settings', function (event) {
  stopEvent(event);
  $('#planSettingsModal').modal('show');
});

$body.on('click', '.js-assign-template', function (event) {
  stopEvent(event);
  $('#assignTemplate').modal('show');
});

$body.on('click', '.js-template-title', function (event) {
  stopEvent(event);
  let data = $(this).data();

  if (!mealPlanTitle) {
    mealPlanTitle = data.name;
  }

  if (!mealPlanComment) {
    mealPlanComment = data.comment || data.explaination;
  }

  if (!workoutsPerWeek) {
    workoutsPerWeek = data.workoutsperweek;
  }

  if (!location) {
    location = data.location;
  }

  if (!gender) {
    gender = data.gender;
  }

  if (!level) {
    level = data.level;
  }

  if (!duration) {
    duration = data.duration;
  }

  //meal stuff
  $('#mealPlanTitle').val(mealPlanTitle);
  $('#mealPlanComment').val(mealPlanComment);

  $('#editTemplateText').modal('show');
  //workout stuff
  setTimeout(() => {
    $('#editTemplateText').find('[name="workoutsPerWeek"]').val(workoutsPerWeek);
    $('#editTemplateText').find('[name="duration"]').val(duration);
    $('#editTemplateText').find('[name="level"]').val(level);
    $('#editTemplateText').find('[name="gender"]').val(gender);
    $('#editTemplateText').find('[name="location"]').val(location);
  }, 100);
});

$body.on('click', '#planSettingsModal input[type=checkbox]', function () {
  const $target = $(this);
  const name = $target.attr('name');

  if (name === 'reps' || name === 'time') {
    const mirror = {
      reps: 'time',
      time: 'reps',
    };

    $target
      .closest('form')
      .find(`input[name="${mirror[name]}"]`)
      .prop('checked', $target.prop('checked'));
  }
});

$body.on('click', '.js-exercise-preview', function (event) {
  stopEvent(event);

  $('#exerciseModal')
    .modal({remote: this.getAttribute('href')})
    .modal('show', this);
});

$body.on('change', '.js-plan-macro-split-select', function () {
  let value = this.value;

  toastr.options.preventDuplicates = true;

  $.post(`/meal/recipes/${this.dataset.recipeId}/update`, {
    'macro_split': value,
  }).done(() => {
    $('[data-macro-split]').attr('data-macro-split', value);
    toastr.success('Macro split successfully updated', 'Recipe Update');
  }).fail(() => {
    toastr.error('Macro split has not been updated',  'Recipe Update');
  });
});

$body.on('click', '.js-recipe-change-image', function (event) {
  event.preventDefault();
  $('#recipeModalImage').modal('show', this);
});

$body.on('click', '.js-apply-recipe', function (event) {
  event.preventDefault();
  $('#applyMealRecipe').modal('show', this);
});
