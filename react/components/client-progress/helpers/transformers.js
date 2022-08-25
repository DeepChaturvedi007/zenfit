import _ from 'lodash';
import moment from 'moment';
import {roundToDecimals} from './utils';

const sortByDateDesc = function(a, b) {
  const timeA = (new Date(a.date)).getTime();
  const timeB = (new Date(b.date)).getTime();
  if (timeA > timeB) {
    return -1;
  }
  if (timeA < timeB) {
    return 1;
  }
  return 0;
};

const sortByDateAsc = function(a, b) {
  const timeA = (new Date(a.date)).getTime();
  const timeB = (new Date(b.date)).getTime();
  if (timeA < timeB) {
    return -1;
  }
  if (timeA > timeB) {
    return 1;
  }
  return 0;
};

const sumReducer = (acc, cur) => Number(acc) + Number(cur);

// Extract list of items from array and calculate avg of them
// IMPORTANT! values under provided keys should be numeric
const extractAvgDataFor = (dataArray, key) => {
  if(!dataArray.length) return 0;
  const sum = dataArray
    .map(item => item[key])
    .reduce(sumReducer);
  return sum / dataArray.length
};

// Recursive build array of weeks with theirs data
// IMPORTANT! Data's elements should have 'date' field
const recursiveBuild = (data = [], to = new Date()) => {
  const from = new Date(to);
  from.setDate(to.getDate() - 7);
  const suitable = data.filter((item) => {
    const { date } = item;
    const iterable = new Date(date);
    return from.getTime() < iterable.getTime() && iterable.getTime() < to.getTime();
  });
  const rest = data.filter((item) => {
    const { date } = item;
    const iterable = new Date(date);
    return iterable.getTime() < from.getTime();
  });

  let result = [];
  if(!rest.length && !suitable.length) {
    return result;
  }
  result.push(suitable);
  if(rest.length) {
    const previous = recursiveBuild(rest, from);
    result = result.concat(previous)
  }
  return result;
};
// Creates array of weeks which contain theirs data.
// IMPORTANT! Data's elements should have 'date' field
const buildWeeksByInnerDate = (data = []) => {
  // Recursive build array of weeks with the dates
  const today = new Date();
  const builtData = recursiveBuild(data, today);
  return builtData.reverse();
};

// Transform data object for the ~/components/KcalTrackingSection/index.js usage
export const buildKCalData = (data) => {
  const { macros: pool = [], meals: mealsPool = [] } = data;
  const arrayByWeeks = buildWeeksByInnerDate(pool);
  const latestWeek = [...arrayByWeeks].pop() || [];

  const avgDataForLastWeek = {
    kcal: extractAvgDataFor(latestWeek, 'kcal'),
    carbs: extractAvgDataFor(latestWeek, 'carbs'),
    protein: extractAvgDataFor(latestWeek, 'protein'),
    fat: extractAvgDataFor(latestWeek, 'fat'),
  };

  const planInfo = mealsPool.sort((a, b) => {
      let dateA = new Date(a.created);
      let dateB = new Date(b.created);
      return dateA - dateB;
    })
    .filter(({active}) => active)
    .map(meal => {
      const {
        name,
        active,
        created,
        last_updated,
        meta,
        desired_kcals,
      } = meal;

      return {
        meta,
        name,
        active,
        createdAt: created,
        updatedAt: last_updated,
        goal: desired_kcals
      }
    })
    .pop();

  const goal = planInfo ? planInfo.goal : 0;
  const yearlySummary = [...arrayByWeeks]
    .reverse()
    .map((data, i) => {
      return {
        week: moment().week() - i,
        kcal: extractAvgDataFor(data, 'kcal'),
        carbs: extractAvgDataFor(data, 'carbs'),
        protein: extractAvgDataFor(data, 'protein'),
        fat: extractAvgDataFor(data, 'fat'),
      }
    });
  return {
    planInfo,
    general: {
      avg: avgDataForLastWeek.kcal,
      goal,
      diff: roundToDecimals(avgDataForLastWeek.kcal - goal, 0)
    },
    weeklySummary: {
      items: latestWeek.sort(),
      avg: avgDataForLastWeek
    },
    yearlySummary
  };
};
