import { Flex } from "@chakra-ui/react";
import { useQuery } from "@tanstack/react-query";
import { Column } from "@tanstack/react-table";
import * as _ from "lodash-es";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Select from "@/components/Select";

import { TableData } from "@/utils/dataTable";
import { createQueryString } from "@/utils/request";

interface FilterRowProps<TData extends TableData> {
  data: TData[];
  column: Column<TData>;
  serverSide?: boolean;
}

const FilterRow = <TData extends TableData>({
  data,
  column,
  serverSide,
}: FilterRowProps<TData>) => {
  const { t } = useTranslation();
  const originalValue = column.getFilterValue();
  const value = (originalValue ?? "") as string;
  const {
    display,
    autocompleteFreeMode,
    filterKind,
    fetchOptions,
    options: optionsProp,
    renderOptions,
    renderOptionsLabel,
    getOptionsValue,
  } = column.columnDef.meta ?? {};

  const optionsQuery = useQuery({
    ...fetchOptions?.query,
    queryKey: [
      ...(fetchOptions?.query.queryKey ?? []),
      createQueryString(fetchOptions?.request),
    ],
    enabled: !!(
      serverSide &&
      !optionsProp &&
      fetchOptions &&
      fetchOptions.query
    ),
  });

  const options = useMemo(() => {
    if (filterKind && ["select", "autocomplete"].includes(filterKind)) {
      const values = optionsProp
        ? optionsProp
        : serverSide && fetchOptions
        ? optionsQuery.data
        : Array.from(column.getFacetedUniqueValues().keys()).sort();

      if (!values || values.length === 0) {
        return [];
      }

      if (values[0] === null || typeof values[0] !== "object") {
        const mergedUniqueValues = _.uniq(values).sort((a, b) => a - b);

        return renderOptions
          ? renderOptions(mergedUniqueValues)
          : mergedUniqueValues.map((value) => ({
              label: renderOptionsLabel
                ? renderOptionsLabel(value)
                : `${value}`,
              value: getOptionsValue ? getOptionsValue(value) : value,
            }));
      }

      const keys = Object.keys(values[0]);
      if (
        keys.length === 2 &&
        keys.includes("label") &&
        keys.includes("value")
      ) {
        return values;
      }

      const mergedUniqueValues = _.uniqWith(
        values.flatMap((value) => value),
        _.isEqual,
      ).sort((a, b) => b - a);
      return renderOptions
        ? renderOptions(mergedUniqueValues)
        : mergedUniqueValues.map((value) => ({
            label: renderOptionsLabel ? renderOptionsLabel(value) : `${value}`,
            value: getOptionsValue ? getOptionsValue(value) : `${value}`,
          }));
    }
    return [];

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    filterKind,
    column,
    data,
    optionsProp,
    optionsQuery.data,
    renderOptionsLabel,
  ]);

  switch (filterKind) {
    case "select":
      return (
        <Select
          variant="flushed"
          size="sm"
          placeholder={t("filter by", { field: column.columnDef.header })}
          fontSize="small"
          value={value}
          onChange={(e) => column.setFilterValue(e.target.value)}
          textTransform="capitalize"
          multiple={display === "list"}
        >
          <option value="">{t("all")}</option>
          {options.map((option) => (
            <option key={option.label} value={option.value}>
              {option.label}
            </option>
          ))}
        </Select>
      );
    case "autocomplete":
      return (
        <Autocomplete
          options={options}
          variant="flushed"
          placeholder={t("filter by", { field: column.columnDef.header })}
          size="sm"
          fontSize="small"
          value={value}
          onChange={
            !autocompleteFreeMode
              ? (e) => column.setFilterValue(e.target.value)
              : undefined
          }
          onChangeDebounce={
            autocompleteFreeMode
              ? (value) => column.setFilterValue(value)
              : undefined
          }
          freeMode={autocompleteFreeMode}
          isLoading={
            serverSide && !optionsProp && fetchOptions && optionsQuery.isLoading
          }
          {...(display === "list" ? { multiple: true } : { allowEmpty: true })}
        />
      );
    case "date":
      return (
        <Input
          type="date"
          variant="flushed"
          size="sm"
          fontSize="small"
          value={value}
          onChangeDebounce={(value) => column.setFilterValue(value)}
        />
      );
    case "time":
      return (
        <Input
          type="time"
          step="900"
          variant="flushed"
          size="sm"
          fontSize="small"
          value={value}
          onChangeDebounce={(value) => column.setFilterValue(value)}
        />
      );
    case "range":
      return (
        <Flex gap={4}>
          <Input
            type="number"
            variant="flushed"
            size="sm"
            fontSize="small"
            placeholder={t("min value", {
              value: column.getFacetedMinMaxValues()?.[0] ?? 0,
            })}
            min={Number(column.getFacetedMinMaxValues()?.[0] ?? "")}
            max={Number(column.getFacetedMinMaxValues()?.[1] ?? "")}
            value={(originalValue as [number, number])?.[0] ?? ""}
            onChangeDebounce={(value) =>
              column.setFilterValue((old: [number, number]) => [
                value,
                old?.[1],
              ])
            }
          />
          <Input
            type="number"
            variant="flushed"
            size="sm"
            fontSize="small"
            placeholder={t("max value", {
              value: column.getFacetedMinMaxValues()?.[1] ?? 0,
            })}
            min={Number(column.getFacetedMinMaxValues()?.[0] ?? "")}
            max={Number(column.getFacetedMinMaxValues()?.[1] ?? "")}
            value={(originalValue as [number, number])?.[1] ?? ""}
            onChangeDebounce={(value) =>
              column.setFilterValue((old: [number, number]) => [
                old?.[0],
                value,
              ])
            }
          />
        </Flex>
      );
    default:
      return (
        <Input
          variant="flushed"
          size="sm"
          fontSize="small"
          placeholder={t("filter by", { field: column.columnDef.header })}
          value={value}
          onChangeDebounce={(value) => column.setFilterValue(value)}
        />
      );
  }
};
export default FilterRow;
