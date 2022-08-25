import { html } from 'common-tags';

export function OwnExercise() {
    return html`
        <div class="plan-search-option">
            <div style='flex: 1'></div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="own-exercise">
                <label class="form-check-label" for="own-exercise">Show only own exercises</label>
            </div>
        </div>
    `;
}