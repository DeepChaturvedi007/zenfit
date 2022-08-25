import {html, safeHtml, stripIndents} from 'common-tags';
import {pictureFilter} from '../helpers';
import {
  TYPE_WORKOUT,
  TYPE_MEAL,
  TYPE_RECIPE
} from '../constants'
import isNumber from "lodash/isNumber";

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

export function PlanDropZone(type) {
  const title = type === TYPE_WORKOUT ?
    'Drop Exercise Here' :
    'Drop Food Item Here';

  return html`
    <div class="plan-box-drop-zone" data-title="${title}"></div>
  `;
}

export function PlanExerciseInput({label, value, name}) {
  return html`
    <div class="form-group">
      <label>${capitalizeFirstLetter(label)}</label>
      <input class="form-control ${`plan-item-input-${name}`}" value="${value}" name="${name}" type="text" />
    </div>
  `;
}

export function PlanStateLink({className, isActive, on, off}) {
  const linkClass = `plan-item-state-link${className ? ` ${className}` : ''}${isActive ? ' is-active' : ''}`;

  return html`
     <a class="${linkClass}" tabIndex="0" role="button" data-on="${on}" data-off="${off}">
        ${isActive ? on : off }
     </a>
  `;
}

export function PlanItemComment({comment}) {
  const hasComment = Boolean(comment);

  return `
    <div class="${`plan-item-comment ${hasComment ? '' : 'is-hidden'}`}">
      <textarea class="form-control js-plan-item-comment" placeholder="Comment">${comment}</textarea>
    </div>
  `
}

export function PlanItemEditName(plan, type) {
  let attributes = [
    `data-title="${plan.name}"`,
    `data-id="${plan.id}"`,
    `data-type="${type}"`,
  ];

  if (type === TYPE_RECIPE) {
    attributes.push(`data-locale="${plan.locale}"`);
    attributes.push(`data-macro-split="${plan.macro_split}"`);
    attributes.push(`data-cooking-time="${plan.cooking_time}"`);
    attributes.push(`data-recipe-type='${JSON.stringify(plan.types)}'`);
    attributes.push(`data-recipe-meta='${JSON.stringify(plan.recipeMeta)}'`);
  }

  return html`
    <a class="grey-color js-add-plan" ${attributes.join(' ')}>
      <span class="material-icons">mode_edit</span>
    </a>
  `;
}

export function PlanTotalsList(totals) {
  return html`
    <ul class="plans-box-totals-list">
      <li>
        <span>Energy</span>
        <var>${Math.round(totals.kcal)}</var>kcal
      </li>
      <li class="is-blue">
        <span>Protein</span>
        <var>${Math.round(totals.protein)}</var>g
      </li>
      <li class="is-red">
        <span>Carbs</span>
        <var>${Math.round(totals.carbohydrate)}</var>g
      </li>
      <li class="is-orange">
        <span>Fat</span>
        <var>${Math.round(totals.fat)}</var>g
      </li>
    </ul>
  `;
}

