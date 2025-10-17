export const parseQueryString = <T = Record<string, string>>(url?: string) => {
  const urlSearchParams = new URLSearchParams(url || window.location.search);
  return Object.fromEntries(urlSearchParams.entries()) as T;
};

export const addQueryString = (key: string, value: string, url?: string) => {
  const qs = parseQueryString(url);
  qs[key] = value;

  return qs;
};

export const updateQueryString = (key: string, value: string) => {
  const url = new URL(window.location.href);
  url.searchParams.set(key, value);
  window.history.pushState(null, "", url.toString());
};
