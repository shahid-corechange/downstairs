import {
  Box,
  Button,
  Table as ChakraTable,
  Flex,
  Icon,
  Spacer,
  Spinner,
  Tbody,
  Td,
  Thead,
  Tr,
  useColorModeValue,
  useConst,
} from "@chakra-ui/react";
import {
  ColumnDef,
  ColumnFiltersState,
  PaginationState,
  Row,
  RowSelectionState,
  SortingState,
  flexRender,
  getCoreRowModel,
  getFacetedMinMaxValues,
  getFacetedRowModel,
  getFacetedUniqueValues,
  useReactTable,
} from "@tanstack/react-table";
import * as _ from "lodash-es";
import {
  CSSProperties,
  forwardRef,
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
import { useTranslation } from "react-i18next";
import { LuEraser, LuPlus } from "react-icons/lu";
import {
  ItemProps,
  TableComponents,
  TableProps,
  TableVirtuoso,
} from "react-virtuoso";

import { PagePagination } from "@/types/pagination";

import { TableData } from "@/utils/dataTable";
import { RequestQueryStringOptions } from "@/utils/request";

import { Dict, PageFilterItem } from "@/types";

import DateRangePicker, { DateRange } from "../DateRangePicker";
import Empty from "../Empty";
import ActionRow, { Actions } from "./components/ActionRow";
import Pagination from "./components/Pagination";
import SelectionAction, {
  SelectionActions,
} from "./components/SelectionAction";
import SelectionRow from "./components/SelectionRow";
import TableAction, { TableActions } from "./components/TableAction";
import TableHead from "./components/TableHead";
import { ActionModalType } from "./types";
import { getCellOptions } from "./utils/cellOptions";
import { getDefaultFilterCriteria } from "./utils/filterCriteria";
import { getDefaultFilterTransformer } from "./utils/filterValueTransformer";

const DEFAULT_PAGE_SIZE = 50;

const tableComponents: TableComponents = {
  TableHead: forwardRef((props, ref) => (
    <Thead {..._.omit(props, "style")} ref={ref} />
  )),
  TableBody: forwardRef((props, ref) => <Tbody {...props} ref={ref} />),
};

export interface ServerSideDataTableProps<T extends TableData> {
  data: T[];
  columns: ColumnDef<T>[];
  tableActions?: TableActions<T>;
  selectionActions?: SelectionActions<T>;
  actions?: Actions<T>;
  title?: string;
  size?: "sm" | "md" | "lg";
  maxHeight?: CSSProperties["maxHeight"];
  increaseViewportBy?: number | { top: number; bottom: number };
  sort?: Record<string, "asc" | "desc">;
  filters?: PageFilterItem[];
  orFilters?: PageFilterItem[][];
  pagination?: PagePagination;
  sortable?: boolean;
  filterable?: boolean;
  paginatable?: boolean;
  clearable?: boolean;
  isFetching?: boolean;
  useWindowScroll?: boolean;
  withCreate?: boolean;
  withEdit?: boolean | ((row: Row<T>) => boolean);
  withDelete?: boolean | ((row: Row<T>) => boolean);
  withRestore?: boolean | ((row: Row<T>) => boolean);
  withDateRange?: boolean;
  dateDefaultFilter?: DateRange;
  minDate?: Date;
  maxDate?: Date;
  showEntries?: number[];
  fetchFn?: (options: Partial<RequestQueryStringOptions<T>>) => void;
  onCreate?: () => void;
  onEdit?: (row: Row<T>) => void;
  onDelete?: (row: Row<T>) => void;
  onRestore?: (row: Row<T>) => void;
  onClearFilters?: () => void;
  onChangeDate?: (dates: DateRange) => void;
}

const ServerSideDataTable = <T extends TableData>({
  data,
  columns,
  actions,
  selectionActions = [],
  title,
  sort,
  filters: filtersProps,
  orFilters,
  pagination: paginationProps,
  fetchFn,
  onCreate,
  onEdit,
  onDelete,
  onRestore,
  onClearFilters,
  onChangeDate,
  tableActions = [],
  dateDefaultFilter,
  minDate,
  maxDate,
  size = "sm",
  maxHeight = 500,
  sortable = true,
  filterable = true,
  paginatable = true,
  clearable = true,
  isFetching = false,
  withDateRange = false,
  withCreate = true,
  withEdit = true,
  withDelete = true,
  withRestore = true,
  showEntries,
  ...props
}: ServerSideDataTableProps<T>) => {
  const { t } = useTranslation();

  const combinedFilters = useMemo(
    () => [...(filtersProps ?? []), ...(orFilters ?? []).flat()],
    [filtersProps, orFilters],
  );

  const firstSorting = useConst(
    sort
      ? Object.entries(sort).map((value) => ({
          id: value[0],
          desc: value[1] === "desc",
        }))
      : [],
  );
  const firstColumnFilters = useConst(
    combinedFilters.map(({ key, value }) => ({ id: key, value })),
  );
  const firstColumnFiltersClause = useConst(() => {
    const result: Record<string, "and" | "or"> = {};

    if (filtersProps) {
      filtersProps.forEach((filter) => {
        result[filter.key] = "and";
      });
    }

    if (orFilters) {
      orFilters.forEach((filter) => {
        filter.forEach((item) => {
          result[item.key] = "or";
        });
      });
    }

    return result;
  });

  const firstPagination = useConst({
    pageIndex: paginationProps ? paginationProps.currentPage - 1 : 0,
    pageSize: paginationProps?.size || DEFAULT_PAGE_SIZE,
  });

  const [sorting, setSorting] = useState<SortingState>(firstSorting);
  const [columnFilters, setColumnFilters] =
    useState<ColumnFiltersState>(firstColumnFilters);
  const [columnFiltersClause, setColumnFiltersClause] = useState<
    Record<string, "and" | "or">
  >(firstColumnFiltersClause);
  const [pagination, setPagination] =
    useState<PaginationState>(firstPagination);
  const [rowSelection, setRowSelection] = useState<RowSelectionState>({});
  const [isLoading, setIsLoading] = useState(false);

  const initialRender = useRef(true);
  const withAction =
    (actions && actions.length > 0) || onEdit || onDelete || onRestore;
  const withSelection = selectionActions && selectionActions.length > 0;

  const [backgroundParent, setBackgroundParent] = useState<HTMLElement | null>(
    null,
  );
  const [isOnRightEnd, setIsOnRightEnd] = useState(false);
  const [isOnLeftEnd, setIsOnLeftEnd] = useState(true);
  const [totalListHeight, setTotalListHeight] = useState(0);

  const tableRef = useRef<HTMLTableElement>(null);

  const hoverBgColor = useColorModeValue("brand.50", "brand.900");

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getFacetedRowModel: getFacetedRowModel(),
    getFacetedUniqueValues: getFacetedUniqueValues(),
    getFacetedMinMaxValues: getFacetedMinMaxValues(),
    manualFiltering: true,
    manualSorting: true,
    manualPagination: true,
    pageCount: paginationProps?.lastPage || 1,
    enableSortingRemoval: false,
    enableRowSelection: withSelection,
    onSortingChange: (updater) => {
      setSorting(updater);
      setPagination((prev) => ({ ...prev, pageIndex: 0 }));
    },
    onColumnFiltersChange: (updater) => {
      setColumnFilters(updater);
      setPagination((prev) => ({ ...prev, pageIndex: 0 }));
    },
    onPaginationChange: setPagination,
    onRowSelectionChange: setRowSelection,
    state: {
      sorting,
      columnFilters,
      pagination,
      rowSelection,
    },
    meta: {
      setColumnFilterClause: (id, clause) => {
        setColumnFiltersClause((prev) => ({ ...prev, [id]: clause }));
      },
    },
  });

  const { rows } = table.getRowModel();
  const selectedRows = table.getSelectedRowModel().rows;

  const Table = useMemo(
    () =>
      ({ style, ...props }: TableProps) => (
        <ChakraTable
          {...props}
          ref={tableRef}
          size={size}
          style={{
            ...style,
            width: "100%",
            borderCollapse: "separate",
            borderSpacing: 0,
          }}
        />
      ),
    [tableRef],
  );

  const TableRow = useMemo(
    () => (props: ItemProps<unknown>) => {
      if (isLoading) {
        return (
          <Tr {...props}>
            <Td
              colSpan={columns.length + 1}
              py={4}
              fontSize="small"
              textAlign="center"
            >
              <Box mb={2}>
                <Spinner size="sm" />
              </Box>
              {t("please wait")}
            </Td>
          </Tr>
        );
      } else if (!isLoading && rows.length === 0) {
        return (
          <Tr {...props}>
            <Td colSpan={columns.length + 1} py={4}>
              <Empty />
            </Td>
          </Tr>
        );
      }

      const index = props["data-index"];
      const row = rows[index];

      return (
        <Tr
          {...props}
          sx={{
            "&:hover td": {
              bg: hoverBgColor,
            },
          }}
        >
          {withSelection && (
            <SelectionRow
              row={row}
              backgroundParent={backgroundParent}
              actions={actions}
              isOnLeftEnd={isOnLeftEnd}
            />
          )}

          {row.getVisibleCells().map((cell) => (
            <Td
              key={cell.id}
              {...getCellOptions(cell)}
              fontSize="small"
              textAlign={
                ["currency", "number"].includes(
                  cell.column.columnDef.meta?.display ?? "",
                )
                  ? "right"
                  : "left"
              }
            >
              {flexRender(cell.column.columnDef.cell, cell.getContext())}
            </Td>
          ))}
          {withAction && (
            <ActionRow
              row={row}
              backgroundParent={backgroundParent}
              actions={actions}
              withEdit={
                !!onEdit &&
                (typeof withEdit === "boolean" ? withEdit : withEdit(row))
              }
              withDelete={
                !!onDelete &&
                (typeof withDelete === "boolean" ? withDelete : withDelete(row))
              }
              withRestore={
                !!onRestore &&
                (typeof withRestore === "boolean"
                  ? withRestore
                  : withRestore(row))
              }
              isOnRightEnd={isOnRightEnd}
              onAction={(modal) => handleAction(modal, row)}
            />
          )}
        </Tr>
      );
    },
    [
      isLoading,
      rows,
      isOnRightEnd,
      isOnLeftEnd,
      backgroundParent,
      hoverBgColor,
    ],
  );

  const filters = useMemo(
    () =>
      columnFilters.reduce<Dict>((acc, filter) => {
        let operator: string;
        let value: unknown;

        const column = table
          .getAllColumns()
          .find((item) => item.id === filter.id);

        if (!column) {
          const columnFilter = combinedFilters.find(
            (item) => item.key === filter.id,
          );

          if (!columnFilter) {
            return acc;
          }

          operator = columnFilter.criteria;
          value = columnFilter.value;
        } else {
          const { display, filterCriteria, filterValueTransformer } =
            column.columnDef.meta || {};

          if (
            filterCriteria &&
            (typeof filter.value === "string" ||
              typeof filter.value === "number" ||
              typeof filter.value === "boolean")
          ) {
            operator =
              typeof filterCriteria === "function"
                ? filterCriteria(filter.value)
                : filterCriteria;
          } else {
            operator = getDefaultFilterCriteria(column);
          }

          if (
            filterValueTransformer &&
            (typeof filter.value === "string" ||
              typeof filter.value === "number" ||
              typeof filter.value === "boolean")
          ) {
            value = filterValueTransformer(filter.value);
          } else {
            value = getDefaultFilterTransformer(filter.value, display);
          }
        }

        if (columnFiltersClause[filter.id] === "or") {
          if (!acc.orFilters) {
            acc.orFilters = [{}];
          }

          if (!acc.orFilters[0][operator]) {
            acc.orFilters[0][operator] = {};
          }

          acc.orFilters[0][operator][filter.id] = value;
        } else {
          if (!acc.filter) {
            acc.filter = {};
          }

          if (!acc.filter[operator]) {
            acc.filter[operator] = {};
          }

          acc.filter[operator][filter.id] = value;
        }

        return acc;
      }, {}),

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [columnFilters, columnFiltersClause],
  );

  useEffect(() => {
    if (
      initialRender.current &&
      _.isEqual(sorting, firstSorting) &&
      _.isEqual(columnFilters, firstColumnFilters) &&
      pagination.pageIndex === firstPagination.pageIndex &&
      pagination.pageSize === firstPagination.pageSize
    ) {
      return;
    }

    initialRender.current = false;
    setIsLoading(true);
    fetchFn?.({
      ...filters,
      sort: sorting.reduce(
        (acc, value) =>
          Object.assign(acc, {
            [value.id]: value.desc ? "desc" : "asc",
          }),
        {} as RequestQueryStringOptions<T>["sort"],
      ),
      page: pagination.pageIndex + 1,
      size: pagination.pageSize,
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filters, sorting, pagination.pageIndex, pagination.pageSize]);

  useEffect(() => {
    if (data && !isFetching) {
      setIsLoading(false);
    }
  }, [data, isFetching]);

  useEffect(() => {
    setIsLoading(isFetching);
  }, [isFetching]);

  useEffect(() => {
    if (tableRef.current) {
      const scroller = tableRef.current.parentElement;

      let parentWithBackground = tableRef.current.parentElement;

      while (parentWithBackground) {
        const bgColor =
          window.getComputedStyle(parentWithBackground).backgroundColor;

        if (bgColor !== "rgba(0, 0, 0, 0)" && bgColor !== "transparent") {
          break;
        }

        parentWithBackground = parentWithBackground.parentElement;
      }

      setBackgroundParent(parentWithBackground);

      if (scroller) {
        setIsOnRightEnd(scroller.scrollWidth <= scroller.clientWidth);
      }
    }
  }, [tableRef.current]);

  const handleClearFilters = () => {
    setColumnFilters([]);
    setSorting([]);
    setPagination({ pageIndex: 0, pageSize: DEFAULT_PAGE_SIZE });

    onClearFilters?.();
  };

  const handleAction = (modal: ActionModalType, row: Row<T>) => {
    switch (modal) {
      case "edit":
        onEdit?.(row);
        break;
      case "delete":
        onDelete?.(row);
        break;
      case "restore":
        onRestore?.(row);
        break;
    }
  };

  return (
    <Flex direction="column">
      <Flex align="center" mb={4} gap={4}>
        {withDateRange && (
          <DateRangePicker
            onChange={onChangeDate}
            defaultValue={dateDefaultFilter}
            submitButtonLabel={t("filter")}
            minDate={minDate}
            maxDate={maxDate}
          />
        )}
        <Spacer />

        {clearable && (sorting.length > 0 || columnFilters.length > 0) && (
          <Button
            variant="outline"
            size="sm"
            fontSize="small"
            leftIcon={<Icon as={LuEraser} boxSize={4} />}
            onClick={handleClearFilters}
          >
            {t("clear filters")}
          </Button>
        )}

        {onCreate && withCreate && (
          <Button
            size="sm"
            fontSize="small"
            leftIcon={<Icon as={LuPlus} boxSize={4} />}
            onClick={onCreate}
          >
            {title ?? t("data")}
          </Button>
        )}

        {withSelection &&
          Object.keys(rowSelection).length > 0 &&
          selectionActions.map((action, i) => (
            <SelectionAction key={i} rows={selectedRows} {...action} />
          ))}

        {tableActions.map((action, i) => (
          <TableAction key={i} table={table} {...action} />
        ))}
      </Flex>
      <TableVirtuoso
        totalCount={isLoading || rows.length === 0 ? 1 : rows.length}
        increaseViewportBy={100}
        style={
          props.useWindowScroll
            ? { overflowX: "auto", overflowY: "hidden" }
            : { height: totalListHeight, maxHeight }
        }
        components={{
          ...tableComponents,
          Table,
          TableRow,
        }}
        fixedHeaderContent={() => (
          <TableHead
            table={table}
            data={data}
            filtersClause={columnFiltersClause}
            backgroundParent={backgroundParent}
            sortable={sortable}
            filterable={filterable}
            withAction={!!withAction}
            withSelection={withSelection}
            isNoDataShown={rows.length === 0 || isLoading}
            isOnLeftEnd={isOnLeftEnd}
            isOnRightEnd={isOnRightEnd}
            serverSide
          />
        )}
        fixedFooterContent={() => (
          <Tr>
            <Td borderBottom="none"></Td>
          </Tr>
        )}
        onScroll={({ currentTarget }) => {
          setIsOnRightEnd(
            currentTarget.scrollLeft >=
              currentTarget.scrollWidth - currentTarget.offsetWidth,
          );
          setIsOnLeftEnd(currentTarget.scrollLeft === 0);
        }}
        totalListHeightChanged={(height) => setTotalListHeight(height)}
        {...props}
      />
      {paginatable && (
        <Pagination
          table={table}
          totalEntries={paginationProps?.total}
          showEntries={showEntries}
          mt={2}
        />
      )}
    </Flex>
  );
};

export default ServerSideDataTable;
