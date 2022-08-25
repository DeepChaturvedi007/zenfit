import React, { useState, useEffect, useMemo, useRef } from "react";
import find from "lodash/find";
import ReactQuill from "react-quill";
import "react-quill/dist/quill.core.css";
import { useLoads } from "react-loads";
import "../../assets/quill.snow.css";
import MessageTypes from "../../constants/MessageTypes";
import Loading from "../../Loading";
import {FormGroup, FormLabel, Input, Select} from "../../components/Form";
import * as api from "../../utils/api";
import { Alert } from "../../components/UI";
import { transformDefaultMessage } from "../../utils/helpers";
import * as PlanDescriptions from "../../constants/PlanDescriptions";

const modules = {
  toolbar: [
    [{ 'header': [1, 2, 3, false] }],
    ['bold', 'italic', 'underline','strike', 'blockquote'],
    [{'list': 'ordered'}, {'list': 'bullet'}],
    ['link', 'image'],
  ],
};

const formats = [
  'header',
  'bold', 'italic', 'underline', 'strike', 'blockquote',
  'list', 'bullet',
  'link', 'image'
];

const DescriptionForm = ({ plan, values, handleBlur, setFieldValue, setFieldTouched, onTemplateDetection }) => {

  const {
    templateId,
    message,
    templateName,
  } = values;
  const fetch = () => api.fetchDefaultMessages(MessageTypes.DEFAULT, plan.client);

  const { response, isRejected, isPending, isResolved, load } = useLoads(fetch, {}, [plan.id]);

  const isTemplate = useRef(false);
  // Check if the message template has the same content and positive ID (it means that the entity exists in the DB);
  const isExistingTemplate = item => item.message === content && Number(item.value) && Number(item.value) > 0;

  const placeholders = useMemo(() => ({
    client: plan.client_name,
    trainer: plan.user,
    kcals: `${plan.desired_kcals} kcals`,
  }), [plan]);

  const messages = useMemo(() => {
    const defaultMessages = [
      { name: 'Current description', value: "0", message: plan.explaination },
      { name: 'English - Zenfit Meal Description Template', value: 'en', message: PlanDescriptions.ENGLISH },
      { name: 'Dansk - Zenfit Meal Description Template', value: 'dk', message: PlanDescriptions.DANSK },
      { name: 'Swedish - Zenfit Meal Description Template', value: 'sv', message: PlanDescriptions.SWEDISH },
      { name: 'Norwegian - Zenfit Meal Description Template', value: 'no', message: PlanDescriptions.NORWEGIAN },
    ];

    if (!response) {
      return defaultMessages;
    }

    const messages = response.data.defaultMessages;

    return Object.values(messages).reduce((list, message) => {
      list.splice(1, 0, { name: message.title, value: String(message.id), message: message.message });
      return list;
    }, defaultMessages);

  }, [response, isResolved]);

  const [messageId, setMessageId] = useState("0");

  const content = useMemo(() => {
    return transformDefaultMessage(message, placeholders, !!templateId)
  }, [message, templateId]);

  const onEditorChange = (value) => {
    const content = transformDefaultMessage(value, placeholders, isTemplate.current);
    if (message !== content) setFieldValue('message', content);
  };

  const onTemplateNameChange = (event) => {
    const value = event.target.value;
    if (templateName !== value) setFieldValue('templateName', value);
  };

  const onEditorBlur = () => {
    setFieldTouched('message', true);
  };

  useEffect(() => {
    const result = find(messages, { value: messageId });
    if (result) {
      onEditorChange(result.message)
    }
    const templateId = isExistingTemplate(result) ? result.value : undefined;
    setFieldValue('templateId', templateId);
    setFieldValue('templateName', result.name);
  }, [messageId]);

  useEffect(() => {
    const template = messages.find(isExistingTemplate);
    if(template) setMessageId(template.value);
  }, [messages]);

  return (
    <React.Fragment>
      {isPending && <Loading/>}
      {isRejected && (
        <Alert type="danger">
          Cannot load default descriptions.
        </Alert>
      )}
      {isResolved && (
        <React.Fragment>
          <FormGroup>
            <Select value={messageId} onChange={(e) => setMessageId(e.target.value)}>
              {messages.map((option, index) => (
                <option value={option.value} key={`message_${index}`}>
                  {option.name}
                </option>
              ))}
            </Select>
          </FormGroup>
          <ReactQuill
            theme="snow"
            placeholder=""
            modules={modules}
            formats={formats}
            value={content}
            onChange={onEditorChange}
            onBlur={onEditorBlur}
          />
          {values.freshTemplate && (
            <FormGroup>
              <FormLabel required>
                Template Name
              </FormLabel>
              <Input
                autoComplete="off"
                name="name"
                value={values.templateName}
                type="text"
                onChange={onTemplateNameChange}
                onBlur={handleBlur}
              />
            </FormGroup>
          )}
        </React.Fragment>
      )}
    </React.Fragment>
  );
};


export default DescriptionForm;
