import {
  ChakraProvider,
  ColorModeScript,
  createStandaloneToast,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { createInertiaApp } from "@inertiajs/react";
import { QueryClientProvider } from "@tanstack/react-query";
import dayjs from "dayjs";
import "dayjs/locale/nn";
import "dayjs/locale/sv";
import duration from "dayjs/plugin/duration";
import localizedFormatPlugin from "dayjs/plugin/localizedFormat";
import objectSupport from "dayjs/plugin/objectSupport";
import timezonePlugin from "dayjs/plugin/timezone";
import updateLocale from "dayjs/plugin/updateLocale";
import utcPlugin from "dayjs/plugin/utc";
import weekOfYear from "dayjs/plugin/weekOfYear";
import weekday from "dayjs/plugin/weekday";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";

import {
  DEFAULT_CURRENCY,
  DEFAULT_LANGUAGE,
  DEFAULT_TIMEZONE,
} from "./constants/user";
import { queryClient } from "./services/client";
import useAuthStore from "./stores/auth";
import "./style.css";
import theme from "./theme";
import { PageProps } from "./types";
import i18n from "./utils/localization";

const appName = "Downstairs";
const { ToastContainer } = createStandaloneToast({ theme });

dayjs.extend(localizedFormatPlugin);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
dayjs.extend(duration);
dayjs.extend(weekday);
dayjs.extend(updateLocale);
dayjs.extend(objectSupport);
dayjs.extend(weekOfYear);

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./pages/${name}.tsx`,
      import.meta.glob("./pages/**/*.tsx"),
    ),
  setup({ el, App, props }) {
    const root = createRoot(el);
    const { setLocale, setUser } = useAuthStore.getState();

    const { user } = (props.initialPage as Page<PageProps>).props;

    if (user) {
      const {
        language = DEFAULT_LANGUAGE,
        currency = DEFAULT_CURRENCY,
        timezone = DEFAULT_TIMEZONE,
      } = user?.info ?? {};

      i18n.changeLanguage(language);

      localStorage.setItem("language", language);

      setLocale(currency, language, timezone);
      setUser(user);
      dayjs.updateLocale(language.split("_")[0], {
        weekStart: 1,
      });
      dayjs.locale(language.split("_")[0]);
      dayjs.tz.setDefault(timezone);
    }

    root.render(
      <QueryClientProvider client={queryClient}>
        <ColorModeScript initialColorMode={theme.config.initialColorMode} />
        <ChakraProvider theme={theme} resetCSS>
          <App {...props} />
          <ToastContainer />
        </ChakraProvider>
      </QueryClientProvider>,
    );
  },
  progress: {
    color: "#246131",
    includeCSS: true,
    showSpinner: true,
  },
});
