import React from 'react';
import Modal from './Modal';
import Recipes from '../Recipes';
import Settings from '../Settings';
import ModalContainer from '../../containers/Modal';
import MealPlansContainer from '../../containers/MealPlans';
import ModalTypes from '../../constants/ModalTypes';
import SettingTypes from '../../constants/SettingTypes';

const MODAL_TITLES = {
  [ModalTypes.RECIPES]: 'Recipes',
  [SettingTypes.SETTINGS_DESCRIPTION]: 'Description',
  [SettingTypes.PDF]: 'PDF',
  [SettingTypes.DELETE]: 'DELETE',
};

const Modals = () => {
  const modal = ModalContainer.useContainer();
  const mealPlan = MealPlansContainer.useContainer();
  let ChilderComponent = null;
  let title = MODAL_TITLES[modal.type] || '';
  let shouldCloseOnOverlayClick = false;
  let childrenProps = {};
  
  if (modal.is(ModalTypes.RECIPES)) {
    ChilderComponent = Recipes;
    shouldCloseOnOverlayClick = true;

    childrenProps.modal = modal;
    childrenProps.mealPlans = mealPlan;
  } else if (modal.is(ModalTypes.SETTINGS)) {
    ChilderComponent = Settings;
    title = `Meal Plan ${MODAL_TITLES[modal.props.settingsType]}`;
  } else if (modal.is(ModalTypes.CUSTOM)) {
    ChilderComponent = modal.props.content || null;
    title = modal.props.title || null;
  }
  
  return (
    <Modal
      {...modal.props}
      contentLabel={`${title} Modal`}
      shouldCloseOnOverlayClick={shouldCloseOnOverlayClick}
      shouldCloseOnEsc={true}
      onRequestClose={modal.hide}
      isOpen={!!modal.type}
      isLoading={modal.isLoading}
      progressValue={modal.progressValue}
      title={title}
      selectedRecipe={modal.selectedRecipe}
      handleSelectedRecipe={mealPlan.addDish}
      handleRemoveRecipe={mealPlan.onRemoveDish}
      setActivity={modal.setActivity}
    >
      {ChilderComponent && <ChilderComponent
        {...modal.props}
        {...childrenProps}
        selectedRecipe={modal.selectedRecipe}
        onSelectRecipe={modal.selectRecipe}
        onClose={modal.hide}
      />}
    </Modal>
  );
};

export default Modals;
