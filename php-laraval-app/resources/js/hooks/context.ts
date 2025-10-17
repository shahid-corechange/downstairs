import React from "react";

export const useContext = <T>(context: React.Context<T | null>) => {
  const contextValue = React.useContext(context);
  if (contextValue === null) {
    throw new Error("useContext must be inside a Provider with a value");
  }
  return contextValue;
};
