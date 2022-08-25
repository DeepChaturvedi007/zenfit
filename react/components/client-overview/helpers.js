import moment from 'moment';
import _ from 'lodash';
import { GENDER, LBS_TO_KG, FEET_TO_CM, GOAL_TYPE } from './const';
import {GetKGFromLBS} from "../../shared/helper/measurementHelper";

export function getWeeks(client, showWeeks = false, isPending = false) {
  let startDate = client.startDate ? moment(client.startDate.date) : moment(client.createdAt.date);
  let now = moment.now();
  let diff = Math.abs(startDate.diff(now, 'weeks')) + 1;

  if (startDate.isAfter(now)) {
    return `Starts ${startDate.format('MMM DD')}`;
  }

  //this is only for the pending filter
  if (isPending) {
    let daysAgo = Math.abs(startDate.diff(now, 'days'));
    if (daysAgo == 0) {
      return 'Today';
    } else if (daysAgo == 1) {
      return 'Yesterday';
    } else {
      return `${daysAgo} days ago`;
    }
  }

  if (!client.endDate) {
    if (showWeeks) {
      return `Week ${diff}`;
    }
    return `${diff}`;
  }

  //there is an end date
  let endDate = moment(client.endDate.date);
  // let totalWeeks = endDate.diff(startDate, 'weeks');
  let totalWeeks = client.duration * 4;

  if (endDate.isBefore(now)) {
    diff = Math.abs(endDate.diff(now, 'weeks'));
    return `Ended ${diff} weeks ago`;
  }

  if (showWeeks) {
    return `Week ${diff} of ${totalWeeks}`;
  }

  return `${diff} of ${totalWeeks}`;
}

const valWithSign = value => {
  return value < 0 ? value : value === 0 ? "" : "+" + value;
}

export const valWithUnit = (measuringSystem, value, withSign = false) => {
  let unit = measuringSystem === 1 ? ' kg' : ' lbs';
  return withSign ? valWithSign(value) + unit : value + unit;
}

export const computeKcals = (gender, weight, height, age, pal, goalType, gramsPerWeek, measureSystem) => {
  let bmr = 0;
  let kcals = 0;
  const KgWeight = measureSystem == 2 ? GetKGFromLBS(weight) : weight;

  if (GENDER[gender] === 'male') {
    bmr = Math.round(88.362 + (13.397 * KgWeight) + (4.799 * height) - (5.677 * age));
  } else {
    bmr = Math.round(447.593 + (9.247 * KgWeight) + (3.098 * height) - (4.330 * age));
  }

  const tdee = Math.round(bmr * pal);
  if (GOAL_TYPE[goalType] === 'Lose Weight') {
    kcals = Math.round(tdee - gramsPerWeek * 1.11);
  } else {
    kcals = Math.round(tdee + gramsPerWeek * 1.11);
  }

  return {
    bmr,
    tdee,
    kcals
  }
}

export const mergeValues = (fields, clientInfo) => {
  return Object.values(fields).map(item => {
    item.value = _.get(clientInfo, item.key);
  });
}

export const updateObject = (object, path, value) => {
  return _.set(object, path, value);
}

export const prepareOptions = options => {
  return Object.keys(options).map(key => {
    return { value: key, label: _.upperFirst(options[key]) }
  })
}

export const prepareMultiOptions = options => {
  let uniqueOptions = options;
  if (Array.isArray(options)) {
    uniqueOptions = _.uniq(options);
  }

  return Object.keys(uniqueOptions).map(key => ({
    value: uniqueOptions[key],
    label: _.capitalize(uniqueOptions[key]),
  }))
}

export const prepareMultiOptionsFoodPref = (options, optionsList = null) => {
  if (optionsList === null) {
    return Object.keys(options).map(key => ({
      value: key,
      label: _.startCase(options[key]),
    }))
  } else {
    return Object.values(options).map(option => ({
      value: option,
      label: _.startCase(optionsList[option]),
    }))
  }
}

export const prepareMeasuringValue = (value, measuringSystem, type) => {
  if (measuringSystem == 2) {
    if (type === 'weight') {
      value = Math.round(value / LBS_TO_KG * 100) / 100;
    } else {
      value = Math.round(value / FEET_TO_CM * 100) / 100;
    }
  } else {
    if (type === 'weight') {
      value = Math.round(value * LBS_TO_KG * 100) / 100;
    } else {
      value = Math.round(value * FEET_TO_CM * 100) / 100;
    }
  }

  return value;
}