export function PlanExercise(workout, options = {}) {
  const {settings} = options;
  const exercise = workout.exercise;
  const preview = `/api/exercises/exerciseInfo/${exercise.id}`;
  const thumbnail = pictureFilter(options.s3, exercise.picture, 'exercises', 50) || '/images/exercise_thumbnail.png';
  const type = workout.type.name.toLowerCase();
  const hasDropSet = false;
  const isSuperSet = Array.isArray(workout.supers) && Boolean(workout.supers.length);

  const actions = [
    {
      className: 'js-plan-item-set-comment',
      on: 'Remove Comment',
      off: 'Add Comment',
      isActive: Boolean(workout.comment)
    },
    {
      className: 'js-plan-item-set-dropset',
      on: 'Remove Drop Set',
      off: 'Drop Set',
      isActive: hasDropSet
    },
    {
      className: 'js-plan-item-set-superset',
      on: 'Cancel Super Set',
      off: 'Create Super Set',
      isActive: isSuperSet
    }
  ];

  const inputs = [
    {
      label: 'Sets',
      name: 'sets',
      value: workout.sets
    },
    {
      label: type,
      name: type,
      value: workout[type]
    },
    {
      label: 'RM',
      name: 'rm',
      value: workout.rm,
    },
    {
      label: 'Weight',
      name: 'weight',
      value: workout.startWeight,
    },
    {
      label: 'Rest in sec',
      name: 'rest',
      value: workout.rest
    },
    {
      label: 'Tempo',
      name: 'tempo',
      value: workout.tempo,
    },
  ].filter(input => settings && settings[input.name]);

  let comment = workout.comment == null ? '' : workout.comment;

  return `
    <div class="plan-item" data-id="${workout.id}" data-exercise-id="${exercise.id}">
      <div class="plans-handle"></div>
      <div class="plan-item-thumb">
        <a
            class="js-exercise-preview"
            href="${preview}"
            role="button"
            data-exercise-type="${workout.type.name}"
        >
          <img src="${thumbnail}" alt="">
          <span></span>
        </a>
      </div>
      <div class="plan-item-details">
        <a class="plan-item-title js-exercise-preview" href="${preview}" data-exercise-type="${workout.type.name}">${exercise.name}</a>
        <div class="plan-item-details">
          ${actions.map(action => PlanStateLink(action)).join("\n")}
        </div>
      </div>
      <div class="plan-item-inputs">
        ${inputs.map(input => PlanExerciseInput(input))}
        <button class="plan-item-delete js-plan-item-remove ${isSuperSet ? 'delete-superset' : ''}" type="button">
          <i class="material-icons">clear</i>
        </button>
      </div>
      ${PlanItemComment({comment})}
    </div>
  `;
}

export function PlanSuperSet({id, children}, type) {
  return html`
    <div class="plan-superset" data-id="${id}">
      <div class="plans-handle plan-superset-handle"></div>
      <div class="plan-superset-divider">Super Set</div>
      ${children}
      <div class="plans-box-item-list" data-children="0" data-count="0"></div>
      ${PlanDropZone(type)}
      <div class="plan-superset-divider">End of Super Set</div>
    </div>
  `;
}

