import { Th, Tr, useColorModeValue } from "@chakra-ui/react";
import { Table } from "@tanstack/react-table";
import { Fragment } from "react";
import { useTranslation } from "react-i18next";

import { TableData } from "@/utils/dataTable";

export type Footer = {
  title?: string;
  column?: string;
  value?: number;
  formatter?: (value: number) => string;
};

interface TableFooterProps<T extends TableData> {
  table: Table<T>;
  size?: "xs" | "sm" | "md" | "lg";
  backgroundParent: HTMLElement | null;
  withAction: boolean;
  withSelection: boolean;
  isOnLeftEnd: boolean;
  isOnRightEnd: boolean;
  isNoDataShown: boolean;
  footerTotal?: Footer[];
}

const TableFooter = <T extends TableData>({
  table,
  size,
  backgroundParent,
  withAction,
  withSelection,
  isOnLeftEnd,
  isOnRightEnd,
  isNoDataShown,
  footerTotal,
}: TableFooterProps<T>) => {
  const { t } = useTranslation();

  const bgColor = useColorModeValue(
    "white",
    backgroundParent
      ? window.getComputedStyle(backgroundParent).backgroundColor
      : "transparent",
  );
  const borderColor = useColorModeValue("brand.100", "brand.700");

  return table.getFooterGroups().map((footerGroup) => (
    <Fragment key={footerGroup.id}>
      {footerTotal?.map((footer) => {
        return (
          <Tr key={footer.title}>
            {withSelection && (
              <Th
                left={0}
                p={size === "xs" ? 2 : 4}
                bg={bgColor}
                borderRight={!isOnLeftEnd && !isNoDataShown ? "1px" : "none"}
                borderColor={borderColor}
                boxShadow={
                  !isOnLeftEnd && !isNoDataShown
                    ? "0px 0px 15px rgba(0, 0, 0, 0.2), 0px 0px 15px rgba(0, 0, 0, 0.06)"
                    : "none"
                }
                clipPath="inset(0 -15px 0 0)"
                zIndex={1}
              />
            )}
            {footerGroup.headers.map((header, index) => {
              const { title, column, value = 0, formatter } = footer;
              const isFirstColumn =
                (index === 0 && !withAction) || (index === 1 && withAction);
              const isLastColumn = index === footerGroup.headers.length - 1;

              if (isFirstColumn) {
                return (
                  <Th
                    key={header.id}
                    p={size === "xs" ? 2 : 4}
                    bg={bgColor}
                    verticalAlign="middle"
                    colSpan={footerGroup.headers.length - 1}
                    fontSize="small"
                    py={4}
                  >
                    {title ?? t("total")}
                  </Th>
                );
              }

              if (isLastColumn) {
                const columnName = header.getContext().column.id;
                const accValue =
                  columnName === column
                    ? table
                        .getRowModel()
                        .rows.reduce(
                          (acc, row) => acc + (row.original[columnName] || 0),
                          0,
                        )
                    : 0;
                const total = value + accValue;

                return (
                  <Th
                    key={header.id}
                    p={size === "xs" ? 2 : 4}
                    bg={bgColor}
                    verticalAlign="middle"
                    fontSize="small"
                    py={2}
                    textAlign="right"
                    whiteSpace="nowrap"
                  >
                    {formatter ? formatter(total) : total}
                  </Th>
                );
              }

              return null;
            })}
            {withAction && (
              <Th
                pos={isNoDataShown ? "static" : "sticky"}
                right={0}
                bg={bgColor}
                borderLeft={!isOnRightEnd && !isNoDataShown ? "1px" : "none"}
                borderColor={borderColor}
                boxShadow={
                  !isOnRightEnd && !isNoDataShown
                    ? "0px 0px 15px rgba(0, 0, 0, 0.2), 0px 0px 15px rgba(0, 0, 0, 0.06)"
                    : "none"
                }
                clipPath="inset(0 0 0 -15px)"
                zIndex={1}
              />
            )}
          </Tr>
        );
      })}
    </Fragment>
  ));
};

export default TableFooter;
