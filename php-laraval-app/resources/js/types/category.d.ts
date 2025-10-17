import Translations from "./translation";

export default interface Category {
  id: number;
  name: string;
  description: string;
  thumbnailImage: string;
  translations?: Translations;
  createdAt: string;
  updatedAt: string;
  deletedAt?: string;
}
