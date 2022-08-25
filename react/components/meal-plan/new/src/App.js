import React from "react";
import PropTypes from "prop-types";
import MealPlansContainer from './containers/MealPlans';
import ModalContainer from './containers/Modal';
import Plans from './modules/Plans';
import Modals from './modules/Modals';

export function App({ global }) {
  return (
    <ModalContainer.Provider>
      <MealPlansContainer.Provider
        initialState={{
          global,
        }}
      >
        <Plans/>
        <Modals/>
      </MealPlansContainer.Provider>
    </ModalContainer.Provider>
  );
}

App.defaultProps = {
  global: {},
};

App.propTypes = {
  global: PropTypes.object,
};
