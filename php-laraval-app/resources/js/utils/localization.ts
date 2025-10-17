import i18n from "i18next";
import { initReactI18next } from "react-i18next";

import translationEN from "../../../lang/en_US.json";
import translationSV from "../../../lang/sv_SE.json";

// the translations
const resources = {
  en_US: {
    translation: translationEN,
  },
  sv_SE: {
    translation: translationSV,
  },
};

i18n
  .use(initReactI18next) // passes i18n down to react-i18next
  .init({
    resources,
    lng:
      localStorage.getItem("language") ||
      navigator.language.replace("-", "_") ||
      "en_US",
    fallbackLng: "en_US",
    supportedLngs: ["en_US", "sv_SE"],
    keySeparator: false, // we do not use keys in form messages.welcome

    interpolation: {
      escapeValue: false, // react already safes from xss
    },
  });

export default i18n;
