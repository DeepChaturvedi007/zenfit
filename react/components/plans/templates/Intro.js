import { html, safeHtml } from 'common-tags'

export function Intro() {

    const handle = '<img class="intro-handle" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABGdBTUEAALGPC/xhBQAAAFtJREFUSA3tkbEJACAQA8XFHcEl3FOx+OaQ8IJYRXgkCaS4lOL3gkDrY+6LLurwT389mfauCZA5tSr0BopOPiNzatXkDRSdfEbm1KrJGyg6+YzMqVWTN1B0/mQLKEk130qvR2oAAAAASUVORK5CYII=" />';
    const gif = '<img class="intro-gif" src="/images/food-item.gif" />';

    const title = 'Drag and drop food items';

    const description = `Use the dots ${handle} to drag the food items`;

    return html`    
        <div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1">
            <div class="widget-head-color-box navy-bg p-lg text-center">
                <div class="m-b-md">
                    <h2 class="font-bold no-margins">
                        ${title}
                    </h2>
                    <p>${description}</p>
                </div>
                ${gif}
            </div>
        </div>
  `;


}