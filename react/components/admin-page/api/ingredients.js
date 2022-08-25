import _ from 'lodash';
import axios from 'axios';

const objToFormData = (obj) => {
  const formData = new FormData();
  const aggregate = (key, value) => {
    formData.append(key, value);
  };
  Object
    .keys(obj)
    .filter(key => !obj[key])
    .forEach(key => {
      delete obj[key];
    });

  transform(null, obj, aggregate);

  return formData;
};

const transform = (key, value, aggregate) => {
  if(value instanceof File) {
    return aggregate(key, value)
  }
  if(_.isArray(value)) {
    value.forEach((v, i) => {
      transform(`${key}[${i}]`, v, aggregate)
    })
  } else if (_.isPlainObject(value)) {
    Object
      .keys(value)
      .forEach((k) => {
        if(key) {
          transform(`${key}[${k}]`, value[k], aggregate)
        } else {
          transform(k, value[k], aggregate)
        }
      })
  } else {
    return aggregate(key, value)
  }
};

const onSuccess = response => {
  return response.data;
};

const onError = err => {
  console.error(err);
  throw err
};

export const createIngredient = (data) => {
  const url = `/admin/api/ingredients`;
  return axios.post(url, objToFormData(data), {
    withCredentials: "include",
  })
    .then(onSuccess)
    .catch(onError)
};

export const updateIngredient = (data) => {
  const id = data.id
  const url = `/admin/api/ingredients/update/${id}`;
  return axios.post(url, objToFormData(data), {
    withCredentials: "include",
  })
  .then(onSuccess)
  .catch(onError)
};

export const deleteIngredient = (id) => {
  const url = `/admin/api/ingredients/delete/${id}`;
  return axios.post(url, objToFormData(id), {
    withCredentials: "include",
  })
  .then(onSuccess)
  .catch(onError)
};

export const fetchIngredients = (query) => {
  const url = `/admin/api/ingredients`;
  return axios.get(url, {params: query})
    .then(onSuccess)
    .then(obj => Object.values(obj))
    .catch(onError);
};