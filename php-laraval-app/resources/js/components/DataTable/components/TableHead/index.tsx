import { Box, Flex, Th, Tr, useColorModeValue } from "@chakra-ui/react";
import { Table, flexRender } from "@tanstack/react-table";
import { Fragment } from "react";

import { TableData } from "@/utils/dataTable";

import FilterRow from "../FilterRow";
import SortIndicator from "../SortIndicator";

interface TableHeadProps<T extends TableData> {
  table: Table<T>;
  size?: "xs" | "sm" | "md" | "lg";
  data: T[];
  backgroundParent: HTMLElement | null;
  sortable: boolean;
  filterable: boolean;
  withAction: boolean;
  withSelection: boolean;
  filtersClause?: Record<string, "and" | "or">;
  isNoDataShown?: boolean;
  isOnRightEnd?: boolean;
  isOnLeftEnd?: boolean;
  serverSide?: boolean;
}

const TableHead = <T extends TableData>({
  table,
  size,
  data,
  backgroundParent,
  sortable,
  filterable,
  withAction,
  withSelection,
  isNoDataShown = false,
  isOnRightEnd = false,
  isOnLeftEnd = false,
  serverSide = false,
}: TableHeadProps<T>) => {
  const bgColor = useColorModeValue(
    "white",
    backgroundParent
      ? window.getComputedStyle(backgroundParent).backgroundColor
      : "transparent",
  );
  const borderColor = useColorModeValue("brand.100", "brand.700");
  // const filterClauseOptions = useConst([
  //   { label: "AND", value: "and" },
  //   { label: "OR", value: "or" },
  // ]);

  return table.getHeaderGroups().map((headerGroup) => (
    <Fragment key={headerGroup.id}>
      <Tr>
        {withSelection && (
          <Th
            pos={isNoDataShown ? "static" : "sticky"}
            rowSpan={filterable ? 2 : 1}
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
            borderBottom={filterable ? "none" : undefined}
            zIndex={1}
          />
        )}
        {headerGroup.headers.map((header) => (
          <Th
            key={header.id}
            p={size === "xs" ? 2 : 4}
            bg={bgColor}
            verticalAlign="middle"
            borderBottom={filterable ? "none" : undefined}
          >
            <Flex
              role="group"
              py={2}
              flexDirection={
                ["currency", "number"].includes(
                  header.column.columnDef.meta?.display ?? "",
                )
                  ? "row-reverse"
                  : "row"
              }
              textAlign={
                ["currency", "number"].includes(
                  header.column.columnDef.meta?.display ?? "",
                )
                  ? "right"
                  : "left"
              }
              gap={4}
              align="center"
              justify="space-between"
              fontSize="small"
              cursor={
                sortable && header.column.getCanSort() ? "pointer" : "auto"
              }
              userSelect={header.column.getCanSort() ? "none" : "auto"}
              onClick={
                sortable ? header.column.getToggleSortingHandler() : undefined
              }
            >
              {flexRender(header.column.columnDef.header, header.getContext())}
              {sortable && header.column.getCanSort() && (
                <SortIndicator column={header.column} />
              )}
            </Flex>
          </Th>
        ))}
        {withAction && (
          <Th
            pos={isNoDataShown ? "static" : "sticky"}
            rowSpan={filterable ? 2 : 1}
            right={0}
            p={size === "xs" ? 2 : 4}
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
      {filterable && (
        <Tr>
          {headerGroup.headers.map((header) => (
            <Th key={header.id} bg={bgColor}>
              {header.column.getCanFilter() && (
                <Box mb={4}>
                  {/* {serverSide && Object.keys(filtersClause).length > 0 && (
                    <Autocomplete
                      variant="flushed"
                      options={filterClauseOptions}
                      value={filtersClause[header.column.id] ?? "and"}
                      onChange={(e) =>
                        table.options.meta?.setColumnFilterClause(
                          header.column.id,
                          e.target.value as "and" | "or",
                        )
                      }
                      size="sm"
                      fontSize="small"
                      minW={8}
                    />
                  )} */}
                  <FilterRow
                    data={data}
                    column={header.column}
                    serverSide={serverSide}
                  />
                </Box>
              )}
            </Th>
          ))}
        </Tr>
      )}
    </Fragment>
  ));
};

export default TableHead;
