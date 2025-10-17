import {
  Button,
  Collapse,
  Icon,
  List,
  ListItem,
  Spacer,
  useDisclosure,
} from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { useEffect, useRef, useState } from "react";
import { LuChevronDown } from "react-icons/lu";

import AuthorizationGuard from "@/components/AuthorizationGuard";

import useLayoutStore from "@/stores/layout";

import { transparentize } from "@/utils/color";
import { isDescendant } from "@/utils/dom";

import { MenuItem as MenuItemType } from "@/menu";

interface MenuItemProps {
  item: MenuItemType;
  level?: number;
}

const MenuItem = ({ item, level = 0 }: MenuItemProps) => {
  const collapsedMenuItem = useLayoutStore((state) => state.collapsedMenuItem);
  const setCollapsedMenuItem = useLayoutStore(
    (state) => state.setCollapsedMenuItem,
  );
  const setActiveMenuItem = useLayoutStore((state) => state.setActiveMenuItem);
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [active, setActive] = useState(false);
  const [show, setShow] = useState(true);

  const ref = useRef<HTMLLIElement>(null);
  const additionalProps = item.path ? { as: Link, href: item.path } : {};

  const handleToggle = () => {
    if (isOpen) {
      onClose();
      setCollapsedMenuItem(null);
      return;
    }

    onOpen();
    setCollapsedMenuItem(ref.current);
  };

  useEffect(() => {
    if (item.path === window.location.pathname) {
      setActive(true);
    } else if (ref.current) {
      const activeChild = ref.current.querySelector(
        `[href="${window.location.pathname}"]`,
      );

      if (activeChild) {
        setActive(true);

        setTimeout(() => setActiveMenuItem(ref.current), 300); // Wait for the transition to finish
      }

      const totalChildren = ref.current.querySelectorAll("a").length;
      setShow(totalChildren > 0);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ref]);

  useEffect(() => {
    if (
      ref.current &&
      ref.current !== collapsedMenuItem &&
      !isDescendant(ref.current, collapsedMenuItem)
    ) {
      onClose();
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ref, collapsedMenuItem]);

  return show ? (
    <AuthorizationGuard permissions={item.permission}>
      <ListItem ref={ref} listStyleType="none">
        <Button
          variant="ghost"
          w="full"
          fontSize="small"
          fontWeight="normal"
          rounded="none"
          pl={`${level * 0.5 + 1}rem`}
          pr={4}
          bg={item.path && active ? "brand.100" : "transparent"}
          _hover={{ bg: item.path && active ? "brand.100" : "brand.50" }}
          _dark={{
            color: item.path && active ? "brand.600" : "brand.200",
            _hover: {
              bg:
                item.path && active
                  ? "brand.100"
                  : transparentize("brand.200", 0.12),
            },
          }}
          onClick={item.path || active ? undefined : handleToggle}
          {...additionalProps}
        >
          {item.icon && <Icon as={item.icon} mr={4} boxSize={5} />}
          {item.title}
          <Spacer />
          {item.children && item.children.length > 0 && (
            <Icon
              as={LuChevronDown}
              transition="transform 0.2s"
              transform={`rotate(${active || isOpen ? "180deg" : "0deg"})`}
            />
          )}
        </Button>
        {item.children && item.children.length > 0 && (
          <Collapse in={active || isOpen} animateOpacity>
            <List ml={0}>
              {item.children.map((item, index) => (
                <MenuItem key={index} item={item} level={level + 1} />
              ))}
            </List>
          </Collapse>
        )}
      </ListItem>
    </AuthorizationGuard>
  ) : null;
};

export default MenuItem;
