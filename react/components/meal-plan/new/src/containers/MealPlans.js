// eslint-disable-next-line
import React, { useState, useEffect } from 'react';
import debounce from "lodash/debounce";
import findIndex from "lodash/findIndex";
import memoize from "lodash/memoize";
import omitBy from "lodash/omitBy";
import isObject from "lodash/isObject";
import filter from "lodash/filter";
import update from "react-addons-update";
import ModalContainer from "./Modal";
import ActivityTypes from "../constants/ActivityTypes";
import RequestTypes from "../constants/RequestTypes";
import { createContainer } from "../utils/unstated";
import { mealPlansNormalizer, mealPlanNormalizer } from "../utils/helpers";
import * as api from "../utils/api";

const requests = new Map();

function useMealPlans(initialState = {}) {
  const modal = ModalContainer.useContainer();
  const global = initialState.global || {};
  const { reloadForUpdate } = global;
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState({
    plans: {},
    meals: {},
    sync: null,
  });
  const [reloadEvent, setReloadEvent] = useState(true);

  const setPlanLoading = (planId, loading = false) => {
    setData(update(data, {
      plans: {
        [planId]: {
          '$merge': {
            loading
          }
        },
      }
    }));
  };

  useEffect(() => {
    const fetch = async () => {
      requests.set(RequestTypes.PLANS_FETCH, api.CancelToken.source());

      setLoading(true);

      try {
        const response = await api.fetchPlans(global.clientId);
        const { meals, plans } = mealPlansNormalizer(response.data);

        setData(update(data, {
          plans: { $merge: plans },
          meals: { $merge: meals },
        }));
      } catch (e) {
        console.error('fetch', e);
      }

      setLoading(false);
      requests.delete(RequestTypes.PLANS_FETCH);
    };

    fetch();

    return () => {
      for (let source of requests.values()) {
        source.cancel('Operation canceled by the user.');
      }

      requests.clear();
    }
  }, [reloadForUpdate, reloadEvent]);

  useEffect(() => {

  }, [data.sync])
  /**
   * @param {number} planId
   *
   * @return {Object}
   */
  const planById = memoize((planId) => data.plans[planId]);

  /**
   * @param {number} planId
   *
   * @return {Array<Object>}
   */
  const mealsByPlan = memoize((planId) => filter(data.meals, { planId }));

  /**
   * @param {number} mealId
   *
   * @return {Array<Object>}
   */
  const dishesByMeal = memoize((mealId) => data.meals[mealId].meals);

  /**
   * @param {number} mealId
   * @returns {boolean}
   */
  const isMealSyncing = (mealId) => (data.sync && data.sync.mealId === mealId);

  /**
   * @param {Object} props
   */
  const setSync = (props) => setData(
    update(data, {
      sync: { $set: props },
    })
  );

  /**
   * @param {number} planId
   *
   * @type {Array<Object>}
   */
  const progressWeightsByPlan = (planId) => {
    return mealsByPlan(planId).map(meal => ({
      id: meal.id,
      name: meal.name,
      value: meal.percent_weight,
      kcalsPerPercent: meal.ideal_totals.kcal / meal.percent_weight / 100
    }))
  };

  /**
   * @param {number} mealId
   * @param {number} recipeId
   *
   * @return {boolean}
   */
  const containsRecipe = (mealId, recipeId) => {
    return dishesByMeal(mealId).some(meal => meal.recipe === recipeId)
  };

  /**
   * @param {number} planId
   * @param {?number} mealId
   *
   * @returns {string}
   */
  const viewMealPlan = (planId, mealId) => `/meal/clients/${global.clientId}/plan/${planId}${mealId ? `/${mealId}` : ''}`

  /**
   * @param {number} planId
   * @param {Object} formData
   * @param {?boolean} syncPlans
   *
   * @returns {Promise<void>}
   */
  const updateMealPlan = async (planId, formData, syncPlans = false) => {
    requests.set(RequestTypes.PLANS_UPDATE, api.CancelToken.source());

    try {
      const response = await api.updatePlan(planId, formData, {
        cancelToken: requests.get(RequestTypes.PLANS_UPDATE).token,
      });

      const { plan } = mealPlanNormalizer(response.data);
      const spec = {
        plans: {
          [plan.id]: { $merge: plan },
        },
      };

      if (syncPlans) {
        spec.sync = {
          $set: { planId: planId },
        };
      }

      setData(update(data, spec));
    } catch (e) {
      console.error('updateMealPlan', e);
      throw e;
    }

    requests.delete(RequestTypes.PLANS_UPDATE);
  };

  /**
   * @param {number} planId
   * @param {Object} formData
   * @param {?boolean} syncPlans
   *
   * @returns {Promise<void>}
   */
  const addParent = async (planId, formData, syncPlans = false) => {
    setPlanLoading(planId, true);
    requests.set(RequestTypes.PLANS_ADD_PARENT, api.CancelToken.source());

    try {
      const response = await api.addParent(planId, formData, {
        cancelToken: requests.get(RequestTypes.PLANS_ADD_PARENT).token,
      });

      const { plan } = mealPlanNormalizer(response.data);
      const spec = {
        plans: {
          [plan.id]: { $merge: { ...plan, loading: syncPlans } },
        },
      };

      if (syncPlans) {
        spec.sync = {
          $set: { planId: planId },
        };
      }

      setData(update(data, spec));
    } catch (e) {
      console.error('addParent', e);
      throw e;
    } finally {
      if (!syncPlans) {
        setPlanLoading(planId, false);
      }
    }

    requests.delete(RequestTypes.PLANS_ADD_PARENT);
  };

  /**
   * @param {number} planId
   * @param {string} planName
   *
   * @returns {Promise<void>}
   */
  const cloneMealPlan = async (planId, planName) => {
    requests.set(RequestTypes.PLANS_CLONE, api.CancelToken.source());

    try {
      await api.clonePlan(planId, planName, global.clientId);
      
      setReloadEvent((prev) => !prev);
    } catch (e) {
      console.error('cloneMealPlan', e);
    }

    requests.delete(RequestTypes.PLANS_CLONE);
  };

  /**
 * @param {number} planId
 *
 * @returns {Promise<void>}
 */
  const deleteMealPlan = async (planId) => {
    requests.set(RequestTypes.PLANS_DELETE, api.CancelToken.source());

    try {
      await api.deletePlan({ plan: planId });

      setData(
        update(data, {
          plans: { $apply: (plans) => omitBy(plans, plan => plan.id === planId) },
          meals: { $apply: (meals) => omitBy(meals, meal => meal.planId === planId) },
        })
      );
    } catch (e) {
      console.error('deleteMealPlan', e);
    }

    requests.delete(RequestTypes.PLANS_DELETE);
  };

  /**
   * @param {number} planId
   */
  const syncMeals = async (planId, mealId, meals) => {
    requests.set(RequestTypes.MEALS_SYNC, api.CancelToken.source());
    const results = mealsByPlan(planId).map((meal, mealIndex) => {
      const dishes = meal.id === mealId ?
        meals :
        meal.meals.map((dish, dishIndex) => ({
          id: dish.id,
          order: dishIndex + 1,
          parent: meal.id,
        }));

      return {
        id: meal.id,
        order: mealIndex + 1,
        meals: dishes,
      };
    });

    let result;
    try {
      const response = await api.syncMealPlan(planId, { results });
      const { plan, meals } = mealPlanNormalizer(response.data.result);
      setData(update(data, {
        plans: {
          [plan.id]: { $set: plan },
        },
        meals: { $merge: meals },
        sync: { $set: null },
      }));

      if (response.data.error) {
        window.toastr.error(response.data.error);
      }

      result = [null, response];
    } catch (err) {
      result = [err, null];
      setSync(null);
      console.error('syncMeals', err);
    }

    // modal.setActivityProps({ result });
    requests.delete(RequestTypes.MEALS_SYNC);
    return result;
  };

  /**
   * @param {meal} meal
   * @param {Object} response
   */
  const updateMacroSplit = (meal, response) => {
    const { meals, plans } = mealPlansNormalizer(response.data);

    setData(update(data, {
      plans: {
        [meal.planId]: { $merge: plans[meal.planId] },
      },
      meals: {
        [meal.id]: { $merge: meals[meal.id] },
      },
    }));
  };

  /**
   * @param {Object} recipe
   * @param {number} mealId
   * @param {?number} dishId
   *
   * @returns {Promise<Array>}
   */
  const addDish = async (recipe, mealId, dishId = null) => {
    let result;
    let activityProps = { recipe, mealId, dishId };
    // modal.setActivity(ActivityTypes.MEAL_RECIPE, activityProps);
    requests.set(RequestTypes.MEALS_SUBMIT, api.CancelToken.source());

    try {
      const meal = data.meals[mealId];
      const response = await api.addMeal({
        client: global.clientId,
        recipe: recipe.id,
        type: meal.type,
        plan: meal.planId,
        parent: meal.id,
        replaceMeal: dishId,
      }, {
        cancelToken: requests.get(RequestTypes.MEALS_SUBMIT).token,
      });

      const { meals, plans } = mealPlansNormalizer(response.data);

      setData(update(data, {
        plans: {
          [meal.planId]: { $merge: plans[meal.planId] },
        },
        meals: {
          [meal.id]: { $merge: meals[meal.id] },
        },
      }));

      result = [null, response];
    } catch (err) {
      console.error('addDish', err);
      result = [err, null];
    }
    if (modal.selectedRecipe.length === 0) {
      modal.setActivity(ActivityTypes.MEAL_RECIPE, { ...activityProps, result });
    }
    requests.delete(RequestTypes.MEALS_SUBMIT);

    return result;
  };

  /**
   * @param {number} planId
   * @param meals
   *
   * @returns {Promise<void>}
   */
  const updateProgressWeights = async (planId, meals) => {
    try {
      const response = await api.updatePercentWeights(planId, { meals });

      if (isObject(response.data)) {
        const responseData = mealPlanNormalizer(response.data);

        setData(update(data, {
          meals: { $merge: responseData.meals },
        }));
      }
    } catch (e) {
      console.error('updateProgressWeight', e);
    }
  };

  /**
   * @param {Object} result
   */
  const onDishDragEnd = (result) => {
    const { destination, source } = result;

    if (!destination) {
      return;
    }

    if (
      destination.droppableId === source.droppableId &&
      destination.index === source.index
    ) {
      return;
    }

    const meal = data.meals[destination.droppableId];
    const dish = { ...data.meals[source.droppableId].meals[source.index] };

    let meals;

    if (source.droppableId === destination.droppableId) {
      meals = {
        [source.droppableId]: {
          meals: {
            $splice: [[source.index, 1], [destination.index, 0, dish]],
          },
        },
      };
    } else {
      meals = {
        [source.droppableId]: {
          meals: {
            $splice: [[source.index, 1]],
          },
        },
        [destination.droppableId]: {
          meals: {
            $splice: [[destination.index, 0, dish]],
          },
        },
      };
    }

    setData(update(data, {
      meals,
      sync: {
        $set: { planId: meal.planId, mealId: destination.droppableId },
      },
    }));
  };

  /**
   * @param {number} mealId
   * @param {number|Function} column
   */
  const onRemoveDish = (mealId, column) => {
    let index = column;
    if (typeof index === 'function') {
      index = findIndex(data.meals[mealId].meals, column);
    }
    const meal = data.meals[mealId];
    const mealList = meal.meals;
    mealList.splice(index, 1);
    return syncMeals(meal.planId, mealId, mealList).then(res => {
      return res;
    });
  };

  /**
   * @param {Object} postData
   *
   * @returns {Promise<Object>}
   */
  const submitDefaultMessage = async (postData) => {
    try {
      const response = await api.submitDefaultMessage({
        ...postData,
        type: 3,
      });

      return response.data.data;
    } catch (e) {
      console.error('submitMessage', e);
    }
  };

  useEffect(() => {
    if (data.sync && data.sync.planId) {
      syncMeals(data.sync.planId);
    }
  }, [data.sync]);

  return {
    data,
    loading,
    viewMealPlan,
    updateMealPlan,
    deleteMealPlan,
    cloneMealPlan,
    syncMeals,
    updateMacroSplit,
    updateProgressWeights,
    onDishDragEnd,
    onRemoveDish,
    addDish,
    submitDefaultMessage,
    planById,
    mealsByPlan,
    progressWeightsByPlan,
    containsRecipe,
    isMealSyncing,
    setSync,
    addParent
  };
}

const MealPlansContainer = createContainer(useMealPlans);

export default MealPlansContainer;
