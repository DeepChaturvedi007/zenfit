import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import pageEn from 'js-yaml-loader!./../../../../translations/survey/page.en.yml';
import pageDa from 'js-yaml-loader!./../../../../translations/survey/page.da.yml';
import pageNb from 'js-yaml-loader!./../../../../translations/survey/page.nb.yml';
import pageSv from 'js-yaml-loader!./../../../../translations/survey/page.sv.yml';

import messagesEn from 'js-yaml-loader!./../../../../translations/survey/messages/en.yml';
import messagesDa from 'js-yaml-loader!./../../../../translations/survey/messages/da.yml';
import messagesNb from 'js-yaml-loader!./../../../../translations/survey/messages/nb.yml';
import messagesSv from 'js-yaml-loader!./../../../../translations/survey/messages/sv.yml';

const splitPlurals = (object) => {
  const newObject = {};
  Object.keys(object).forEach((key) => {
    let elem = object[key];
    if (typeof elem === 'object') {
      newObject[key] = splitPlurals(elem);
      return;
    }
    // replace all symfony parameters %param% with {{param}} in all strings for js
    elem = String(elem).replace(/%([^%]+(?=%))%/gi, '{{$1}}');

    // splits all plurales like "one apple|many apples" into different keys apple and apple_plural
    if (typeof elem === 'string' && elem.includes('|')) {
      const plural = elem.split('|');
      newObject[key] = plural.shift();
      newObject[`${key}_plural`] = plural.shift();

      return;
    }

    newObject[key] = elem;
  });

  return newObject;
};

const resources = {
  en: {
    main: splitPlurals(pageEn),
    messages: splitPlurals(messagesEn),
  },
  da_DK: {
    main: splitPlurals(pageDa),
    messages: splitPlurals(messagesDa),
  },
  nb_NO: {
    main: splitPlurals(pageNb),
    messages: splitPlurals(messagesNb),
  },
  sv_SE: {
    main: splitPlurals(pageSv),
    messages: splitPlurals(messagesSv),
  }
};

const initTranslator = () => {
  i18n
    .use(initReactI18next)
    .init({
      resources,
      lng: (window && window.locale) || 'en',
      fallbackLng: 'en',
      keySeparator: '.', // keySeparator: false, // don't count "." as separator
      interpolation: {
        escapeValue: false // react already safes from xss
      }

    });
}

export default initTranslator;
