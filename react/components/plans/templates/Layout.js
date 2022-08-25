import { html } from 'common-tags'
import { BoardHeader } from './Board';
import { SearchForm } from './Search';
import { OwnExercise } from './OwnExerciseOption';
import { EmptyState } from './EmptyState';
import { Intro } from './Intro';
import { PlanComment } from './Plan';
import { TYPE_MEAL, TYPE_WORKOUT, TYPE_RECIPE } from '../constants';

export function Layout({ data, updatedAt, type, equipments, muscles, locale, empty, client, plan, q, showIntro, template, options, isWorkout }, isTemplate) {
  if (empty) {
    return html`
      ${EmptyState({ type, client, template, plan }, isTemplate)}
    `;
  }

  let displayIntro = type === TYPE_MEAL
    ? `<div class="row intro-box">${showIntro ? Intro() : ''}</div>`
    : '';

  let planComment = '';

  if (type === TYPE_WORKOUT) {
    planComment = PlanComment(plan, type, false, 'Write an overall comment to this Workout Plan.', null, {
      containerClass: 'plans-board-comment',
    });
  }

  const title = type === TYPE_WORKOUT ? 'Exercises' : 'Food items';
  const modalTitle = type === TYPE_WORKOUT ? 'Create Your Own Exercise' : 'Create Your Own Food Item';
  const modal = type === TYPE_WORKOUT ? '#user_exercise_modal' : '#addMealProduct';
  const searchBar = `
    <aside class="plans-search">
      ${SearchForm({ type, equipments, muscles, locale, q })}
      <div class="plans-search-title">
        <p>${title}</p>
        <a data-toggle="modal" data-type="new" data-target="${modal}" data-title="" data-video="" href="#">
          <i class="fa fa-plus-square" aria-hidden="true"></i>
        </a>
        <div style="flex: 1"></div>
        ${isWorkout ? OwnExercise() : ''}
      </div>
      <div class="plans-search-result has-touch-scrolling"></div>
    </aside>
  `;

  return `
    ${searchBar}
    <section class="plans-board">
        ${BoardHeader({ updatedAt, type, plan, client, options, data }, isTemplate)}
        <div class="plans-board-list"></div>
        ${displayIntro}
        ${planComment}
    </div>
    <div class="plans-temp"></div>
    </section>
  `;


}
