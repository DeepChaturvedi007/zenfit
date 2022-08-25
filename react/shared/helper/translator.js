import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import pageEn from 'js-yaml-loader!./../../../translations/survey/page.en.yml';
import pageDa from 'js-yaml-loader!./../../../translations/survey/page.da.yml';
import pageNb from 'js-yaml-loader!./../../../translations/survey/page.nb.yml';
import pageSv from 'js-yaml-loader!./../../../translations/survey/page.sv.yml';

import messagesEn from 'js-yaml-loader!./../../../translations/survey/messages/en.yml';
import messagesDa from 'js-yaml-loader!./../../../translations/survey/messages/da.yml';
import messagesNb from 'js-yaml-loader!./../../../translations/survey/messages/nb.yml';
import messagesSv from 'js-yaml-loader!./../../../translations/survey/messages/sv.yml';

import GlobalMessagesEn from 'js-yaml-loader!./../../../translations/messages.en.yml'
import GlobalMessagesDa from 'js-yaml-loader!./../../../translations/messages.da.yml'
import GlobalMessagesNb from 'js-yaml-loader!./../../../translations/messages.nb.yml'
import GlobalMessagesFi from 'js-yaml-loader!./../../../translations/messages.fi.yml'
import GlobalMessagesDe from 'js-yaml-loader!./../../../translations/messages.de.yml'
import GlobalMessagesNl from 'js-yaml-loader!./../../../translations/messages.nl.yml'
import GlobalMessagesSv from 'js-yaml-loader!./../../../translations/messages.sv.yml'

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
    globalMessages: splitPlurals(GlobalMessagesEn)
  },
  da_DK: {
    main: splitPlurals(pageDa),
    messages: splitPlurals(messagesDa),
    globalMessages: splitPlurals(GlobalMessagesDa)
  },
  nb_NO: {
    main: splitPlurals(pageNb),
    messages: splitPlurals(messagesNb),
    globalMessages: splitPlurals(GlobalMessagesNb)
  },
  sv_SE: {
    main: splitPlurals(pageSv),
    messages: splitPlurals(messagesSv),
    globalMessages: splitPlurals(GlobalMessagesSv)
  },
  fi_FI: {
    globalMessages: splitPlurals(GlobalMessagesFi)
  },
  de_DE: {
    globalMessages: splitPlurals(GlobalMessagesDe)
  },
  nl_NL: {
    globalMessages: splitPlurals(GlobalMessagesNl)
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
