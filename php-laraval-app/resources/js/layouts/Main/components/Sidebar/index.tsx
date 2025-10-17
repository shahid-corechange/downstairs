import { Box, List, useBreakpointValue, useConst } from "@chakra-ui/react";
import { useEffect, useRef } from "react";
import { useTranslation } from "react-i18next";

import HorizontalCollapse from "@/components/HorizontalCollapse";

import { NAVBAR_HEIGHT } from "@/constants/layout";

import useLayoutStore from "@/stores/layout";

import getMenus from "@/menu";

import { default as MenuGroup } from "./components/MenuGroup";

const Sidebar = () => {
  const { t } = useTranslation();

  const sidebar = useLayoutStore((state) => state.sidebar);
  const setSidebar = useLayoutStore((state) => state.setSidebar);
  const setSidebarWidth = useLayoutStore((state) => state.setSidebarWidth);
  const activeMenuItem = useLayoutStore((state) => state.activeMenuItem);

  const isDesktop = useBreakpointValue(
    { base: false, md: true },
    { fallback: "md" },
  );
  const ref = useRef<HTMLDivElement>(null);

  const menus = useConst(getMenus(t));

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
  }, [ref.current, activeMenuItem]);

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
    >
      <HorizontalCollapse in={sidebar}>
        <List
          minH={`calc(100vh - ${NAVBAR_HEIGHT}px)`}
          w={60}
          ml={0}
          py={2}
          display="flex"
          flexDir="column"
          borderRight="1px"
          borderColor="inherit"
        >
          {menus.map((item, index) => (
            <MenuGroup key={index} item={item} />
          ))}
        </List>
      </HorizontalCollapse>
    </Box>
  );
};

export default Sidebar;
