import { html } from 'common-tags'

export function ModalSuccessMessage(title, description) {
  return html`
    <div class="modal-success">
      <div class="modal-success-icon"></div>
      <h4 class="modal-success-title">${title}</h4>
      <p class="modal-success-description">${description}</p>
    </div>
  `;
}
