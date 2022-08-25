import { html, safeHtml } from 'common-tags';
import { pictureFilter } from '../helpers';
import {
  TYPE_WORKOUT
} from '../constants';
import {
  exerciseTransformer,
  productTransformer
} from '../helpers';

export function SearchFilters({ equipments, muscles }) {
  return html`
    <div class="plans-search-filters">
      <div class="plans-search-filter">
        <select class="form-control" data-key="muscleId">
          <option value="" selected>Filter by Muscle Group</option>
          ${muscles.map(item => safeHtml`<option value="${item.id}">${item.name}</option>`)}
        </select>
      </div>
      <div class="plans-search-filter">
        <select class="form-control" data-key="equipmentId">
          <option value="" selected>Filter by Equipment</option>
          ${equipments.map(item => safeHtml`<option value="${item.id}">${item.name}</option>`)}
        </select>
      </div>
    </div>

  `;
}

export function SearchExerciseItem(data, locale, options = {}) {
  const workout = exerciseTransformer(data);
  const exercise = workout.exercise;
  const preview = `/api/exercises/exerciseInfo/${exercise.id}`;
  const thumbnail = pictureFilter(options.s3, exercise.picture, 'exercises', 50) || '/images/exercise_thumbnail.png';
  const description = [];

  if (exercise.muscle) {
    description.push(exercise.muscle.name);
  }

  if (exercise.equipment) {
    description.push(exercise.equipment.name);
  }

  if (exercise.type) {
    description.push(exercise.type.name);
  }

  const workoutJSON = encodeURI(JSON.stringify(workout));

  return html`
    <div
      class="plan-item"
      data-id="${exercise.id}"
      data-item="${workoutJSON}"
    >
      <div title="You drag an exercise by pulling these dots - try for yourself!" class="plans-handle"></div>
      <div class="plan-item-thumb">
        <a
            class="js-exercise-preview"
            href="${preview}"
            data-exercise-type="${workout.type && workout.type.name}"
        >
          <img src="${thumbnail}" alt="">
          <span></span>
        </a>
      </div>
      <div class="plan-item-details">
          <a class="plan-item-title js-exercise-preview" href="${preview}" data-exercise-type="${workout.type && workout.type.name}">
            ${exercise.name ? exercise.name : 'N/A'}
          </a>
          <div class="plan-item-description">${description.join(' - ')}</div>
      </div>
    </div>
  `;
}

export function SearchExerciseYoutubeItem(data, locale, options = {}, searchParams = {}) {
  const thumbnail = data.snippet.thumbnails.default.url;
  const video = `https://www.youtube.com/watch?v=${data.id.videoId}`;
  const preview = `/api/exercises/youtubeExerciseInfo/${data.id.videoId}`;
  const description = [];

  return html`
    <div class="plan-item plan-item--youtube no-drag">
      <div class="plan-item-thumb">
        <a
            class="js-exercise-preview"
            href="${preview}"
        >
          <img src="${thumbnail}" alt="">
          <span></span>
        </a>
      </div>
      <div class="plan-item-details">
          <a class="plan-item-title js-exercise-preview" href="${preview}">
            ${data.snippet.title}
          </a>
          <div class="plan-item-description text-right">
            <a
              data-toggle="modal"
              data-type="new"
              data-target="#user_exercise_modal"
              data-title="${searchParams.q}"
              data-video="${video}"
              href="#"
            >Create as Exercise</a>
          </div>
      </div>
    </div>
  `;
}

