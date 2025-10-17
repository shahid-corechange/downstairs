import { create } from "zustand";

import { MenuItem } from "@/menu";

interface State {
  sidebar: boolean;
  collapsedMenuItem: Element | null;
  activeMenuItem: HTMLElement | null;
  sidebarWidth: number;
  menuButtons: MenuItem[];
}

interface Actions {
  toggleSidebar: () => void;
  setSidebar: (sidebar: boolean) => void;
  setSidebarWidth: (width: number) => void;
  setCollapsedMenuItem: (element: Element | null) => void;
  setActiveMenuItem: (element: HTMLElement | null) => void;
  setMenuButtons: (menuButtons: MenuItem[]) => void;
}

const initialState: State = {
  sidebar: true,
  sidebarWidth: 0,
  collapsedMenuItem: null,
  activeMenuItem: null,
  menuButtons: [],
};

const useLayoutStore = create<State & Actions>((set) => ({
  ...initialState,
  toggleSidebar: () => set((state) => ({ sidebar: !state.sidebar })),
  setSidebar: (sidebar) => set(() => ({ sidebar })),
  setSidebarWidth: (width) => set(() => ({ sidebarWidth: width })),
  setCollapsedMenuItem: (element) =>
    set(() => ({ collapsedMenuItem: element })),
  setActiveMenuItem: (element) => set(() => ({ activeMenuItem: element })),
  setMenuButtons: (menuButtons) => set(() => ({ menuButtons })),
}));

export default useLayoutStore;
