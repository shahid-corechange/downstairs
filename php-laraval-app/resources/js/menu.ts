import { TFunction } from "i18next";
import { IconType } from "react-icons";
import {
  AiFillCalendar,
  AiOutlineArrowLeft,
  AiOutlineCalendar,
  AiOutlineClear,
  AiOutlineClockCircle,
  AiOutlineCode,
  AiOutlineCodeSandbox,
  AiOutlineContacts,
  AiOutlineContainer,
  AiOutlineEuro,
  AiOutlineHome,
  AiOutlineIdcard,
  AiOutlineInteraction,
  AiOutlineLogout,
  AiOutlineSchedule,
  AiOutlineSearch,
  AiOutlineSetting,
  AiOutlineShop,
  AiOutlineShopping,
  AiOutlineSkin,
  AiOutlineTags,
  AiOutlineTeam,
  AiOutlineUser,
  AiOutlineUserAdd,
  AiOutlineUserSwitch,
  AiOutlineWarning,
} from "react-icons/ai";
import { IoKeyOutline } from "react-icons/io5";
import {
  MdOutlineHistory,
  MdOutlineShoppingCart,
  MdOutlineTimeToLeave,
} from "react-icons/md";
import { PiPresentationChart } from "react-icons/pi";

import CustomerTypeSelectionModal from "@/components/CustomerTypeSelectionModal";

import StoreSelectionModal from "./components/StoreSelectionModal";
import PERMISSIONS from "./constants/permission";
import CustomerInformationModal from "./pages/CashierCustomer/CustomerInformation";

export interface MenuItem {
  title: string;
  path?: string;
  icon?: IconType;
  permission?: keyof typeof PERMISSIONS;
  children?: MenuItem[];
}

export interface MenuGroup {
  group?: string;
  permission?: keyof typeof PERMISSIONS;
  children: MenuItem[];
}

export interface CashierMenuGroup {
  path?: string;
  children: CashierMenuItem[];
}

export interface CashierMenuItem {
  title: string;
  path?: string;
  icon?: IconType;
  permission?: keyof typeof PERMISSIONS;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  component?: React.ComponentType<any>;
  componentProps?: Record<string, unknown>;
}

