import { TFunction } from "i18next";

import AuthenticationLog from "@/types/authenticationLog";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<AuthenticationLog>(({ createData }) => [
    createData("user", {
      id: "userId",
      label: t("user"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          size: -1,
          include: ["user"],
          only: ["user.id", "user.fullname"],
          groupBy: "userId",
          sort: { "user.fullname": "asc" },
        },
        query: {
          queryKey: ["web", "log", "authentications", "json"],
          select: (response) => response.data.data.map(({ user }) => user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("ipAddress", {
      label: t("ip address"),
    }),
    createData("userAgent", {
      label: t("user agent"),
    }),
    createData("loginAt", {
      label: t("login at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("loginSuccessful", {
      label: t("login successful"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          size: -1,
          only: ["loginSuccessful"],
          groupBy: "loginSuccessful",
        },
        query: {
          queryKey: ["web", "log", "authentications", "json"],
          select: (response) =>
            response.data.data.map(({ loginSuccessful }) => loginSuccessful),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
    createData("logoutAt", {
      label: t("logout at"),
      display: "datetime",
      filterKind: "date",
      render: (originalValue, value) => (originalValue ? value : "-"),
    }),
  ]);

export default getColumns;
