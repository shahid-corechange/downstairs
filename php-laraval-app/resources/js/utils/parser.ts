export const parseValueByType = (value: string, type = "string") => {
  switch (type) {
    case "integer":
      return parseInt(value);
    case "boolean":
      return value.toLocaleLowerCase() === "true";
    case "float":
      return parseFloat(value);
    default:
      return value;
  }
};