export function PlanHeader(plan, type, canCollapse = true, canClone = true, isDraggable = true, canDelete = true) {
  let className = 'plans-box-header';
  let collapseAction = '';

  if (canCollapse) {
    className += ' js-plan-toggle';
    collapseAction = html`
      <button class="plans-box-header-action js-plan-toggle" type="button">
        <span class="material-icons">${plan.last ? ' expand_less' : ' expand_more'}</span>
      </button>
    `;
  }

  let saveRecipeAction = '';
  const canSaveRecipe = !!plan.recipe;
  if(canSaveRecipe) {
    saveRecipeAction = `
      <button
        class="plans-box-header-action js-recipe-save"
        type="button"
        data-source-meal="${plan.id}"
        data-id="${plan.recipe}"
        data-name="${plan.name}"
        data-image="${plan.image}"
        data-locale="${plan.locale}"
        data-macro-split="${plan.macro_split}"
        data-cooking-time="${plan.cooking_time}"
        data-recipe-meta='${JSON.stringify(plan.recipe_meta)}'
        data-types='${JSON.stringify(plan.recipe_types)}'
        data-title="Save Recipe"
        data-target="#recipeModal"
        data-toggle="modal"
        data-mode="save"
        role="button"
      >
        <span class="material-icons">save_alt</span>
      </button>
    `;
  }

  let cloneAction = '';

  if (canClone) {
    cloneAction = `<button class="plans-box-header-action js-plan-clone" data-id="${plan.id}" data-title="${plan.name}" type="button">
      <span class="material-icons">content_copy</span>
    </button>`;
  }

  let deleteAction = '';

  if (canDelete) {
    deleteAction = `
      <button class="plans-box-header-action js-plan-delete" type="button">
        <span class="material-icons">delete</span>
      </button>
    `;
  }

  let dragHandle = '';

  if (isDraggable) {
    dragHandle = '<div class="plans-handle plans-box-handle"></div>';
  }

  let diffText = '';

  if (type === TYPE_MEAL && plan.contains_alternatives && +plan.desired_kcals > 0) {
    let diff = Math.abs(plan.ideal_kcals - plan.totals.kcal);
    diffText = diff > 30 ? `${diff} kcals off` : '';
  }

  let headerBody = `
    <h5>
      <span>${plan.name}</span>
      ${PlanItemEditName(plan, type)}
      <span class="diff-indicator label label-danger">${diffText}</span>
    </h5>
    <div class="plans-box-header-actions">
      ${saveRecipeAction}
      ${cloneAction}
      ${deleteAction}
      ${collapseAction}
    </div>
  `;

  const hasImage = type === TYPE_RECIPE
    || (type === TYPE_MEAL && plan.hasOwnProperty('image') && plan.hasOwnProperty('products'));
  const canChangeImage = type === TYPE_MEAL || type === TYPE_RECIPE;

  const thumbRE = /thumbnail/i;
  const headerImage = hasImage && plan.image && !thumbRE.test(plan.image) ? `
    <div class="plans-box-header-image">
        <img src="${plan.image || '/bundles/app/images/recipe-placeholder.png'}" alt="${plan.name}"/>
    </div>` : '';

  if (hasImage) {
    const imageProps = {
      id: plan.id,
      name: plan.name,
      image: plan.image || '/bundles/app/images/recipe-placeholder.png',
      type,
    };

    headerBody = `
      ${headerImage}
      <div class="plans-box-header-wrapper">
        <div class="plans-box-header-main">
          ${headerBody}
        </div>
        ${canChangeImage ? (`<div class="plans-box-header-links">
          <a href="#" class="js-recipe-change-image" data-image="${btoa(JSON.stringify(imageProps))}">Change image</a>
        </div>`) : ''}
        ${PlanMealTotals(plan.totals)}
      </div>
    `;
  }

  return html`
    <header class="${className}">
      ${dragHandle}
      ${headerBody}
    </header>
  `;
}

export function PlanComment(plan, type, withHandler = true, placeholder = null, handler = null, props = {}) {
  if (!placeholder) {
    switch (type) {
      case TYPE_MEAL:
        placeholder = 'Comment to this meal plan';
        break;
      case TYPE_WORKOUT:
        placeholder = 'Comment to this day';
        break;
      case TYPE_RECIPE:
        placeholder = 'Write the recipe to client here';
        break;
    }
  }

  const containerClass = props.containerClass ? props.containerClass : 'plans-box-comment';
  const hasComment = Boolean(plan.comment);
  const commentValue = hasComment ? plan.comment : '';

  return `
    <div class="${containerClass}">
      ${withHandler ? `<a tabindex="0" role="button" class="${hasComment ? 'hidden' : ''}">${handler || 'Comment for Plan'}</a>` : ''}
      <textarea class="form-control ${(withHandler && !hasComment) ? 'hidden' : ''}" placeholder="${placeholder}">${commentValue}</textarea>
    </div>
  `;
}

