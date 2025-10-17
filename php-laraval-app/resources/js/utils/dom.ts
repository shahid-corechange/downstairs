export const isDescendant = (parent: Element, child: Element | null) => {
  if (!child) {
    return false;
  }

  let node = child.parentNode;
  while (node !== null) {
    if (node === parent) {
      return true;
    }
    node = node.parentNode;
  }
  return false;
};
