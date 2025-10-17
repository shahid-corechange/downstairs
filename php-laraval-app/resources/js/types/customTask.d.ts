import Translations from "./translation";

export default interface CustomTask {
  id: number;
  name: string;
  description: string;
  createdAt: string;
  updatedAt: string;
  translations?: Translations;
}