export function PlanProduct(meal, locale, options = {}) {
  const product = meal.product;
  let name = product.name;

  if (product.brand) {
    name += `, ${product.brand}`;
  }

  if (product.recommended && options.admin) {
    name += ` <span class="glyphicon glyphicon-star text-success"></span>`;
  }

  let chooseAmountText = 'Choose amount';

  if (meal.weight) {
    chooseAmountText = `${meal.weightUnits} x ${meal.weight.name} (${Math.round(meal.weight.weight)}g)`;
  } else if (meal.totalWeight) {
    chooseAmountText = `${Math.round(meal.totalWeight)}g`;
  }

  const totalEnergy = Math.round((meal.product.kcal / 100) * meal.totalWeight);

  return html`
    <div
      class="plan-item"
      data-id="${meal.id}"
      data-product-id="${product.id}"
      data-kcal="${product.kcal}"
      data-protein="${product.protein}"
      data-carbohydrates="${product.carbohydrates}"
      data-fat="${product.fat}"
      data-total-weight="${meal.totalWeight}"
      data-weight-units="${meal.weightUnits}"
      data-weight-id="${meal.weight && meal.weight.id}"
    >
      <div class="plans-handle"></div>
      <div class="plan-item-details">
        <span class="plan-item-title">${name}</span>
      </div>
      <div class="plan-item-inputs">
        <div class="form-group">
          <a class="js-plans-choose-amount" role="button" tabindex="0">
            ${chooseAmountText}
          </a>
        </div>
        <div class="form-group">
          <span class="js-plans-product-kcal">${totalEnergy}</span>kcal
        </div>
        <button class="plan-item-delete js-plan-item-remove" type="button">
          <i class="material-icons">clear</i>
        </button>
      </div>
    </div>
  `;
}

export function PlanTotalsDrop(data, weight = 0, showChart = false) {
  const keys = ['kcal', 'protein', 'carbohydrate', 'fat'];
  const totals = {};
  let minWeight = weight;

  if (isNaN(weight) || typeof weight !== 'number') {
    minWeight = 50;
  }

  keys.forEach((key) => {
    const value = parseFloat(data[key]) || 0;

    let max = Math.round(value);
    let min = Math.round(value / (100 / minWeight));

    if (!isFinite(max)) {
      max = 0;
    }

    if (!isFinite(min)) {
      min = 0;
    }

    totals[key] = {min, max};
  });

  return html`
    <h5>${data.title}</h5>
    ${showChart ? `<div class="plan-meal-totals-chart"></div>` : ''}
    <ul class="plans-box-totals-list">
      <li class="is-muted">
        <span>In the portion</span>
        <var>${minWeight}g</var>
        <var>100g</var>
      </li>
      <li>
        <span>Energy</span>
        <var>${totals.kcal.min}kcal</var>
        <var>${totals.kcal.max}kcal</var>
      </li>
      <li class="is-blue">
        <span>Protein</span>
        <var>${totals.protein.min}g</var>
        <var>${totals.protein.max}g</var>
      </li>
      <li class="is-red">
        <span>Carbs</span>
        <var>${totals.carbohydrate.min}g</var>
        <var>${totals.carbohydrate.max}g</var>
      </li>
      <li class="is-orange">
        <span>Fat</span>
        <var>${totals.fat.min}g</var>
        <var>${totals.fat.max}g</var>
      </li>
    </ul>
  `;
}

export function PlanMealAmountDrop(mealProduct, locale) {
  const {product} = mealProduct;
  const weightId = mealProduct.weight ? parseInt(mealProduct.weight.id, 10) : 0;
  const weights = [{
    id: 0,
    name: 'Gram'
  }].concat(
    mealProduct.weights.filter((x) => {
      return !('locale' in x) || (x.locale && x.locale === locale)
    })
  );

  return html`
    <form class="plan-item-amount-form">
      ${weights.map((row, index) => {
    const id = parseInt(row.id, 10);
    const isBase = index === 0;
    const isChecked = weightId === id;
    let value = 1;

    if (isBase) {
      value = parseInt(mealProduct.totalWeight, 10) || 100;
    } else {
      if (isChecked) {
        value = mealProduct.weightUnits || 1;
      }
    }

    const suffixContainer = `<div class="plan-item-amount-total${isChecked ? ' is-visible' : ''}">`;

    return html`
          <div class="plan-item-amount-row">
            <div>
              <input class="plan-item-amount-radio" type="radio" name="weight_type" value="${id}" id="weight_type_${id}" ${isChecked ? ' checked' : ''}/>
            </div>
            <label class="control-label" for="weight_type_${id}">
              ${row.name}
              ${isBase ? '' : ` (${Math.round(row.weight)}g)`}
            </label>
            ${isBase ? '' : suffixContainer}
                <input type="number" value="${parseFloat(value)}" step="0.01" class="form-control text-right plan-item-amount-value" ${isBase ? '' : `data-weight="${row.weight}"`}>
            ${isBase ? suffixContainer : ''}
                ${isBase ? '' : ' pcs.'}
                (<var>${mealProduct.totalKcal}</var> kcal)
            </div>
          </div>
        `
  })}
      <div class="plan-item-amount-actions">
        <button class="btn btn-success" type="submit">Save amount</button>
        <div>
          <a class="product-weights-cancel js-close-amount" href="#">Cancel</a>
        </div>
      </div>
    </form>
  `;
}

