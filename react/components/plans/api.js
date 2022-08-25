import qs from 'qs';
import { create } from 'apisauce';
import {
    SAVE_WORKOUT_PLAN,
    GET_WORKOUT_PLAN_DAYS,
} from '../../api/workout-api';

const api = create({
  baseURL: '/',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Cache-Control': 'no-cache'
  }
});

api.updateWorkoutPlan = function saveWorkoutPlan(planId, data) {
  return api.post(`/workout/${planId}/update`, qs.stringify(data), {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
  });
};

api.saveWorkout = function saveWorkout(planId, data) {
  return api.post(SAVE_WORKOUT_PLAN(planId), data);
};

api.getWorkout = function getWorkout(planId) {
  return api.get(GET_WORKOUT_PLAN_DAYS(planId));
};

api.saveMeals = function saveMeals(planId, data, locale) {
  return api.post(`/api/meal/save/${planId}`, {data, locale});
};

api.getMeals = function getMeals(planId, locale = 'en', meal = null) {
  return api.get(`/api/meal/plans/${planId}`, { locale, meal }, {
    headers: {
      'Cache-Control': 'no-cache'
    }
  });
};

api.getRecipe = function getRecipe(recipeId) {
  return api.get(`/api/meal/recipes/${recipeId}`);
};

api.syncRecipe = function syncRecipe(recipeId, data, locale) {
  return api.post(`/api/meal/recipes/${recipeId}/sync`, { locale, data });
};

api.getEquipments = function() {
  return api.get('/api/exercises/equipment');
};

api.getMuscles = function() {
  return api.get('/api/exercises/muscle-groups');
};

api.getExercises = function(data = {}) {
  return api.get('/api/exercises', data);
};

api.getMealProducts = function(data = {}) {
  return api.get('/internal-api/products', data);
};

api.searchYoutube = function(q, key, maxResults = 5, part = 'snippet', params = {}) {
  const queryParams = {
    q,
    key,
    maxResults,
    part,
  };

  if (params.nextPageToken) {
    queryParams.pageToken = params.nextPageToken;
  } else if (params.prevPageToken) {
    queryParams.pageToken = params.prevPageToken;
  }

  return api.get('https://content.googleapis.com/youtube/v3/search', queryParams);
};

export default api;
