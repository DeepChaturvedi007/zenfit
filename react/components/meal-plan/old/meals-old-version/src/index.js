import "es7-object-polyfill";
import React from "react";
import PropTypes from "prop-types";
import ReactDOM from "react-dom";
import MealPlansContainer from './containers/MealPlans';
import ModalContainer from './containers/Modal';
import Plans from './modules/Plans';
import Modals from './modules/Modals';

function App({ global }) {
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

const root = document.querySelector('[data-react="meal-plans"]');

if (root) {
  ReactDOM.render(<App global={root.dataset} />, root);
}
