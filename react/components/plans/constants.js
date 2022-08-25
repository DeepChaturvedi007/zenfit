export const TYPE_WORKOUT = 'workout';
export const TYPE_MEAL = 'meal';
export const TYPE_RECIPE = 'recipe';
export const LOCALE_KEY = 'plans_locale';

export const IS_TOUCH = (('ontouchstart' in window)
|| (navigator.maxTouchPoints > 0)
|| (navigator.msMaxTouchPoints > 0));

export const IS_FIREFOX = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
export const DROP_SET_TEXT = 'This is a dropset. Drop the set 1 time.';
export const DROP_SET_RE = /^this is a dropset/i;