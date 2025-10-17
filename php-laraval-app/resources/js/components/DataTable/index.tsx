import * as _ from "lodash-es";

import { TableData } from "@/utils/dataTable";

import ClientSideDataTable, { ClientSideDataTableProps } from "./ClientSide";
import ServerSideDataTable, { ServerSideDataTableProps } from "./ServerSide";

interface DataTableVariantProps<T extends TableData> {
  clientSide: ClientSideDataTableProps<T> & { serverSide?: false };
  serverSide: ServerSideDataTableProps<T> & { serverSide: true };
}

type DataTableProps<T extends TableData> =
  DataTableVariantProps<T>[keyof DataTableVariantProps<T>];

const DataTable = <T extends TableData>(props: DataTableProps<T>) => {
  return props.serverSide ? (
    <ServerSideDataTable {..._.omit(props, ["serverSide"])} />
  ) : (
    <ClientSideDataTable {..._.omit(props, ["serverSide"])} />
  );
};

export default DataTable;
