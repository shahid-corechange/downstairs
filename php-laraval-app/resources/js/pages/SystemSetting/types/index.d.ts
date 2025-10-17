import Team from "@/types/team";

export type SettingProps = {
  settings: GlobalSetting[];
  teams: Team[];
  refillSequences: Record<number, string>;
};
