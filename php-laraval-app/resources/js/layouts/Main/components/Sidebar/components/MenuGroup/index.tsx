import {
  Divider,
  Flex,
  Heading,
  List,
  ListItem,
  ListItemProps,
} from "@chakra-ui/react";
import { useEffect, useRef, useState } from "react";

import AuthorizationGuard from "@/components/AuthorizationGuard";

import { MenuGroup as MenuGroupType } from "@/menu";

import MenuItem from "../MenuItem";

interface MenuGroupProps extends ListItemProps {
  item: MenuGroupType;
}

const MenuGroup = ({ item, ...props }: MenuGroupProps) => {
  const [show, setShow] = useState(true);
  const ref = useRef<HTMLLIElement>(null);

  useEffect(() => {
    if (ref.current) {
      const totalChildren = ref.current.querySelectorAll("a").length;
      setShow(totalChildren > 0);
    }
  }, [ref]);

  return show ? (
    <AuthorizationGuard permissions={item.permission}>
      <ListItem ref={ref} {...props}>
        {item.group && (
          <Flex direction="column" mt={6} mb={2} px={4} gap={2}>
            <Heading fontSize="small" fontWeight="normal" color="gray.500">
              {item.group}
            </Heading>
            <Divider />
          </Flex>
        )}
        <List ml={0}>
          {item.children.map((item, index) => (
            <MenuItem key={index} item={item} />
          ))}
        </List>
      </ListItem>
    </AuthorizationGuard>
  ) : null;
};

export default MenuGroup;
