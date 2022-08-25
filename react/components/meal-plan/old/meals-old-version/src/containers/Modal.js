// eslint-disable-next-line
import React, { useState } from "react";
import update from "react-addons-update";
import { createContainer } from "../utils/unstated";

function useModal() {
  const [modal, setModal] = useState({
    type: null,
    props: {},
    activity: null,
    isLoading: false,
    progressValue: null,
    selectedRecipe: []
  });
  React.useEffect(() => {
    
  })
  /**
   * @param {string|null} type
   * @param {?Object} props
   */
  const setActivity = (type, props = {}) => {
    setModal(update(modal, {
      activity: {
        $set: type ? { type, props } : null,
      },
    }));
  };

  /**
   *
   * @param {Object} props
   */
  const setActivityProps = (props) => {
    if (modal.activity) {
      setModal(update(modal, {
        activity: {
          props: { $merge: props },
        },
      }));
    }
  };

  /**
   * @param {string} type
   *
   * @returns {boolean}
   */
  const is = (type) => type === modal.type;

  /**
   * @param {string} type
   * @param {?Object} props
   */
  const show = (type, props) => {
    setModal(update(modal, {
      type: { $set: type },
      props: { $set: props },
    }));
  };

  const hide = () => {
    setModal(update(modal, {
      type: { $set: null },
      props: { $set: {} },
      activity: { $set: null },
      selectedRecipe: { $set: [] }
    }));
  };

  /**
   * @param {boolean} isLoading
   */
  const loading = (isLoading) => {
    setModal(update(modal, {
      isLoading: {
        $set: isLoading,
      },
    }));
  };

  /**
   * @param {number|null} progressValue
   */
  const setProgressValue = (progressValue) => {
    setModal(update(modal, {
      progressValue: {
        $set: progressValue,
      },
    }));
  };

  const selectRecipe = (recipe, seleted) => {
    let list = [];
    recipe.seleted = seleted;
    const objIndex = modal.selectedRecipe.findIndex((obj => obj.id === recipe.id));
    if(objIndex !== -1){
      list = modal.selectedRecipe.filter((item) => {
        return item.id !== recipe.id;
      })
    }
    else {
      list = [...modal.selectedRecipe, recipe];
    }
    setModal(update(modal, {
      selectedRecipe: {
        $set: list,
      },
    }));
  }

  return { ...modal, setActivity, setActivityProps, is, show, hide, loading, setProgressValue, selectRecipe };
}

const ModalContainer = createContainer(useModal);

export default ModalContainer;
