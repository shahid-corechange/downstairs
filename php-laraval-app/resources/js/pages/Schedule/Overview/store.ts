import { Dayjs } from "dayjs";
import { create } from "zustand";

import { SIMPLE_TIME_FORMAT } from "@/constants/datetime";
import { DEFAULT_CREATION_LIMIT_DAYS } from "@/constants/subscription";

import Schedule from "@/types/schedule";
import Team from "@/types/team";

import { getQuarter, toDayjs } from "@/utils/datetime";
import { updateQueryString } from "@/utils/querystring";

import { ScheduleCellInfo, ScheduleFilterStatus } from "./types";

interface State {
  schedules: Schedule[];
  filteredSchedules: Schedule[];
  teams: Team[];
  shownTeamIds: number[];
  availableBlocks: ScheduleCellInfo[];
  selectedDate: Dayjs;
  selectedDayIndex: number | null;
  showWeekend: boolean;
  showEarlyHours: boolean;
  showLateHours: boolean;
  statusFilter: ScheduleFilterStatus;
  creationLimitDays: number;
  minQuarterFilter?: number;
  minStartTimeFilter?: string;
  cityFilter?: string;
  customerFilter?: string;
  openedScheduleId?: number;
  newSubscription?: ScheduleCellInfo;
  draggedSchedule?: Schedule;
  draggedScheduleRect?: Omit<DOMRect, "bottom" | "right" | "toJSON">;
  dragTarget?: ScheduleCellInfo;
  scheduleComponentRef?: React.RefObject<HTMLDivElement>;
  tableComponentRef?: React.RefObject<HTMLTableElement>;
}

type ScheduleFilters = Partial<
  Pick<
    State,
    | "shownTeamIds"
    | "selectedDayIndex"
    | "minQuarterFilter"
    | "minStartTimeFilter"
    | "cityFilter"
    | "customerFilter"
    | "statusFilter"
  >
>;

interface Actions {
  setSchedulesAndTeams: (data: Pick<State, "schedules" | "teams">) => void;
  updateFilteredSchedule: (filters: ScheduleFilters) => void;
  setShownTeamIds: (teamIds: number[]) => void;
  addTeam: (id: number) => void;
  removeTeam: (id: number) => void;
  selectDate: (selectedDate: Dayjs) => void;
  selectDayIndex: (dayIndex: number | null) => void;
  setMinQuarterFilter: (minQuarter: number | undefined) => void;
  setStartTimeFilter: (minStartTime: string | undefined) => void;
  setCityFilter: (city: string | undefined) => void;
  setCustomerFilter: (customer: string | undefined) => void;
  setStatusFilter: (status: ScheduleFilterStatus) => void;
  setOpenedScheduleId: (id: number | undefined) => void;
  setNewSubscription: (data?: ScheduleCellInfo) => void;
  setAvailableBlocks: (blocks?: ScheduleCellInfo[]) => void;
  setDraggedSchedule: (schedule?: Schedule) => void;
  setDraggedScheduleRect: (
    rect?: Omit<DOMRect, "bottom" | "right" | "toJSON">,
  ) => void;
  setDragTarget: (target?: ScheduleCellInfo) => void;
  setScheduleComponentRef: (ref?: React.RefObject<HTMLDivElement>) => void;
  setTableComponentRef: (ref?: React.RefObject<HTMLTableElement>) => void;
  setShowWeekend: (showWeekend: boolean) => void;
  setShowEarlyHours: (showEarlyHours: boolean) => void;
  setShowLateHours: (showLateHours: boolean) => void;
  toggleShowWeekend: () => void;
  toggleShowEarlyHours: () => void;
  toggleShowLateHours: () => void;
  toggleShowAll: () => void;
  addSchedule: (schedule: Schedule) => void;
  updateSchedule: (schedule: Schedule) => void;
  reset: () => void;
  setCreationLimitDays: (sequence: number) => void;
}

const getInitialState = (): State => ({
  schedules: [],
  filteredSchedules: [],
  teams: [],
  shownTeamIds: [],
  availableBlocks: [],
  selectedDate: toDayjs().startOf("day"),
  customerFilter: undefined,
  cityFilter: undefined,
  creationLimitDays: DEFAULT_CREATION_LIMIT_DAYS,
  minQuarterFilter: undefined,
  minStartTimeFilter: undefined,
  statusFilter: "active",
  selectedDayIndex: null,
  showWeekend: false,
  showEarlyHours: false,
  showLateHours: false,
});

