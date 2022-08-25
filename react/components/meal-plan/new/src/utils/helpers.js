export const timeout = (ms) => new Promise(resolve => setTimeout(resolve, ms));

/**
 * @param {Object} data
 *
 * @returns {{ plan: Object, meals: Object }}
 */
export function mealPlanNormalizer(data) {
  const { meal_plans, ...plan } = data;
  const meals = Object.values(meal_plans).reduce((collection, meal) => {
    collection[meal.id] = { ...meal, planId: data.id };
    return collection;
  }, {});

  return { plan, meals };
}

/**
 * @param {Array<Object>} data
 *
 * @returns {{plans: Object<number, Object>, meals: Object<number, Object>}}
 */
export function mealPlansNormalizer(data) {
  return data
    .reduce((collection, plan) => {
      const data = mealPlanNormalizer(plan);

      collection.plans[plan.id] = data.plan;
      collection.meals = { ...collection.meals, ...data.meals };

      return collection;
    }, {
      plans: {},
      meals: {},
    });
}

/**
 * @param {string} message
 * @param {Object<string, *>} placeholders
 * @param {boolean} toActualValues
 *
 * @returns {string}
 */
export function transformDefaultMessage(message, placeholders, toActualValues = true) {
  if (message) {
    let results = message;

    Object.keys(placeholders).forEach(placeholder => {
      for (let i = 0; i < 2; i++) {
        results = toActualValues
          ? results.replace(placeholders[placeholder], `[${placeholder}]`)
          : results.replace(`[${placeholder}]`, placeholders[placeholder]);
      }
    });

    return results;
  }

  return "";
}

/**
 * Clamps the given number between min and max values. Returns value if within
 * range, or closest bound.
 */
/**
 * @param {number} value
 * @param {number} min
 * @param {number} max
 * @returns {number}
 */
export function clamp(value, min, max) {
  if (value == null) {
    return value;
  }
  if (max < min) {
    throw new Error('clamp: max cannot be less than min');
  }
  return Math.min(Math.max(value, min), max);
}

/**
 * @param {*} obj
 * @returns {boolean}
 */
export const isCallable = (obj) => typeof obj === 'function';