export function PlanMealTotals(totals) {
  return html`
    <div class="plans-meal-totals">
      <div class="plan-totals-col">
        <span>Carbs</span>
        <var>${Math.round(totals.carbohydrate)}</var>g
      </div>
      <div class="plan-totals-col">
        <span>Protein</span>
        <var>${Math.round(totals.protein)}</var>g
      </div>
      <div class="plan-totals-col">
        <span>Fat</span>
        <var>${Math.round(totals.fat)}</var>g
      </div>
      <div class="plan-totals-col">
        <var>${Math.round(totals.weight)}</var>g
      </div>
      <div class="plan-totals-col">
        <var>${Math.round(totals.kcal)}</var>kcal
      </div>
    </div>
  `;
}

export function PlanTotalsChart(title, totals) {
  return html`
    <aside class="plans-box-side">
      <div class="plan-box-side-widget">
        <h2 class="plans-box-side-title">${title}</h2>
        ${PlanTotalsList(totals)}
        <div class="plans-box-side-chart"></div>
      </div>
    </aside>
  `;
}

export function PlanMeal(meal, type, locale) {
  const totalProducts = meal.products.length;
  const hasImage = meal.hasOwnProperty('image') && meal.hasOwnProperty('products');

  const template = html`
    <div
      class="plans-meal-plan"
      data-id="${meal.id}"
      data-protein="${meal.totals.protein}"
      data-carbohydrate="${meal.totals.carbohydrate}"
      data-fat="${meal.totals.fat}"
      data-kcal="${meal.totals.kcal}"
      data-weight="${meal.totals.weight}"
      data-ideal-kcals="${meal.ideal_kcals}"
    >
      ${PlanHeader(meal, type, false)}
      ${hasImage ? '' : PlanMealTotals(meal.totals)}
      <div class="plans-box-item-list plans-box-item-list--products" data-count="${totalProducts}"></div>
      ${PlanDropZone(type)}
      ${PlanComment(meal, type, true, 'Comment to this meal', 'Add Comment')}
    </div>
  `;

  return stripIndents`${template}`;
}

export function PlanCollapsedDescription(plan, type) {
  let children = '';

  switch (type) {
    case TYPE_WORKOUT:
      const exercises = plan.workouts.length + plan.workouts.filter(x => x.supers.length).length;
      const supersets = plan.workouts.filter(x => x.supers.length).length;

      children = html`
        <span>${exercises} Exercises in Total</span>
        <span>${supersets} Super Sets</span>
      `;
      break;
    case TYPE_MEAL:
      const meals = plan.meals.length;
      if(plan.contains_alternatives) {
        children = html`
          <span>${meals} alternatives with ${Math.round(plan.avg_totals.kcal)} kcals on average.</span>
        `;
        break;
      } else {
        children = html`
          <span>${meals} meals with ${Math.round(plan.totals.kcal)} kcal.</span>
        `;
        break;
      }
  }

  return html`
    <div class="plans-box-collapsed-description">
      ${children}
    </div>
  `;
}

export function PlanMealActions(plan, type, masterPlan, options) {
  const recipeParams = {
    'plan': masterPlan.id,
    'meal': plan.id,
    'locale': masterPlan.locale || options.locale,
  };

  if (isNumber(plan.macroSplit)) {
    recipeParams.macroSplit = plan.macroSplit;
  }

  if (isNumber(plan.type)) {
    recipeParams.type = plan.type;
  }

  return html`
    <div class="plans-box-meal-actions">
      <button class="btn btn-success js-add-plan" data-parent="${plan.id}" data-type="${type}">
        + Add New Meal
      </button>
    </div>
  `;
}