const getMenus = (t: TFunction): MenuGroup[] => [
  {
    children: [
      {
        title: t("dashboard"),
        icon: PiPresentationChart,
        path: "/dashboard",
      },
    ],
  },
  {
    group: t("operation"),
    children: [
      {
        title: t("schedules"),
        icon: AiOutlineCalendar,
        children: [
          {
            title: t("overview"),
            path: "/schedules",
            permission: "schedules index",
          },
          {
            title: t("change requests"),
            children: [
              {
                title: t("overview"),
                path: "/schedules/change-requests",
                permission: "schedule change requests index",
              },
              {
                title: t("history"),
                path: "/schedules/change-requests/histories",
                permission: "schedule change requests index",
              },
            ],
          },
        ],
      },
      {
        title: t("laundry orders"),
        icon: AiOutlineSkin,
        children: [
          {
            title: t("overview"),
            path: "/laundry-orders",
            permission: "laundry orders index",
          },
        ],
      },
      {
        title: t("orders"),
        icon: AiOutlineContainer,
        children: [
          {
            title: t("overview"),
            path: "/orders",
            permission: "orders index",
          },
        ],
      },
      {
        title: t("invoices"),
        icon: AiOutlineEuro,
        children: [
          {
            title: t("overview"),
            path: "/invoices",
            permission: "invoices index",
          },
        ],
      },
      {
        title: t("unassign subscriptions"),
        icon: AiOutlineSchedule,
        children: [
          {
            title: t("overview"),
            path: "/unassign-subscriptions",
            permission: "unassign subscriptions index",
          },
        ],
      },
    ],
  },
  {
    group: t("private customer"),
    children: [
      {
        title: t("accounts"),
        icon: AiOutlineUser,
        children: [
          {
            title: t("overview"),
            path: "/customers",
            permission: "customers index",
          },
          {
            title: t("wizardAccounts"),
            path: "/customers/wizard",
            permission: "customers wizard",
          },
          {
            title: t("fixed prices"),
            path: "/customers/fixedprices",
            permission: "fixed prices index",
          },
          {
            title: t("discounts"),
            path: "/customers/discounts",
            permission: "customer discounts index",
          },
        ],
      },
      {
        title: t("properties"),
        icon: AiOutlineHome,
        children: [
          {
            title: t("overview"),
            path: "/customers/properties",
            permission: "properties index",
          },
          {
            title: t("wizardProperties"),
            path: "/customers/properties/wizard",
            permission: "properties wizard",
          },
        ],
      },
      {
        title: t("subscriptions"),
        icon: AiOutlineIdcard,
        children: [
          {
            title: t("overview"),
            path: "/customers/subscriptions",
            permission: "subscriptions index",
          },
          {
            title: t("wizardSubscriptions"),
            path: "/customers/subscriptions/wizard",
            permission: "subscriptions wizard",
          },
        ],
      },
    ],
  },
  {
    group: t("company customer"),
    children: [
      {
        title: t("accounts"),
        icon: AiOutlineUser,
        children: [
          {
            title: t("overview"),
            path: "/companies",
            permission: "companies index",
          },
          {
            title: t("wizardAccounts"),
            path: "/companies/wizard",
            permission: "companies wizard",
          },
          {
            title: t("fixed prices"),
            path: "/companies/fixedprices",
            permission: "company fixed prices index",
          },
          {
            title: t("discounts"),
            path: "/companies/discounts",
            permission: "company discounts index",
          },
        ],
      },
      {
        title: t("properties"),
        icon: AiOutlineHome,
        children: [
          {
            title: t("overview"),
            path: "/companies/properties",
            permission: "company properties index",
          },
          {
            title: t("wizardProperties"),
            path: "/companies/properties/wizard",
            permission: "company properties wizard",
          },
        ],
      },
      {
        title: t("subscriptions"),
        icon: AiOutlineIdcard,
        children: [
          {
            title: t("overview"),
            path: "/companies/subscriptions",
            permission: "company subscriptions index",
          },
          {
            title: t("wizardSubscriptions"),
            path: "/companies/subscriptions/wizard",
            permission: "company subscriptions wizard",
          },
        ],
      },
    ],
  },
  {
    group: t("employee"),
    children: [
      {
        title: t("accounts"),
        icon: AiOutlineContacts,
        children: [
          {
            title: t("overview"),
            path: "/employees",
            permission: "employees index",
          },
          {
            title: t("wizardAccounts"),
            path: "/employees/wizard",
            permission: "employees wizard",
          },
        ],
      },
      {
        title: t("teams"),
        icon: AiOutlineTeam,
        children: [
          {
            title: t("overview"),
            path: "/teams",
            permission: "teams index",
          },
        ],
      },
      {
        title: t("deviations"),
        icon: AiOutlineWarning,
        children: [
          {
            title: t("overview"),
            path: "/deviations",
            permission: "deviations index",
          },
          {
            title: t("employee"),
            path: "/deviations/employee",
            permission: "deviations index",
          },
        ],
      },
      {
        title: t("time reports"),
        icon: AiOutlineClockCircle,
        children: [
          {
            title: t("overview"),
            path: "/time-reports",
            permission: "time reports index",
          },
          {
            title: t("daily"),
            path: "/time-reports/daily",
            permission: "time reports index",
          },
        ],
      },
      {
        title: t("leave registration"),
        icon: MdOutlineTimeToLeave,
        children: [
          {
            title: t("overview"),
            path: "/leave-registrations",
            permission: "leave registrations index",
          },
        ],
      },
    ],
  },
  {
    group: t("management"),
    children: [
      {
        title: t("categories"),
        icon: AiOutlineTags,
        children: [
          {
            title: t("overview"),
            path: "/categories",
            permission: "categories index",
          },
        ],
      },
      {
        title: t("stores"),
        icon: AiOutlineShop,
        children: [
          {
            title: t("overview"),
            path: "/stores",
            permission: "stores index",
          },
        ],
      },
      {
        title: t("services"),
        icon: AiOutlineClear,
        children: [
          {
            title: t("overview"),
            path: "/services",
            permission: "services index",
          },
          {
            title: t("quarter rules"),
            path: "/services/quarters",
            permission: "service quarters index",
          },
        ],
      },
      {
        title: t("addons"),
        icon: AiOutlineCodeSandbox,
        children: [
          {
            title: t("overview"),
            path: "/addons",
            permission: "addons index",
          },
        ],
      },
      {
        title: t("products"),
        icon: AiOutlineShopping,
        children: [
          {
            title: t("overview"),
            path: "/products",
            permission: "products index",
          },
        ],
      },
      {
        title: t("price adjustments"),
        icon: AiOutlineInteraction,
        children: [
          {
            title: t("overview"),
            path: "/price-adjustments",
            permission: "price adjustment index",
          },
        ],
      },
      {
        title: t("block days"),
        icon: AiFillCalendar,
        children: [
          {
            title: t("overview"),
            path: "/blockdays",
            permission: "blockdays index",
          },
        ],
      },
      {
        title: t("key places"),
        icon: IoKeyOutline,
        children: [
          {
            title: t("overview"),
            path: "/keyplaces",
            permission: "key places index",
          },
        ],
      },
      {
        title: t("roles"),
        icon: AiOutlineUserSwitch,
        children: [
          {
            title: t("overview"),
            path: "/roles",
            permission: "roles index",
          },
        ],
      },
      {
        title: t("system settings"),
        icon: AiOutlineSetting,
        children: [
          {
            title: t("overview"),
            path: "/system-settings",
            permission: "system settings index",
          },
        ],
      },
    ],
  },
  {
    group: t("monitoring"),
    children: [
      {
        title: t("feedbacks"),
        icon: AiOutlineInteraction,
        children: [
          {
            title: t("overview"),
            path: "/feedbacks",
            permission: "feedbacks index",
          },
        ],
      },

      {
        title: t("logs"),
        icon: AiOutlineCode,
        children: [
          {
            title: t("activity logs"),
            path: "/log/activities",
            permission: "activity logs index",
          },
          {
            title: t("authentication logs"),
            path: "/log/authentications",
            permission: "authentication logs index",
          },
          //   {
          //     title: "Audit Logs",
          //     path: "/logs/audit",
          //   },
        ],
      },
    ],
  },
];

