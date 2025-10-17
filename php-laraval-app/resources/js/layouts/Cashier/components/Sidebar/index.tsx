import { Box, List, useBreakpointValue, useConst } from "@chakra-ui/react";
import { useEffect, useRef } from "react";
import { useTranslation } from "react-i18next";

import HorizontalCollapse from "@/components/HorizontalCollapse";

import { NAVBAR_HEIGHT } from "@/constants/layout";

import useLayoutStore from "@/stores/layout";

import { getCashierMenus } from "@/menu";

import MenuItem from "./components/MenuItem";

interface SidebarProps {
  customerId?: number;
}

const Sidebar = ({ customerId }: SidebarProps) => {
  const { t } = useTranslation();

  const sidebar = useLayoutStore((state) => state.sidebar);
  const setSidebar = useLayoutStore((state) => state.setSidebar);
  const setSidebarWidth = useLayoutStore((state) => state.setSidebarWidth);
  const activeMenuItem = useLayoutStore((state) => state.activeMenuItem);

  const menuButtons = useLayoutStore((state) => state.menuButtons);
  const setMenuButtons = useLayoutStore((state) => state.setMenuButtons);

  const isDesktop = useBreakpointValue(
    { base: false, md: true },
    { fallback: "md" },
  );
  const ref = useRef<HTMLDivElement>(null);

  const menus = useConst(getCashierMenus(t, customerId));
  const currentPath = window.location.pathname;
  const currentMenu = menus.find((menu) => {
    if (!menu.path) {
      return false;
    }

    return currentPath.startsWith(menu.path);
  });

  useEffect(() => {
    if (currentMenu?.children) {
      setMenuButtons(currentMenu.children);
    } else {
      setMenuButtons(menus[0].children);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentPath, currentMenu, menus]);

  useEffect(() => {
    setSidebar(!!isDesktop);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isDesktop]);

  useEffect(() => {
    if (ref.current && activeMenuItem) {
      ref.current.scrollTop =
        activeMenuItem.offsetTop -
        ref.current.offsetHeight +
        40 +
        window.innerHeight / 2;
    }
  }, [activeMenuItem]);

  useEffect(() => {
    if (ref.current) {
      setSidebarWidth(ref.current.offsetWidth);
    }
  }, [ref.current]);

  return (
    <Box
      ref={ref}
      position="sticky"
      top={`${NAVBAR_HEIGHT}px`}
      h={`calc(100vh - ${NAVBAR_HEIGHT}px)`}
      overflow="auto"
      flexShrink={0}
      scrollBehavior="smooth"
      bg="inherit"
    >
      <HorizontalCollapse in={sidebar}>
        <List
          minH={`calc(100vh - ${NAVBAR_HEIGHT}px)`}
          w={40}
          ml={0}
          py={4}
          display="flex"
          flexDir="column"
        >
          {menuButtons.map((item, index) => (
            <MenuItem key={index} item={item} />
          ))}
        </List>
      </HorizontalCollapse>
    </Box>
  );
};

export default Sidebar;
