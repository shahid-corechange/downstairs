import Addon from "./addon";

export default interface AddonStatistic {
  itemableId: number;
  credit: number;
  currency: number;
  total: number;
  addon?: Addon;
}
