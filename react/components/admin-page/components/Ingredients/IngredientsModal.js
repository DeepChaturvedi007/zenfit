import React from 'react';
import {
  Modal,
  CloseButton,
  ModalHeader,
  ModalBody,
  ModalFooter,
} from "../common/Modal";
import IngredientForm from "../forms/IngredientForm";

const IngredientsModal = ({show, onHide, onSubmit, ingredient}) => {
  return (
    <Modal show={show} onHide={onHide}>
      <ModalHeader>
        <CloseButton onClick={onHide} label={'x'}/>
      </ModalHeader>
      <ModalBody id={'ingredient-form-content'}>
        <IngredientForm onSubmit={onSubmit} ingredient={ingredient} />
      </ModalBody>
      <ModalFooter>
        <button className={'btn btn-secondary'} onClick={onHide}>Close</button>
      </ModalFooter>
    </Modal>
  );
};

export default IngredientsModal;
