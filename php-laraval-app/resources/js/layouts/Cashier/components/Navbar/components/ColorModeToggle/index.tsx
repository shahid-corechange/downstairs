import { IconButton, useColorMode } from "@chakra-ui/react";
import { LuCloudSun, LuMoonStar } from "react-icons/lu";

const ColorModeToggle = () => {
  const { colorMode, toggleColorMode } = useColorMode();

  return (
    <IconButton
      variant="ghost"
      size="md"
      fontSize="2xl"
      _hover={{
        color: colorMode === "light" ? "yellow.500" : "white",
      }}
      aria-label="Toggle theme"
      onClick={toggleColorMode}
    >
      {colorMode === "light" ? <LuCloudSun /> : <LuMoonStar />}
    </IconButton>
  );
};

export default ColorModeToggle;
