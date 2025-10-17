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
  Tfoot,
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
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
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
import { LuEraser, LuPlus, LuSearch } from "react-icons/lu";
import {
  ItemProps,
  TableComponents,
  TableProps,
  TableVirtuoso,
} from "react-virtuoso";

import { TableData } from "@/utils/dataTable";

import DateRangePicker, { DateRange } from "../DateRangePicker";
import Empty from "../Empty";
import Input from "../Input";
import ActionRow, { Actions } from "./components/ActionRow";
import Pagination from "./components/Pagination";
import SelectionAction, {
  SelectionActions,
} from "./components/SelectionAction";
import SelectionRow from "./components/SelectionRow";
import TableAction, { TableActions } from "./components/TableAction";
import TableFooter, { Footer } from "./components/TableFooter";
import TableHead from "./components/TableHead";
import { ActionModalType } from "./types";
import { getCellOptions } from "./utils/cellOptions";

const tableComponents: TableComponents = {
  TableHead: forwardRef((props, ref) => (
    <Thead {..._.omit(props, "style")} ref={ref} />
  )),
  TableBody: forwardRef((props, ref) => <Tbody {...props} ref={ref} />),
  TableFoot: forwardRef((props, ref) => (
    <Tfoot {..._.omit(props, "style")} ref={ref} />
  )),
};

export interface ClientSideDataTableProps<T extends TableData> {
  data: T[];
  columns: ColumnDef<T>[];
  filters?: ColumnFiltersState;
  tableActions?: TableActions<T>;
  selectionActions?: SelectionActions<T>;
  actions?: Actions<T>;
  footerTotal?: Footer[];
  title?: string;
  size?: "xs" | "sm" | "md" | "lg";
  maxHeight?: CSSProperties["maxHeight"];
  increaseViewportBy?: number | { top: number; bottom: number };
  searchable?: boolean;
  sortable?: boolean;
  filterable?: boolean;
  paginatable?: boolean;
  clearable?: boolean;
  isLoading?: boolean;
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
  onCreate?: () => void;
  onEdit?: (row: Row<T>) => void;
  onDelete?: (row: Row<T>) => void;
  onRestore?: (row: Row<T>) => void;
  onChangeDate?: (dates: DateRange) => void;
}

const ClientSideDataTable = <T extends TableData>({
  data,
  columns,
  filters: defaultFilters,
  actions,
  footerTotal,
  selectionActions = [],
  title,
  onCreate,
  onEdit,
  onDelete,
  onRestore,
  onChangeDate,
  tableActions = [],
  dateDefaultFilter,
  minDate,
  maxDate,
  size = "sm",
  maxHeight = 500,
  searchable = true,
  sortable = true,
  filterable = true,
  paginatable = true,
  clearable = true,
  isLoading = false,
  withDateRange = false,
  withCreate = true,
  withEdit = true,
  withDelete = true,
  withRestore = true,
  showEntries,
  ...props
}: ClientSideDataTableProps<T>) => {
  const { t } = useTranslation();

  const initialState = useConst({
    sorting: [],
    columnFilters: defaultFilters ?? [],
    globalFilter: "",
    pagination: {
      pageIndex: 0,
      pageSize: 50,
    },
  });
  const withAction =
    (actions && actions.length > 0) || onEdit || onDelete || onRestore;
  const withSelection = selectionActions && selectionActions.length > 0;

  const [sorting, setSorting] = useState<SortingState>(initialState.sorting);
  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>(
    initialState.columnFilters,
  );
  const [globalFilter, setGlobalFilter] = useState(initialState.globalFilter);
  const [rowSelection, setRowSelection] = useState<RowSelectionState>({});
  const [pagination, setPagination] = useState<PaginationState>(
    initialState.pagination,
  );
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
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getFacetedRowModel: getFacetedRowModel(),
    getFacetedUniqueValues: getFacetedUniqueValues(),
    getFacetedMinMaxValues: getFacetedMinMaxValues(),
    getPaginationRowModel: getPaginationRowModel(),
    enableSortingRemoval: false,
    enableRowSelection: withSelection,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    onGlobalFilterChange: setGlobalFilter,
    onPaginationChange: setPagination,
    onRowSelectionChange: setRowSelection,
    state: {
      pagination,
      sorting,
      columnFilters,
      globalFilter,
      rowSelection,
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
          size={size === "xs" ? "sm" : size}
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
              size={size}
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
              p={size === "xs" ? 2 : 4}
            >
              {flexRender(cell.column.columnDef.cell, cell.getContext())}
            </Td>
          ))}
          {withAction && (
            <ActionRow
              row={row}
              size={size}
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
      rows,
      isLoading,
      isOnRightEnd,
      isOnLeftEnd,
      backgroundParent,
      hoverBgColor,
    ],
  );

  const showClearFilters = useMemo(
    () =>
      clearable &&
      (!_.isEqual(sorting, initialState.sorting) ||
        !_.isEqual(
          columnFilters,
          initialState.columnFilters ||
            globalFilter !== initialState.globalFilter,
        )),
    [clearable, sorting, columnFilters, globalFilter, initialState],
  );

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

  const handleClearFilters = () => {
    setSorting(initialState.sorting);
    setColumnFilters(initialState.columnFilters);
    setGlobalFilter(initialState.globalFilter);
    setPagination(initialState.pagination);
  };

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

  return (
    <Flex direction="column">
      <Flex align="center" mb={4} gap={4}>
        {searchable && (
          <Input
            size="xs"
            placeholder={t("search")}
            container={{ width: "auto", flex: 0.25 }}
            prefix={<Icon as={LuSearch} color="gray.500" />}
            onChangeDebounce={(value) => setGlobalFilter(String(value))}
            value={globalFilter}
          />
        )}
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

        {showClearFilters && (
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
        style={{
          paddingBottom: 100,
          ...(props.useWindowScroll
            ? { overflowX: "auto", overflowY: "hidden" }
            : { height: totalListHeight, maxHeight }),
        }}
        components={{
          ...tableComponents,
          Table,
          TableRow,
        }}
        fixedHeaderContent={() => (
          <TableHead
            table={table}
            size={size}
            data={data}
            backgroundParent={backgroundParent}
            sortable={sortable}
            filterable={filterable}
            withAction={!!withAction}
            withSelection={withSelection}
            isNoDataShown={rows.length === 0 || isLoading}
            isOnLeftEnd={isOnLeftEnd}
            isOnRightEnd={isOnRightEnd}
          />
        )}
        fixedFooterContent={() =>
          footerTotal && (
            <TableFooter
              table={table}
              size={size}
              backgroundParent={backgroundParent}
              withAction={!!withAction}
              withSelection={withSelection}
              isNoDataShown={rows.length === 0 || isLoading}
              isOnLeftEnd={isOnLeftEnd}
              isOnRightEnd={isOnRightEnd}
              footerTotal={footerTotal}
            />
          )
        }
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
        <Pagination table={table} mt={2} showEntries={showEntries} />
      )}
    </Flex>
  );
};

export default ClientSideDataTable;
