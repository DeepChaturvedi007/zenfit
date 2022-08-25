import { html } from 'common-tags';
import {
  TYPE_WORKOUT
} from '../constants';

export function EmptyState({ type, client, template, plan }, isTemplate) {
  let title;
  let plansLeftAlert;
  let plansLeftDescription;
  let disabled;

  if (isTemplate) {
    title = type === TYPE_WORKOUT ? 'Workout Template' : 'Meal Template';
  }

  const btn = type === TYPE_WORKOUT ? 'Use Workout Template' : 'Use Meal Template';
  const scratchUrl = type === TYPE_WORKOUT ? `/workout/from-scratch/${plan.id}` : `/meal/from-scratch/${plan.id}`;

  const description = type === TYPE_WORKOUT ?
      'Welcome to the Workout Plan Builder! You can choose to create the Workout Plan from an existing workout or you can start from scratch.' :
      'Welcome to the Meal Plan Builder! You can choose to create the Meal Plan from an existing workout or you can start from scratch.';

  return html`
    <div class="empty-state">
      <h2 class="empty-state-title">Create ${title}</h2>
      <p class="empty-state-description">${description}</p>
      <div class="empty-state-actions">
        <button class="btn btn-primary js-use-template">${btn}</button>
        <span class="or-selector">OR</span>
        <a
          role="button"
          href="${scratchUrl}"
          class="btn btn-default btnStart"
        >Start From Scratch</a>
      </div>
    </div>
  `;
}
