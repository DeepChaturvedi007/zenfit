import React from 'react';
import DOMPurify from "dompurify";
import TextTruncate from "react-text-truncate";

const ChatTemplateItem = (props) => {
    const {placeholders, message, title, useTemplateAction, previewTextAction, messageId, invoice} = props;
    let renderMsg = message;
    if(!invoice){
        Object.keys(placeholders).forEach((key) => {
            if (renderMsg.indexOf(`[${key}]`) !== -1) {
                const re = new RegExp(`\\[${key}\\]`, "g");
                renderMsg = renderMsg.replace(re, placeholders[key]);
            }
        });
        renderMsg = DOMPurify.sanitize(renderMsg, {ALLOWED_TAGS: {}});
    }
    function urlify(text) {
        var urlRegex = /(https?:\/\/[^\s]+)/g;
        return text.replace(urlRegex, function(url) {
        return '<a href="' + url + '" target="_blank">' + url + '</a>';
        })
    }
    // renderMsg = 'https://www.google.com/search?q=long+text+witout+space+in+html&rlz=1C5GCEA_enKR922KR922&oq=long+text+witout+space+in+html&aqs=chrome..69i57.16279j0j7&sourceid=chrome&ie=UTF-8https://www.google.com/search?q=long+text+witout+space+in+html&rlz=1C5GCEA_enKR922KR922&oq=long+text+witout+space+in+html&aqs=chrome..69i57.16279j0j7&sourceid=chrome&ie=UTF-8';
    return (
        <div className="template-item">
            <div className="template-item-content">
                <div className="template-item-title">
                    <span className="template-item-title-main">{title}</span>
                    <span className="template-item-title-id">{`ID #${messageId}`}</span>
                </div>
                <div className="template-item-body">
                    <div dangerouslySetInnerHTML={{__html: urlify(renderMsg)}} className="template-item-message-text"/>
                </div>
                <div className="template-item-btm">
                    <div className="template-item-btn template-item-btn--primary" onClick={() => {
                        useTemplateAction(renderMsg);
                    }}>Use template</div>
                    {/*<div className="template-item-btn template-item-btn--secondary" onClick={() => {*/}
                    {/*    previewTextAction(renderMsg);*/}
                    {/*}}>View text</div>*/}
                </div>
            </div>
        </div>
    );
};

export default ChatTemplateItem;
