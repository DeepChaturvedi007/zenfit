export const GET_MEAL_TEMPLATES = () => {
  return `/dashboard/mealTemplates/templates`;
};

export const GET_MEAL_TEMPLATE = plan => {
  return `/dashboard/mealTemplates/edit/${plan}`;
};

export const UPDATE_MEAL_TEMPLATE = plan => {
  return `/dashboard/mealTemplates/update/${plan}`;
};

export const DELETE_MEAL_TEMPLATE = plan => {
  return `/dashboard/deleteMealTemplate/${plan}`;
};

export const DUPLICATE_MEAL_TEMPLATE = plan => {
  return `/dashboard/mealTemplates/duplicateTemplate/${plan}`;
};

export const GET_CLIENT_MEAL = clientId => {
  return `/react-api/fetchMealPlan?client=${clientId}`;
};

export const APPLY_MEAL_TEMPLATE_TO_CLIENTS = (templateId) => {
  return `/api/meal/assign-meal-template/${templateId}`;
};

export const DELETE_MEAL_PLAN = plan => {
  return `/dashboard/deleteClientMealPlan/${plan}`;
};

export const GET_MEAL_PLAN = plan => {
  return `/dashboard/client/mealPlan/${plan}`;
};

export const CREATE_MEAL_PLAN = () => {
  return `/dashboard/createMealPlan`;
};


export const GET_MEAL_INGREDIENTS = () => {
  return `/api/recipe/get-ingredients`;
};
