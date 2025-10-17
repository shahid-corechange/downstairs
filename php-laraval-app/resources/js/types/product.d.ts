import { Dict } from ".";
import Addon from "./addon";
import Category from "./category";
import Service from "./service";
import Translations from "./translation";

export default interface Product {
  id: number;
  name: string;
  unit: string;
  price: number;
  priceWithVat: number;
  creditPrice: number;
  vatGroup: number;
  hasRut: boolean;
  description: string;
  thumbnailImage: string;
  createdAt: string;
  updatedAt: string;
  color: string;
  fortnoxArticleId?: string;
  deletedAt?: string;
  meta?: Dict;
  categories?: Category[];
  addons?: Addon[];
  services?: Service[];
  stores?: Store[];
  translations?: Translations;
  tasks?: CustomTask[];
}
