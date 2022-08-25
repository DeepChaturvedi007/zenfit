import axios from 'axios';

const client = axios.create({
  baseURL: '/'
});

export const CancelToken = axios.CancelToken;
export const isCancel = axios.isCancel;

/**
 * @param {number} clientId
 *
 * @returns {Promise<Object>}
 */
export function fetchPlans(clientId) {
  return client.get(`/api/v3/meal/client/${clientId}`, {
    headers: {
      'Cache-Control': 'no-cache',
    },
  });
}

/**
 * @param {Object} params
 * @param {?Object} options
 *
 * @returns {Promise<Object>}
 */
export function fetchRecipes(params, options = {}) {
  return client.get('/api/recipe/get-recipes', {
    params,
    ...options,
  });
}

/**
 * @param {FormData} params
 * @param {?Object} options
 *
 * @returns {Promise<Object>}
 */
export function updateRecipePreference(params, options = {}) {
  return client.post('/api/recipe/update-user-recipe-preferences', params, {
    ...options,
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
}

/**
 * @param {number} planId
 * @param {Object} data
 * @param {?Object} options
 *
 * @returns {Promise<Object>}
 */
export function updatePlan(planId, data, options = {}) {
  return client.post(`/api/v3/meal/save/settings/${planId}`, data, options);
}

/**
 * @param {*} params
 *
 * @returns {Promise<Object>}
 */
export function deletePlan(params) {
  return client.delete('/api/v3/meal/plan/delete', { params });
}

/**
 * @param {*} params
 *
 * @returns {Promise<Object>}
 */
export function clonePlan(planId, planName, clientId) {
  let bodyData = new FormData();
  bodyData.append('plan', planId);
  bodyData.append('name', planName);
  bodyData.append('client', clientId);
  return client.post(`/meal/`, bodyData);
}

/**
 * @param {Object} data
 * @param {?Object} options
 *
 * @returns {Promise<Object>}
 */
export function addMeal(data, options = {}) {
  return client.post('/api/v3/meal', data, options);
}

/**
 * @param {Object} data
 * @param {?Object} options
 *
 * @returns {Promise<Object>}
 */
export function updateMeal(data, options = {}) {
  return client.put('/api/v3/meal', data, options);
}

/**
 * @param {Object} params
 *
 * @returns {Promise<Object>}
 */
export function deleteMeal(params) {
  return client.delete('/api/v3/meal', { params });
}

/**
 * @param {Object} plan
 * @param {?Object} options
 *
 * @return {Promise<any>}
 */
export function fetchPdf(plan, options = {}) {
  return client.get(`/pdf/exportPlansPdfMealClient/${plan.id}`, {
    params: {
      name: `${plan.client_name}-mealplan`,
      ...(options.params || {}),
      version: 2,
    },
    ...options,
  });
}

/**
 * @param {number} planId
 * @param {object} data
 * @param {?object} options
 *
 * @return {Promise<any>}
 */
export function syncMealPlan(planId, data, options = {}) {
  return client.post(`/api/v3/meal/plan/reorder/${planId}`, { data }, options);
}

/**
 * @param {number} type
 *
 * @return {Promise<any>}
 */
export function fetchDefaultMessages(type, clientId) {
  return client.get(`/api/trainer/get-default-messages/${type}/${clientId}`);
}

/**
 * @param {number} planId
 * @param {object} data
 * @param {?object} options
 *
 * @return {Promise<any>}
 */
export function updatePercentWeights(planId, data, options = {}) {
  return client.post(`/api/v3/meal/plan/${planId}/percent-weights`, data, options);
}

/**
 * @param {Object} data
 * @param {?Object} options
 *
 * @returns {Promise<Object>}
 */
export function submitDefaultMessage(data, options = {}) {
  return client.post('/api/trainer/set-default-message', data, options);
}

/**
 * @param planId
 * @param {Object} data
 * @param {?Object} options
 *
 * @returns {Promise<Object>}
 */
export function addParent(planId, data, options = {}) {
  return client.post(`/api/v3/meal/plan/${planId}/add-parent`, data, options);
}
