
import axios from 'axios';

export const saveData = (data = {}, token) => {
  const formData = objToFormData(data);

  return axios.post('/zapier/client', formData, {
    headers: {
      'Authorization': token,
    }
  })
    .then(response => {
      if(response.data.redirect) {
        window.location.replace(response.data.redirect);
      }
      return response;
    })
};


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
    value.forEach((v) => {
      transform(`${key}[]`, v, aggregate)
    })
  } else if (_.isObject(value)) {
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
