export const capitalizeString = (input: string) => {
  return input.charAt(0).toUpperCase() + input.slice(1).toLowerCase();
};

export const toPascalCase = (input: string) => {
  return input
    .split(" ")
    .map((word) => capitalizeString(word))
    .join("");
};

export const pascalToSentence = (input: string) => {
  return input
    .replace(/([a-z])([A-Z])/g, "$1 $2")
    .replace(/([A-Z])([A-Z][a-z])/g, "$1 $2");
};

export const capitalizeEachWord = (str: string): string => {
  return str
    .split(" ")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(" ");
};
