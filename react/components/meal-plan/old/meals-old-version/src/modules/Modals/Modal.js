import React, { useEffect, useRef, cloneElement, useState } from 'react';
import ReactModal from 'react-modal';
import { ReactComponent as CloseIcon } from 'remixicon/icons/System/close-line.svg';
import CircularProgress from '@material-ui/core/CircularProgress'
import {
  ModalDialog,
  ModalClose,
  ModalHeader,
  ModalTitle,
  ModalFooter
} from '../../components/Modal';
import { Spinner } from "../../components/Spinner";

const style = {
  overlay: {
    backgroundColor: 'rgba(0, 0, 0, 0.68)',
    zIndex: 5000,
  },
  content: {
    background: 'none',
    border: 0,
    borderRadius: '6px',
    padding: 0,
    left: 0,
    right: 0,
    bottom: 0,
    top: 0,
  },
};

const Modal = React.memo(({ meal, dish, title, Footer, children, shouldCloseOnOverlayClick, isOpen, isLoading, progressValue, selectedRecipe, handleSelectedRecipe, handleRemoveRecipe, ...props }) => {
  const dialogRef = useRef();
  const modalRef = useRef();
  const addRecipeList = selectedRecipe.filter(item => {
    return !item.seleted
  })
  const [allSubmit, setAllSubmit] = useState(false);
  const handleClickOutside = event => {
    const dialog = dialogRef.current;

    if (dialog && !dialog.contains(event.target)) {
      props.onRequestClose();
    }
  };
  const addAll = () => {
    let apiCount = 0;
    setAllSubmit(true)
    props.setActivity(true);
    const removeRecipe = selectedRecipe.filter((item) => {
      return item.seleted
    })
    const addRecipe = selectedRecipe.filter((item) => {
      return !item.seleted
    })
    removeRecipe.map((item) => {
      const dishId = dish ? dish.id : null;
      const column = dish => dish.recipe === item.id;
      handleRemoveRecipe(meal.id, column).then(res => {
        apiCount++;
        if(apiCount === removeRecipe.length){
          addRecipe.map((item) => {
            const dishId = dish ? dish.id : null;
            handleSelectedRecipe(item, meal.id, dishId).then(res => {
              apiCount++;
              if(apiCount === selectedRecipe.length){
                setAllSubmit(false);
                props.onRequestClose();
              }
            })
          })
          if(removeRecipe.length === selectedRecipe.length){
            setAllSubmit(false);
            props.onRequestClose();
          }
        }
      })
      return 0;
    })
    if(removeRecipe.length === 0) {
      addRecipe.map((item) => {
        const dishId = dish ? dish.id : null;
        handleSelectedRecipe(item, meal.id, dishId).then(res => {
          apiCount++;
          if(apiCount === selectedRecipe.length){
            setAllSubmit(false);
            props.onRequestClose();
          }
        })
      })
    }
  }
  useEffect(() => {
    if (shouldCloseOnOverlayClick && isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    } else {
      document.removeEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpen]);

  return (
    <ReactModal
      {...props}
      isOpen={isOpen}
      ariaHideApp={false}
      style={style}
      ref={modalRef}
    >
      {/* <p>test modal</p> */}
      <ModalDialog ref={dialogRef}>
        <ModalHeader sticky="true">
          <ModalTitle>{title}</ModalTitle>
          {isLoading ? <Spinner value={progressValue} /> : (
            <ModalClose type="button" onClick={props.onRequestClose}>
              <CloseIcon />
            </ModalClose>
          )}
        </ModalHeader>
        {children ? cloneElement(children, { modalRef, dialogRef }) : null}
        {selectedRecipe.length !== 0 && (
          <ModalFooter sticky="true">
            {/* <span>{addRecipeList.length} recipes selected</span> */}
            <div style={{flex: 1}} />
            <button className={'btn btn-success'} onClick={addAll} disabled={allSubmit}>
              {allSubmit && (
                <CircularProgress size={14} />
              )}
              Update Meal Plan
            </button>
          </ModalFooter>
        )}
      </ModalDialog>
    </ReactModal>
  );
});

Modal.defaultProps = {
  onRequestClose: () => { },
  backdropClose: false,
};

export default Modal;
