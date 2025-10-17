import { Dict } from ".";
import CustomTask from "./customTask";
import Product from "./product";
import Service from "./service";
import Translations from "./translation";

export default interface Addon {
  id: number;
  name: string;
  unit: string;
  price: number;
  priceWithVat: number;
  creditPrice: number;
  vatGroup: number;
  hasRut: boolean;
  description: string;
  color: string;
  thumbnailImage: string;
  createdAt: string;
  updatedAt: string;
  fortnoxArticleId?: string;
  deletedAt?: string;
  meta?: Dict;
  tasks?: CustomTask[];
  services?: Service[];
  categories?: Category[];
  products?: Product[];
  translations?: Translations;
}