export function SearchYoutubeHeader() {
  return html`
    <div class="plans-search-youtube no-drag">
      <div class="plans-search-youtube-header">
        <div class="plans-search-youtube-logo">
          <svg viewBox="0 0 56 24" preserveAspectRatio="xMidYMid meet">
            <g viewBox="0 0 56 24" preserveAspectRatio="xMidYMid meet" class="style-scope yt-icon">
              <path id="you-path" d="M20.9 19.3h-2.12v-1.24c-.8.94-1.5 1.4-2.23 1.4-.66 0-1.1-.3-1.34-.87-.12-.35-.22-.88-.22-1.67V7.9h2.12V17.16c.05.3.18.42.45.42.4 0 .78-.37 1.23-1V7.9h2.12v11.4M13.4 11.62c0-1.22-.23-2.13-.66-2.7-.56-.8-1.45-1.1-2.35-1.1-1.02 0-1.8.3-2.35 1.1-.44.57-.67 1.5-.67 2.7v4.07c0 1.2.2 2.04.64 2.6.56.8 1.48 1.2 2.37 1.2.9 0 1.82-.4 2.4-1.2.4-.56.6-1.4.6-2.6V11.6zm-2.1 4.3c.1 1.13-.25 1.7-.9 1.7-.66 0-1-.57-.9-1.7V11.4c-.1-1.13.24-1.66.9-1.66.65 0 1 .53.9 1.66v4.52zM5.03 13.1v6.2H2.8v-6.2S.47 5.46 0 4.04h2.35L3.92 10l1.56-5.95h2.34l-2.8 9.04" class="style-scope yt-icon"></path>
              <g id="tube-paths" class="style-scope yt-icon">
                <path d="M42.74 9.7c-.33 0-.7.2-1.05.52v6.86c.33.34.7.5 1.04.5.6 0 .85-.42.85-1.55v-4.86c0-1.13-.27-1.46-.86-1.46M51.08 11.07c0-1.05-.27-1.36-.94-1.36-.67 0-.96.3-.96 1.35v1.25h1.9v-1.23" class="style-scope yt-icon"></path>
                <path d="M55.67 5.28s-.33-2.3-1.33-3.33C53.07.6 51.64.6 51 .53 46.33.2 39.32.2 39.32.2h-.02s-7 0-11.67.33c-.65.08-2.08.08-3.35 1.42-1 1.02-1.32 3.33-1.32 3.33s-.34 2.72-.34 5.44v2.55c0 2.72.34 5.43.34 5.43s.32 2.32 1.32 3.34c1.27 1.34 2.94 1.3 3.68 1.43 2.67.26 11.35.34 11.35.34s7.03 0 11.7-.34c.65-.08 2.07-.08 3.34-1.42 1-1.02 1.33-3.34 1.33-3.34S56 16 56 13.27v-2.55c0-2.72-.33-5.44-.33-5.44zM29.95 19.3h-2.23v-13h-2.35V4.18h7.04V6.3h-2.45v13zm8.05 0h-2.12v-1.24c-.8.94-1.5 1.4-2.23 1.4-.66 0-1.1-.3-1.34-.87-.12-.35-.22-.88-.22-1.67V8h2.12v9.17c.05.3.18.42.45.42.4 0 .78-.37 1.23-1V8H38v11.3zm7.7-3.38c0 1.04-.07 1.78-.2 2.26-.28.84-.87 1.27-1.67 1.27-.72 0-1.46-.44-2.14-1.28v1.14h-2.02V4.18h2V9.1c.66-.8 1.4-1.27 2.15-1.27.8 0 1.34.47 1.6 1.3.15.47.28 1.2.28 2.27v4.52zm4.46 1.67c.5 0 .8-.28.9-.83.02-.1.02-.6.02-1.42h2.12v.32c0 .66-.05 1.13-.07 1.33-.07.46-.23.87-.47 1.23-.56.82-1.4 1.22-2.45 1.22-1.05 0-1.85-.38-2.44-1.16-.43-.57-.7-1.4-.7-2.6v-3.96c0-1.2.25-2.14.68-2.72.58-.77 1.4-1.18 2.42-1.18s1.82.4 2.4 1.18c.4.58.65 1.46.65 2.67V14H49.2v2.02c0 1.05.3 1.57.98 1.57z" class="style-scope yt-icon"></path>
              </g>
            </g>
          </svg>
        </div>
        <h5>Exercise Videos</h5>
      </div>
      <p class="plans-search-youtube-description">Use Youtube Videos to Create Custom Exercises!</p>
    </div>
  `;
}

export function SearchYoutubeFooter() {
  return html`
    <div class="plans-search-youtube-footer no-drag">
        <a class="plans-search-youtube-more js-more-youtube-results" href="#">See More Youtube Results</a>
    </div>
  `;
}

