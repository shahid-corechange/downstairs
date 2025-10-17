import { create } from "zustand";

import User from "@/types/user";

interface State {
  currency: string;
  language: string;
  shortLanguage: string;
  timezone: string;
  user?: User;
}

interface Actions {
  setLocale: (currency: string, language: string, timezone: string) => void;
  setUser: (user: User) => void;
}

const initialState: State = {
  currency: "USD",
  language: "en_US",
  shortLanguage: "en",
  timezone: "UTC",
};

const useAuthStore = create<State & Actions>((set) => ({
  ...initialState,
  setLocale: (currency, language, timezone) =>
    set(() => ({
      currency,
      language,
      timezone,
      shortLanguage: language.split("_")[0],
    })),
  setUser: (user) => set(() => ({ user })),
}));

export default useAuthStore;
