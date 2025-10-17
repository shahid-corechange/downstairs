import Addon from "./addon";
import Category from "./category";
import CustomTask from "./customTask";
import Product from "./product";
import ServiceQuarter from "./serviceQuarter";
import Translations from "./translation";

export default interface Service {
  id: number;
  name: string;
  type: "cleaning" | "laundry";
  membershipType: "company" | "private";
  price: number;
  priceWithVat: number;
  vatGroup: number;
  hasRut: boolean;
  description: string;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  fortnoxArticleId?: string;
  thumbnailImage?: string;
  categories?: Category[];
  addons?: Addon[];
  products?: Product[];
  tasks?: CustomTask[];
  translations?: Translations;
  quarters?: ServiceQuarter[];
}
