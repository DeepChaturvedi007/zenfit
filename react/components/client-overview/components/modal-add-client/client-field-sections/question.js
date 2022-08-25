import React from 'react';

import {question} from '../help-const';

const Questions = (props) => {
    const {
        value,
        handleHelpView,
        handleCheckBox
    } = props;

    return(
        <div className="checkbox">
            <label>
                <input
                    type="checkbox"
                    checked={value}
                    id="questionnaire"
                    name="questionnaire"
                    onChange={(e) => {handleCheckBox(!value, e.target.name)}}
                />
                Send questionnaire to client.
            </label>
            <a className="read-more" onClick={() => {handleHelpView(question.title, question.content)}}>What is the questionnaire?</a>
            {value && (
                <div className="description-box">
                    <small>We'll notify you when questionnaire has been answered.</small>
                </div>
            )}
        </div>
    )
}

export default Questions;