export function EmptyStateSearch(type, q, itemCount = 0) {
  let description;

  if (itemCount) {
    description = type === TYPE_WORKOUT ?
      'There are no more exercises matching your search query. Fortunately, you can create your own exercise!' :
      'There are no more food products matching your search query. Fortunately, you can create your own food products!';
  } else {
    description = type === TYPE_WORKOUT ?
      'Sorry, we couldn\'t find any exercises matching your search query. Fortunately, you can create your own exercise!' :
      'Sorry, we couldn\'t find any food items matching your search query. Fortunately, you can create your own food items!';
  }

  const button = type === TYPE_WORKOUT ?
    'Create Exercise' :
    'Create Food Item';

  const modal = type === TYPE_WORKOUT ?
    '#user_exercise_modal' :
    '#addMealProduct';

  return html`
    <div class="search-result-empty-state no-drag">
        <p>${description}</p>
        <button
          class="btn btn-success"
          data-toggle="modal"
          data-type="new"
          data-target="${modal}"
          data-title="${q}">
          ${button}</button>
    </div>
  `;
}

export function SearchProductItem(data, locale, options = {}) {
  const meal = productTransformer(data);
  const product = meal.product;
  let translatedName = product.name;

  if (!translatedName) {
    return html``;
  }

  const description = [
    `Protein ${product.protein}g`,
    `Carbs ${product.carbohydrates}g`,
    `Fat ${product.fat}g`
  ];

  const mealJSON = encodeURI(JSON.stringify(meal));

  return html`
    <div
      class="plan-item"
      data-id="${product.id}"
      data-item="${mealJSON}"
    >
      <div class="plans-handle"></div>
      <div class="plan-item-details">
          <span>${translatedName}${product.brand ? `, ${product.brand}` : ""}${product.recommended && options.admin ? ` <span class="glyphicon glyphicon-star text-success"></span>` : ""}</span>
          <div class="plan-item-description">
            ${description.join('  ')}
            <span class="pull-right">${product.kcal}kcal / 100g</span>
          </div>
      </div>
    </div>
  `;
}

export function SearchLanguage(selectedLocale) {

  let locales = {
    en: {
      title: 'English',
      flag: 'em-gb'
    },
    da_DK: {
      title: 'Danish',
      flag: 'em-flag-dk'
    },
    sv_SE: {
      title: 'Swedish',
      flag: 'em-flag-se'
    },
    nb_NO: {
      title: 'Norwegian',
      flag: 'em-flag-no'
    },
    fi_FI: {
      title: 'Finnish',
      flag: 'em-flag-fi'
    },
    nl_NL: {
      title: 'Dutch',
      flag: 'em-flag-nl'
    },
    de_DE: {
      title: 'German',
      flag: 'em-flag-de'
    }
  }

  let list = Object.keys(locales)
    .filter(key => {
      if (selectedLocale == key) {
        return false;
      }
      return true;
    })
    .map(key => {
      let flag = `<i class="em ${locales[key].flag}"></i>`;
      return `<li><a class="js-plans-locale" role="button" tabindex="0" data-lang="${key}"><span class="flag">${flag}</span>${locales[key].title}</a></li>`;
    });

  return html`
    <div>
      <span class="search-food-language">Food Item List Language:</span>
        <ul class="search-language">
            <li class="active">
                 <span class="flag"><i class="em ${locales[selectedLocale].flag}"></i></span>
                 ${locales[selectedLocale].title} <i class="fa fa-caret-down"></i>
                <ul>${list}</ul>
            </li>
        </ul>
    </div>
  `;
}

export function SearchForm({ type, equipments, muscles, locale, q }) {
  const isWorkout = type === TYPE_WORKOUT;
  const placeholder = isWorkout ? 'Search Exercises, Equipment, Muscle Groups...' : 'Search product';

  return html`
    <form class="plans-search-form">
      ${isWorkout ? '' : SearchLanguage(locale)}
      <div class="plans-search-text">
        <input
          class="form-control"
          type="text"
          name="q"
          value="${q}"
          placeholder="${placeholder}"
          autocomplete="off"
        />
        <button type="submit">
          <i class="material-icons">search</i>
        </button>
      </div>
      ${isWorkout ? SearchFilters({ equipments, muscles }) : ''}
    </form>
  `;
}
