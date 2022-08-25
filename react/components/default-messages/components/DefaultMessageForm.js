import React, {Fragment} from 'react';
import {connect} from "react-redux";
import * as messagesActions from "../actions/messages-action";

class DefaultMessageForm extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            id: this.props.formValues.id,
            type: this.props.formValues.type,
            title: this.props.formValues.title,
            body: this.props.formValues.body,
            subject: this.props.formValues.subject,
        };

        this.bodyRef = React.createRef();

        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleInputChange = this.handleInputChange.bind(this);
        this.insertAtCursor = this.insertAtCursor.bind(this);
    }

    handleSubmit(event) {
        const {id, type, title, body, subject} = this.state;

        event.preventDefault();
        if (id) {
            this.props.updateMessage(id, type, title, body, subject);
        } else {
            this.props.createMessage(type, title, body, subject);
        }
    }

    handleInputChange(event) {
        const target = event.target;
        const value = target.value;
        const name = target.name;

        this.setState({[name]: value});
    }

    insertAtCursor(value) {
        const textarea = this.bodyRef.current;
        let newText = textarea.value;

        if (textarea.selectionStart || textarea.selectionStart === '0') {
            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            newText = textarea.value.substring(0, startPos)
                + value
                + textarea.value.substring(endPos, textarea.value.length);
        } else {
            newText += value;
        }

        this.setState({body: newText});
    }

    render() {
        const {formValues, typeTitles, cancelAction, placeholderLabels} = this.props;
        const typeTitle = typeTitles[formValues.type] || `Type id: ${formValues.type}`;
        const typePlaceholderLabels = placeholderLabels[formValues.type] || [];
        let placeholderList = [];
        Object.keys(typePlaceholderLabels).forEach((placeholder) => {
            placeholderList.push((
                <li className="placeholder-list-item" key={placeholder} onClick={() => {
                    this.insertAtCursor(`[${placeholder}]`);
                }}>
                    <span className="bold">{placeholder}</span> - {typePlaceholderLabels[placeholder]}
                </li>
            ));
        });

        return (
            <Fragment>
                <div className="type-title">
                    <span className="type-title-name">{typeTitle}</span>
                    {(formValues.id)
                        ? <span className="type-title-id">{`ID#${formValues.id}`}</span>
                        : ''
                    }
                </div>
                <form className="default-message-form" onSubmit={this.handleSubmit}>
                    <input type="hidden" name="id" defaultValue={formValues.id} />
                    <input type="hidden" name="type" defaultValue={formValues.type} />
                    <div className="form-top">
                        <div className="form-item">
                            <label>Template Title</label>
                            <input type="text" name="title" value={this.state.title} onChange={this.handleInputChange} />
                        </div>
                        <div className="form-item">
                            <label>Subject</label>
                            <input type="text" name="subject" value={this.state.subject} onChange={this.handleInputChange} />
                        </div>
                        <div className="form-item">
                            <label>Template Body</label>
                            <textarea name="body" rows={10} ref={this.bodyRef} value={this.state.body} onChange={this.handleInputChange} />
                        </div>
                        {(placeholderList.length > 0) && (
                            <div className="form-item">
                                <label>Apply placeholders:</label>
                                <ul className="placeholder-list">
                                    {placeholderList}
                                </ul>
                            </div>
                        )}
                    </div>
                    <div className="form-bottom">
                        <button className="secondary-btn" onClick={() => cancelAction()}>Cancel</button>
                        <button type="submit" className="submit-btn">Save</button>
                    </div>
                </form>
            </Fragment>
        );
    }
}

function mapStateToProps(state) {
    return {
        formValues: state.messages.formValues,
        typeTitles: state.messages.typeTitles,
        placeholderLabels: state.messages.placeholderLabels,
    };
}
export default connect(mapStateToProps, {...messagesActions})(DefaultMessageForm);
