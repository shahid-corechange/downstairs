import { Meta } from "@/types/utils";

import { Dict } from "@/types";

export const getMeta = (keys: string[], data: Dict) => {
  return keys.reduce((acc, key) => {
    if (data[key]) {
      acc[key] = data[key];
    }

    return acc;
  }, {} as Meta);
};
