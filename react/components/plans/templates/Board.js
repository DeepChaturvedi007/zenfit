import isNumber from 'lodash/isNumber';
import {html} from 'common-tags';
import {
  TYPE_MEAL,
  TYPE_WORKOUT,
  TYPE_RECIPE
} from '../constants';
import { percent } from '../helpers';

/**
 * @param {boolean} isTemplate
 * @param {Object} client
 * @param {Object} plan
 * @param {Array<Object>} data Meal List
 * @param {Object} options
 *
 * @returns {string}
 */
export function BoardHeaderMealActions(isTemplate, client, plan, data, options) {
  const applyRecipeProps = {
    'plan': plan.id,
    'locale': plan.locale || options.locale,
  };

  const meal = data[0];

  if (meal) {
    if (isNumber(meal.macroSplit)) {
      applyRecipeProps.macroSplit = meal.macroSplit;
    }

    if (isNumber(meal.type)) {
      applyRecipeProps.type = meal.type;
    }
  }

  return html`
    <div class="board-meal-actions">
      <div class="dropdown">
          <button class="btn btn-default btn-sm text-uppercase"
                  data-toggle="dropdown"
                  aria-expanded="false"
                  type="button">
              More
              <span class="button-caret"><strong class="caret"></strong></span>
          </button>
          <ul class="dropdown-menu">
              ${isTemplate ? '' : '<li><a href="#" class="js-save-template">Save as Template</a></li>'}
              <li><a href="#" class="js-use-template">Apply template</a></li>
              <li><a href="#" class="js-save-pdf">Save as PDF</a>
              <li><a href="#" class="js-apply-recipe" data-params="${window.btoa(JSON.stringify(applyRecipeProps))}">Apply recipe</a>
          </ul>
      </div>
    </div>
  `;
}

export function BoardHeaderMealInfo(plan, client, data = [], type = TYPE_MEAL) {
  const containsAlternatives = !!parseInt(plan.contains_alternatives, 10) || 0;

  let totals = data.reduce((totals, item) => {
    const idealTotals = containsAlternatives ? item.ideal_totals : item.totals;
    const avgTotals = containsAlternatives ? item.avg_totals : item.totals;

    totals.desiredKcals += idealTotals.kcal;
    totals.avgKcals += avgTotals.kcal;
    totals.protein += avgTotals.protein;
    totals.carbohydrate += avgTotals.carbohydrate;
    totals.fat += avgTotals.fat;

    return totals;
  }, {
    desiredKcals: 0,
    avgKcals: 0,
    protein: 0,
    carbohydrate: 0,
    fat: 0,
  });

  const progressMax = totals.protein * 4 + totals.carbohydrate * 4 + totals.fat * 9;

  return html`
    <div class="board-meal-totals">
        ${type === TYPE_RECIPE || !containsAlternatives ? `
          <h6>Total kcals: <var class="js-header-total-kcal">${Math.round(totals.desiredKcals)}</var> kcal</h6>
        ` : `
          <h6>Target for this plan: <var class="js-header-target-kcal">${Math.round(totals.desiredKcals)}</var> kcal</h6>
          <h6>Your average: <var class="js-header-avg-kcal">${Math.round(totals.avgKcals)}</var> kcal</h6>
        `}
        <div class="board-meal-meta">
            <span data-color="red">Carbs: <var class="js-header-carbohydrate">${Math.round(totals.carbohydrate)}g (${percent(totals.carbohydrate * 4, progressMax)}%)</var></span> •
            <span data-color="blue">Protein: <var class="js-header-protein">${Math.round(totals.protein)}g (${percent(totals.protein * 4, progressMax)}%)</var></span> •
            <span data-color="yellow">Fat: <var class="js-header-fat">${Math.round(totals.fat)}g (${percent(totals.fat * 9, progressMax)}%)</var></span>
        </div>
    </div>
  `;
}

export function BoardHeaderWorkoutActions(isTemplate, client) {
  return html`
        <button class="btn btn-success btn-sm js-add-plan text-uppercase m-r-xs" type="button" data-type="${TYPE_WORKOUT}">Add day</button>
        <div class="dropdown">
            <button class="btn btn-default btn-sm text-uppercase"
                    data-toggle="dropdown"
                    aria-expanded="false"
                    type="button">
                More
                <span class="button-caret"><strong class="caret"></strong></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                ${isTemplate ? '' : '<li><button class="btn btn-default js-save-template" type="button">Save as Template</button></li>'}
                <li><button class="btn btn-default js-use-template" type="button">Apply template</button></li>
                <li><button class="btn btn-default js-save-pdf" type="button">Save as PDF</button>
            </ul>
        </div>
    `;
}

export function BoardHeader({data, updatedAt, type, plan, client, options}, isTemplate = false) {
  const boardDescription = `Updated on ${updatedAt}`;
  let className = 'grey-color';
  let workoutsPerWeek = 0;
  let duration = 0;
  let level = 0;
  let location = 0;
  let gender = 0;

  if (plan.meta) {
    workoutsPerWeek = plan.meta.workoutsPerWeek;
    duration = plan.meta.duration;
    level = plan.meta.level;
    location = plan.meta.location;
    gender = plan.meta.gender;
  }

  let editAttributes = [
    `data-name="${plan.name}"`,
    `data-explaination="${plan.explaination}"`,
    `data-workoutsPerWeek="${workoutsPerWeek}"`,
    `data-duration="${duration}"`,
    `data-level="${level}"`,
    `data-location="${location}"`,
    `data-gender="${gender}"`,
    'role="button"',
  ];

  if (type === TYPE_MEAL) {
    editAttributes = [
      ...editAttributes,
      `data-title="${plan.name}"`,
      `data-comment="${plan.explaination}"`,
      `data-type="edit"`,
      'data-target="#mealPlanModal"',
      'data-toggle="modal"',
    ];
  } else {
    className += ' js-template-title';
  }

  const boardEditTitle = html`
    <a class="${className}" ${editAttributes.join(' ')}>
      <span class="material-icons">mode_edit</span>
    </a>
  `;

  let headerLeft = `
    <h2 class="plans-board-title">
      <span class="js-plan-title">${plan.name}</span>
      ${boardEditTitle}
    </h2>
    <!--<small class="js-plan-description">${boardDescription}</small>-->
  `;

  if (type === TYPE_RECIPE) {
    headerLeft = '';
  } else if (type === TYPE_MEAL) {
    headerLeft += BoardHeaderMealActions(isTemplate, client, plan, data, options);
  }

  return html`
    <header class="plans-board-header">
      <div class="plans-board-header-left">
        ${headerLeft}
      </div>
      <div class="plans-board-header-right">
        ${type === TYPE_RECIPE ? BoardHeaderMealInfo(plan, client, [data], type) : ''}
        ${type === TYPE_MEAL ? BoardHeaderMealInfo(plan, client, data, type) : ''}
        ${type === TYPE_WORKOUT ? BoardHeaderWorkoutActions(isTemplate, client, updatedAt) : ''}
      </div>
    </header>
  `;
}
