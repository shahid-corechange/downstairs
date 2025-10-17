import { Button, Icon, ListItem, Text, useDisclosure } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { useCallback, useEffect, useRef, useState } from "react";

import AuthorizationGuard from "@/components/AuthorizationGuard";

import useLayoutStore from "@/stores/layout";

import { transparentize } from "@/utils/color";

import { CashierMenuItem } from "@/menu";

interface MenuItemProps {
  item: CashierMenuItem;
}

const TRANSITION_DELAY = 300;

const MenuItem = ({ item }: MenuItemProps) => {
  const setActiveMenuItem = useLayoutStore((state) => state.setActiveMenuItem);
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [active, setActive] = useState(false);

  const {
    isOpen: isModalOpen,
    onOpen: onModalOpen,
    onClose: onModalClose,
  } = useDisclosure();

  const ref = useRef<HTMLLIElement>(null);
  const additionalProps =
    item.path && !item.component ? { as: Link, href: item.path } : {};

  const handleClick = useCallback(() => {
    if (item.component) {
      onModalOpen();
      return;
    }

    if (isOpen) {
      onClose();
      return;
    }
    onOpen();
  }, [isOpen, onClose, onOpen, onModalOpen, item.component]);

  useEffect(() => {
    if (!item.path) {
      setActive(false);
      return;
    }

    const isActive = window.location.pathname.startsWith(item.path);
    setActive(isActive);
  }, [item.path]);

  useEffect(() => {
    if (!ref.current || !active) {
      return;
    }

    const timeoutId = setTimeout(() => {
      setActiveMenuItem(ref.current!);
    }, TRANSITION_DELAY);

    return () => clearTimeout(timeoutId);
  }, [active, setActiveMenuItem]);

  return (
    <AuthorizationGuard permissions={item.permission}>
      <ListItem
        ref={ref}
        listStyleType="none"
        display="flex"
        justifyContent="center"
        gap={2}
      >
        <Button
          variant="solid"
          fontSize="xl"
          aria-label={item.title}
          w={32}
          h={32}
          mb={4}
          borderWidth={1}
          borderColor="brand.500"
          bg={item.path && active ? "brand.500" : "transparent"}
          _hover={{
            bg:
              item.path && active
                ? "brand.500"
                : transparentize("brand.200", 0.12),
          }}
          _dark={{
            bg: item.path && active ? "brand.100" : "transparent",
            borderColor: item.path && active ? "brand.500" : "brand.200",
            _hover: {
              borderColor: item.path && active ? "brand.500" : "brand.200",
              bg:
                item.path && active
                  ? "brand.100"
                  : transparentize("brand.200", 0.12),
            },
          }}
          onClick={
            (item.path && !item.component) || active ? undefined : handleClick
          }
          {...additionalProps}
          flexDirection="column"
          alignItems="center"
          justifyContent="center"
          gap={2}
        >
          <Icon
            color={item.path && active ? "brand.50" : "brand.500"}
            _dark={{
              color: item.path && active ? "brand.600" : "brand.200",
            }}
            as={item.icon}
            boxSize={12}
          />
          <Text
            whiteSpace="pre-wrap"
            wordBreak="break-word"
            fontSize="sm"
            lineHeight="short"
            textAlign="center"
            color={item.path && active ? "brand.50" : "brand.500"}
            _dark={{
              color: item.path && active ? "brand.600" : "brand.200",
            }}
          >
            {item.title}
          </Text>
        </Button>

        {item.component && (
          <item.component
            isOpen={isModalOpen}
            onClose={onModalClose}
            {...item.componentProps}
          />
        )}
      </ListItem>
    </AuthorizationGuard>
  );
};

export default MenuItem;
