import React from 'react';
import DOMPurify from "dompurify";
import TextTruncate from "react-text-truncate";
import ItemActions from "./ItemActions";

const DefaultMessageItem = (props) => {
    const {placeholders, message, title, onClick, actions, messageId, messageType, subject} = props;

    let renderMsg = message;
    Object.keys(placeholders).forEach((key) => {
        if (renderMsg.indexOf(`[${key}]`) !== -1) {
            const re = new RegExp(`\\[${key}\\]`, "g");
            renderMsg = renderMsg.replace(re, placeholders[key]);
        }
    });

    renderMsg = DOMPurify.sanitize(renderMsg, {ALLOWED_TAGS: {}});

    return (
        <div className="template-item">
            <div className="template-item-content" onClick={() => {
                onClick(renderMsg);
            }}>
                {(actions)
                    ? <ItemActions
                        actions={actions}
                        messageBody={message}
                        messageId={messageId}
                        messageType={messageType}
                        messageTitle={title}
                        messageSubject={subject}
                    />
                    : ''
                }
                <div className="template-item-title">{title}</div>
                <div className="template-item-body">
                    <TextTruncate
                        text={renderMsg}
                        truncateText="..."
                        element="span"
                        line={4}
                    />
                </div>
                <div className="template-item-btm">
                    <div className="template-item-btn template-item-btn--edit" onClick={() => {
                        if (actions.edit) {
                            actions.edit.fn(message, messageId, title, messageType, subject);
                        }
                    }}>View / Edit</div>
                </div>
            </div>
        </div>
    );
};

export default DefaultMessageItem;