export const getCashierMenus = (
  t: TFunction,
  customerId?: number,
): CashierMenuGroup[] => [
  {
    children: [
      {
        title: t("search"),
        path: "/cashier/search",
        icon: AiOutlineSearch,
      },
      {
        title: t("new customer"),
        path: "/cashier/customers/wizard",
        component: CustomerTypeSelectionModal,
        icon: AiOutlineUserAdd,
      },
      {
        title: t("orders"),
        path: "/cashier/orders",
        icon: AiOutlineContainer,
      },
      {
        title: t("direct sales"),
        path: "/cashier/direct-sales/cart",
        icon: MdOutlineShoppingCart,
      },
      {
        title: t("exit store"),
        component: StoreSelectionModal,
        icon: AiOutlineLogout,
      },
    ],
  },
  {
    path: "/cashier/direct-sales",
    children: [
      {
        title: t("shopping cart"),
        path: "/cashier/direct-sales/cart",
        icon: MdOutlineShoppingCart,
      },
      {
        title: t("shopping histories"),
        path: "/cashier/direct-sales/histories",
        icon: MdOutlineHistory,
      },
      {
        title: t("back to search"),
        path: "/cashier/search",
        icon: AiOutlineArrowLeft,
      },
    ],
  },
  {
    path: `/cashier/customers/${customerId}`,
    children: [
      {
        title: t("customer cart"),
        path: `/cashier/customers/${customerId}/cart`,
        icon: MdOutlineShoppingCart,
      },
      {
        title: t("customer information"),
        component: CustomerInformationModal,
        componentProps: {
          userId: customerId,
        },
        icon: AiOutlineUser,
      },
      {
        title: t("customer orders"),
        path: `/cashier/customers/${customerId}/orders`,
        icon: AiOutlineContainer,
      },
      {
        title: t("back to search"),
        path: "/cashier/search",
        icon: AiOutlineArrowLeft,
      },
    ],
  },
];

export default getMenus;
