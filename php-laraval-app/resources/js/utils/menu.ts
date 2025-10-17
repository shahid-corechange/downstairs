import getMenus, { MenuGroup, MenuItem } from "@/menu";

import i18n from "./localization";

export const createBreadcrumb = () => {
  const path = window.location.pathname;
  const items: (MenuGroup | MenuItem)[] = [];

  const findPathRecursive = (node: MenuItem) => {
    if ("path" in node && node.path === path) {
      return true;
    } else if (node.children && node.children.length > 0) {
      for (const childNode of node.children) {
        const status = findPathRecursive(childNode);

        if (status) {
          items.unshift(node);
          return true;
        }
      }
    }

    return false;
  };

  for (const node of getMenus(i18n.t)) {
    for (const childNode of node.children) {
      const status = findPathRecursive(childNode);

      if (status) {
        items.unshift(node);
        break;
      }
    }
  }

  return items;
};
