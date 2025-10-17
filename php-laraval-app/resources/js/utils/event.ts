export const dispatchInputChangeEvent = (
  input: Element,
  value?: string | number | boolean | readonly string[],
) => {
  const trigger = Object.getOwnPropertyDescriptor(
    window.HTMLInputElement.prototype,
    "value",
  )?.set;
  trigger?.call(input, value ?? "");
  input.dispatchEvent(new Event("change", { bubbles: true }));
};
