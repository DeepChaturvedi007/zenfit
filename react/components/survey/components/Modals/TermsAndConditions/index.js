import './styles.scss';
import React, {useEffect, useState} from 'react';
import {
  Modal,
  CloseButton,
  ModalHeader,
  ModalBody,
  ModalFooter,
} from "../../common/ui/Modal";
import TermsAndConditions from "../../TermsAndConditions";

const TermsAndConditionsModal = ({ show, onHide }) => {
  const [html, setHtml] = useState('');
  useEffect(() => {
    const customTermsAndConditions = document.getElementById('survey-page-hidden');
    if(customTermsAndConditions && customTermsAndConditions.innerText.length) {
        setHtml(customTermsAndConditions.innerText);
    }
  },[]);

  return (
    <Modal show={show} onHide={onHide}>
      <ModalHeader>
        <h5 className={'inline-block m-0'}>Terms and conditions</h5>
        <CloseButton onClick={onHide} label={'x'}/>
      </ModalHeader>
      <ModalBody id={'terms-and-conditions-content'}>
        <TermsAndConditions terms={html} />
      </ModalBody>
      <ModalFooter>
        <button className={'btn btn-secondary'} onClick={onHide}>Close</button>
      </ModalFooter>
    </Modal>
  );
};

export default TermsAndConditionsModal;