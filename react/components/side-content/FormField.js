import React, {useRef} from 'react';

import JoditEditor from "jodit-react";

const FormField = (props) => {
    const {
        templateList,
        sendData,
        clientName,
        dataKey,
        onChange,
        changeTemplate,
        handleSubmit,
        onSaveTemplate
    } = props;
    const editor = useRef(null)

    const config = {
        // all options from https://xdsoft.net/jodit/doc/
  		readonly: false,
      defaultActionOnPaste: 'insert_clear_html',
      processPasteHTML:true,
      enter:"br",
      askBeforePasteHTML:false,
      processPasteFromWord:true
  	}
    return (
        <div>
            <div className="mail-box-header">
                <h3>{sendData.title}</h3>
            </div>
            <div className="mail-box">
                <form id="send-email-to-client" onSubmit={handleSubmit}>
                    <div className="mail-body">
                        <div className="form-group form-group-padding">
                            <label htmlFor="inputAddress">To</label>
                            <input
                                type="email"
                                name="to"
                                className="form-control"
                                value={sendData.to}
                                onChange={(e) => {onChange(e.target.value, e.target.name, sendData)}}
                            />
                        </div>
                            <div className="form-row">
                                <div className="form-group col-md-8">
                                    <label>Subject</label>
                                    <input
                                        type="text"
                                        name="subject"
                                        className="form-control"
                                        value={sendData.subject}
                                        onChange={(e) => {onChange(e.target.value, e.target.name, sendData)}}
                                    />
                                </div>
                                <div className="form-group col-md-4">
                                        <label>Template</label>
                                        <select
                                            className="form-control m-b"
                                            name="template"
                                            value={sendData.template}
                                            onChange={(e) => {changeTemplate(e.target.value)}}
                                        >
                                            {templateList.length === 0 ? (
                                                <option>Default e-mail template</option>
                                            ) : (
                                                templateList.map((item, i) => {
                                                    return(
                                                        <option value={item.id} key={i}>{ item.title }</option>
                                                    )
                                                })
                                            )}
                                        </select>
                                </div>
                            </div>
                            <div className="form-group row summernote-form">
                                <JoditEditor
                                    ref={editor}
                                    tabIndex={10}
                                    config={config}
                                    value={sendData.message}
                                    onBlur={(newContent) => onChange(newContent.target.innerHTML, 'message', sendData)}
                                />
                            </div>
                        <div className="mail-body text-right">
                            <a href="#" style={{marginRight: 10}} onClick={onSaveTemplate}>Save e-mail as template</a>
                            <button type="submit" className="btn btn-sm btn-success">Send</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    )
}

export default FormField;
