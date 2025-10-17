import {
  Avatar,
  Flex,
  Icon,
  IconButton,
  Image,
  Menu,
  MenuButton,
  MenuDivider,
  MenuItem,
  MenuList,
  Spacer,
  Text,
  useBreakpointValue,
  useColorMode,
  useColorModeValue,
} from "@chakra-ui/react";
import { Link, router, usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { RiMenuFoldLine } from "react-icons/ri";

import useCart from "@/hooks/useCart";

import useLayoutStore from "@/stores/layout";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import ColorModeToggle from "./components/ColorModeToggle";
import StoreSelector from "./components/StoreSelector";

const logoSources = {
  light: "/images/small-logo-b.png",
  dark: "/images/small-logo-w.png",
  mobile: "/images/mobile-logo.png",
};

const Navbar = () => {
  const { t } = useTranslation();

  const { colorMode } = useColorMode();
  const sidebar = useLayoutStore((state) => state.sidebar);
  const toggleSidebar = useLayoutStore((state) => state.toggleSidebar);
  const { user } = usePage<PageProps>().props;
  const hasCashierAccess = hasPermission("access cashier");

  const bg = useColorModeValue("white", "gray.800");
  const logo = useBreakpointValue({
    base: logoSources["mobile"],
    md: logoSources[colorMode],
  });

  const { reset } = useCart();

  const handleLogout = () => {
    reset();
    router.post("/logout");
  };

  return (
    <Flex
      position="sticky"
      top={0}
      w="full"
      py={2}
      bg={bg}
      borderBottom="1px"
      borderColor="inherit"
      boxShadow="sm"
      zIndex="docked"
    >
      <Flex
        align="center"
        justify="center"
        transition="width 0.2s"
        w={{ base: 20, md: 60 }}
        borderRight="1px"
        borderColor="inherit"
      >
        <Image h={10} src={logo} alt="Logo" />
      </Flex>
      <Flex flex={1} align="center" px={4}>
        <IconButton
          variant="ghost"
          size="md"
          fontSize="xl"
          aria-label="Toggle Sidebar"
          onClick={toggleSidebar}
        >
          <Icon
            as={RiMenuFoldLine}
            transition="all 0.2s"
            transform={`scaleX(${sidebar ? 1 : -1})`}
          />
        </IconButton>
        <Spacer />
        <Flex gap={2}>
          {hasCashierAccess && <StoreSelector />}
          <ColorModeToggle />
          <Menu>
            <MenuButton as={IconButton} variant="unstyled">
              <Avatar
                size="sm"
                name={user.fullname}
                src={user?.info?.avatar ?? ""}
              />
            </MenuButton>
            <MenuList minW={300}>
              <Flex align="center" p={4} gap={4}>
                <Avatar
                  size="md"
                  name={user.fullname}
                  src={user?.info?.avatar ?? ""}
                />
                <Flex direction="column">
                  <Text fontSize="sm" fontWeight="bold">
                    {user.fullname}
                  </Text>
                  <Text fontSize="sm">{user.email}</Text>
                </Flex>
              </Flex>
              <MenuDivider />
              <MenuItem as={Link} href="/profile" px={4} fontSize="sm">
                {t("my account")}
              </MenuItem>
              <MenuItem px={4} fontSize="sm" onClick={handleLogout}>
                {t("log out")}
              </MenuItem>
            </MenuList>
          </Menu>
        </Flex>
      </Flex>
    </Flex>
  );
};

export default Navbar;
