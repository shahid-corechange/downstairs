import { extendTheme, withDefaultColorScheme } from "@chakra-ui/react";

const theme = extendTheme(
  {
    initialColorMode: "system",
    useSystemColorMode: false,
    fonts: {
      heading: "Inter, sans-serif",
      body: "Inter, sans-serif",
    },
    colors: {
      brand: {
        50: "#F1F8F4",
        100: "#D6EBDD",
        200: "#9FD1B0",
        300: "#68B683",
        400: "#428A5B",
        500: "#244A31",
        600: "#21452E",
        700: "#1B3725",
        800: "#14291B",
        900: "#0D1C12",
      },
      secondaryGray: {
        100: "#E0E5F2",
        200: "#E1E9F8",
        300: "#F4F7FE",
        400: "#E9EDF7",
        500: "#8F9BBA",
        600: "#A3AED0",
        700: "#707EAE",
        800: "#707EAE",
        900: "#1B2559",
      },
    },
    components: {
      Popover: {
        baseStyle: {
          popper: {
            zIndex: "popover",
          },
        },
      },
    },
  },
  withDefaultColorScheme({ colorScheme: "brand" }),
);

export default theme;