const useScheduleStore = create<State & Actions>()((set, get) => ({
  ...getInitialState(),
  setSchedulesAndTeams: ({ schedules, teams }) => {
    set({ schedules, teams });
  },
  updateFilteredSchedule: ({
    customerFilter = get().customerFilter,
    cityFilter = get().cityFilter,
    shownTeamIds = get().shownTeamIds,
    selectedDayIndex = get().selectedDayIndex,
    minQuarterFilter = get().minQuarterFilter,
    minStartTimeFilter = get().minStartTimeFilter,
    statusFilter = get().statusFilter,
  }) => {
    const newFilteredSchedules = get().schedules.reduce<Schedule[]>(
      (acc, schedule) => {
        const startAt = toDayjs(schedule.startAt);
        const endAt = toDayjs(schedule.endAt);

        const teamId = schedule?.team?.id ?? -1;
        const dayIndex = startAt.weekday();
        const city = schedule?.property?.address?.city?.name ?? "";
        const customer = schedule?.user?.fullname ?? "";
        const time = startAt.format(SIMPLE_TIME_FORMAT);
        const quarter = getQuarter(startAt, endAt);

        if (
          shownTeamIds.includes(teamId) &&
          (selectedDayIndex === null || dayIndex === selectedDayIndex) &&
          (!customerFilter ||
            customer.toLowerCase().includes(customerFilter.toLowerCase())) &&
          (!cityFilter ||
            city.toLowerCase().includes(cityFilter.toLowerCase())) &&
          (!minStartTimeFilter || time >= minStartTimeFilter) &&
          (!minQuarterFilter || quarter >= minQuarterFilter) &&
          (statusFilter === "all" ||
            schedule.status === statusFilter ||
            (statusFilter === "active" && schedule.status !== "cancel"))
        ) {
          acc.push(schedule);
        }

        return acc;
      },
      [],
    );

    set({ filteredSchedules: newFilteredSchedules });
  },
  selectDate: (selectedDate) => {
    set({ selectedDate });
  },
  setShownTeamIds: (teamIds) => {
    get().updateFilteredSchedule({ shownTeamIds: teamIds });
    set({ shownTeamIds: teamIds });
  },
  addTeam: (id) => {
    const shownTeamIds = [...get().shownTeamIds, id];
    get().updateFilteredSchedule({ shownTeamIds });
    set({ shownTeamIds });
  },
  removeTeam: (id) => {
    const shownTeamIds = get().shownTeamIds.filter((teamId) => id !== teamId);
    get().updateFilteredSchedule({ shownTeamIds });
    set({ shownTeamIds });
  },
  selectDayIndex: (dayIndex) => {
    get().updateFilteredSchedule({ selectedDayIndex: dayIndex });
    set({ selectedDayIndex: dayIndex });
  },
  setMinQuarterFilter: (minQuarter) => {
    get().updateFilteredSchedule({ minQuarterFilter: minQuarter });
    set({ minQuarterFilter: minQuarter });
  },
  setStartTimeFilter: (minStartTime) => {
    get().updateFilteredSchedule({ minStartTimeFilter: minStartTime });
    set({ minStartTimeFilter: minStartTime });
  },
  setCityFilter: (city) => {
    get().updateFilteredSchedule({ cityFilter: city });
    set({ cityFilter: city });
  },
  setCustomerFilter: (customer) => {
    get().updateFilteredSchedule({ customerFilter: customer });
    set({ customerFilter: customer });
  },
  setStatusFilter: (status) => {
    get().updateFilteredSchedule({ statusFilter: status });
    set({ statusFilter: status });
  },
  setAvailableBlocks: (blocks) => set({ availableBlocks: blocks }),
  setNewSubscription: (data) => set({ newSubscription: data }),
  setOpenedScheduleId: (id) => set({ openedScheduleId: id }),
  setDraggedSchedule: (schedule) => set({ draggedSchedule: schedule }),
  setDraggedScheduleRect: (rect) => set({ draggedScheduleRect: rect }),
  setDragTarget: (target) => set({ dragTarget: target }),
  setScheduleComponentRef: (ref) => set({ scheduleComponentRef: ref }),
  setTableComponentRef: (ref) => set({ tableComponentRef: ref }),
  setShowWeekend: (showWeekend) => set({ showWeekend }),
  setShowEarlyHours: (showEarlyHours) => set({ showEarlyHours }),
  setShowLateHours: (showLateHours) => set({ showLateHours }),
  toggleShowWeekend: () => {
    updateQueryString("view.showWeekend", String(!get().showWeekend));
    set({ showWeekend: !get().showWeekend });
  },
  toggleShowEarlyHours: () => {
    updateQueryString("view.showEarlyHours", String(!get().showEarlyHours));
    set({ showEarlyHours: !get().showEarlyHours });
  },
  toggleShowLateHours: () => {
    updateQueryString("view.showLateHours", String(!get().showLateHours));
    set({ showLateHours: !get().showLateHours });
  },
  toggleShowAll: () => {
    const status =
      get().showEarlyHours && get().showLateHours && get().showWeekend;

    updateQueryString("view.showWeekend", String(!status));
    updateQueryString("view.showEarlyHours", String(!status));
    updateQueryString("view.showLateHours", String(!status));

    set({
      showEarlyHours: !status,
      showLateHours: !status,
      showWeekend: !status,
    });
  },
  addSchedule: (schedule) => {
    const schedules = [...get().schedules, schedule];
    set({ schedules });
    get().updateFilteredSchedule({});
  },
  updateSchedule: (schedule) => {
    const schedules = get().schedules.map((item) =>
      item.id === schedule.id ? schedule : item,
    );
    set({ schedules });
    get().updateFilteredSchedule({});
  },
  reset: () => set(getInitialState()),
  setCreationLimitDays: (sequence) => set({ creationLimitDays: sequence }),
}));

export default useScheduleStore;