export function Plan(plan, type, masterPlan, options) {
  let listAttr = [];

  if (type === TYPE_WORKOUT) {
    const totalWorkouts = plan.workouts.length;

    listAttr.push(`data-count="${totalWorkouts}"`);
  } else if (type === TYPE_RECIPE) {
    const totalProducts = plan.products.length;
    [
      'kcal',
      'protein',
      'carbohydrate',
      'fat', 'weight',
    ].forEach(key => listAttr.push(`data-${key}="${plan.totals[key]}"`));
    listAttr.push(`data-count="${totalProducts}"`);
  }

  let canCollapse = type !== TYPE_RECIPE;
  let canClone = canCollapse;
  let canDelete = canCollapse;

  let isCollapsed = canCollapse && !plan.last;
  let isDraggable = canCollapse;

  let expandFooterAction = '';

  if (canCollapse) {
    expandFooterAction = `<div class="plans-box-plan expand-plan js-plan-toggle"><i class="material-icons expand-icon">more_horiz</i></div>`;
  }

  let collapsedDescription = '';

  if (canCollapse) {
    collapsedDescription = PlanCollapsedDescription(plan, type);
  }

  let chartTitle = 'Total Calorie Counter';
  let chartTotals = plan.totals;

  if(plan.contains_alternatives) {
    chartTitle = 'Target Calorie Counter';
    chartTotals = plan.ideal_totals;
  }

  let totalCharts = ''; // type !== TYPE_WORKOUT ? PlanTotalsChart(chartTitle, chartTotals) : '';
  let description = '';

  if (type === TYPE_MEAL && plan.contains_alternatives) {
    const planTotals = plan.ideal_totals || plan.totals;

    if(plan.locale == 'dk') {
      description = `<div class="plans-box-description">
         Til ${plan.name.toLowerCase()} skal du have <var>${Math.round(planTotals.kcal)} kcal</var>
         bestående af <var>${Math.round(planTotals.carbohydrate)}g kulhydrater, ${Math.round(planTotals.protein)}g protein</var>
         og  <var>${Math.round(planTotals.fat)}g fedt</var>.<br/>
         Dette er reflekteret i disse ${plan.meals.length} alternativer.
      </div>`
    } else {
      description = `<div class="plans-box-description">
         For ${plan.name.toLowerCase()} you should consume <var>${Math.round(planTotals.kcal)} kcal</var>
         based on approximately <var>${Math.round(planTotals.carbohydrate)}g carbs, ${Math.round(planTotals.protein)}g protein</var>
         and  <var>${Math.round(planTotals.fat)}g fat</var>.<br/>
         This is reflected in the ${plan.meals.length} alternatives I have made here.
      </div>`
    }
  }
  plan.desired_kcals = masterPlan.desired_kcals;
  return `
    <div class="plans-box${isCollapsed ? ' is-collapsed' : ' '}" data-id="${plan.id}" data-contains-alternatives="${plan.contains_alternatives}" data-meals="${plan.meals ? plan.meals.length : 0}">
      <div class="plans-box-plan">
        ${PlanHeader(plan, type, canCollapse, canClone, isDraggable, canDelete)}
        ${description}
        ${collapsedDescription}
        <div class="plans-box-item-list" ${listAttr.join(' ')}></div>
        ${type === TYPE_WORKOUT || TYPE_RECIPE ? PlanDropZone(type) : ''}
        ${type === TYPE_MEAL && plan.meals && plan.meals.length > 1 ? PlanMealActions(plan, type, masterPlan, options) : ''}
        ${type === TYPE_RECIPE || type == TYPE_WORKOUT || type == TYPE_MEAL ? PlanComment(plan, type, false) : ''}
        ${expandFooterAction}
      </div>
      ${totalCharts}
    </div>
  `;
}
