/**
 * @param {Object} data
 * @return {Object}
 */
export function exerciseTransformer(data) {
  return {
    id: undefined,
    comment: null,
    time: 0,
    reps: 0,
    rest: 0,
    sets: 0,
    startWeight: null,
    type: data.workoutType || null,
    exercise: {
      id: data.id,
      name: data.name,
      description: data.description,
      picture: data.picture_url,
      video: data.video_url,
      muscle: data.muscleGroup || null,
      type: data.exerciseType || null,
      equipment: data.equipment || null,
    },
    supers: []
  };
}

/**
 * @param {Object} data
 * @return {Object}
 */
export function productTransformer(data) {
  return {
    id: null,
    name: null,
    comment: null,
    order: 0,
    product: {
      id: data.id,
      brand: data.brand,
      name: data.name,
      kcal: data.kcal,
      protein: data.protein,
      fat: data.fat,
      carbohydrates: data.carbohydrates,
      recommended: data.recommended
    },
    weights: Array.isArray(data.weights) ? data.weights : []
  };
}


const URL_PROTOCOL_REPLACER = /^(https?:)/i;

/**
 * @param {string} s3
 * @param {string} picture
 * @param {*?} type
 * @param {*?} size
 * @return {string}
 */
export function pictureFilter(s3, picture, type = null, size = null) {
  if (!picture || /^https?/i.test(picture)) {
    return picture ? picture.replace(URL_PROTOCOL_REPLACER, window.location.protocol) : picture;
  }

  let source = s3;

  if (type) {
    source += `${type}/`;
  }

  if (size) {
    source += `${size}/`;
  }

  return source + picture;
}

export const percent = (value, max) => Math.floor((value / max) * 100);
