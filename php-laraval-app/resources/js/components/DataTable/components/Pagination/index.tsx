import {
  Button,
  Flex,
  FlexProps,
  Icon,
  IconButton,
  Select,
  Text,
} from "@chakra-ui/react";
import { Table } from "@tanstack/react-table";
import { Trans, useTranslation } from "react-i18next";
import { LuChevronLeft, LuChevronsLeft } from "react-icons/lu";

import Input from "@/components/Input";

import { TableData } from "@/utils/dataTable";

interface PaginationProps<T extends TableData> extends FlexProps {
  table: Table<T>;
  totalEntries?: number;
  showEntries?: number[];
}

const Pagination = <T extends TableData>({
  table,
  totalEntries,
  showEntries = [25, 50, 100, 200],
  ...props
}: PaginationProps<T>) => {
  const { t } = useTranslation();

  const totalPage = table.getPageCount();
  const currentPageIndex = table.getState().pagination.pageIndex;

  const generatePages = () => {
    const pagesNum = Math.min(5, totalPage);
    let firstPage = Math.max(
      1,
      currentPageIndex + 1 - Math.floor(pagesNum / 2),
    );
    const lastPage = Math.min(totalPage, firstPage + pagesNum - 1);

    if (lastPage - firstPage + 1 < pagesNum) {
      firstPage = Math.max(1, lastPage - pagesNum + 1);
    }

    const pages: number[] = [];
    for (let i = firstPage; i <= lastPage; i++) {
      pages.push(i);
    }

    return pages;
  };

  return (
    <Flex align="center" {...props} gap={4}>
      <Flex align="center" gap={2}>
        <Text fontSize="xs">{t("show")}</Text>
        <Select
          size="sm"
          w="fit-content"
          rounded="md"
          focusBorderColor="brand.500"
          value={table.getState().pagination.pageSize}
          onChange={(e) => {
            table.setPageSize(Number(e.target.value));
            table.setPageIndex(0);
          }}
        >
          {showEntries.map((value) => (
            <option key={value} value={value}>
              {value}
            </option>
          ))}
        </Select>
        <Text fontSize="xs">
          <Trans
            i18nKey="pagination entries"
            values={{
              entries: totalEntries || table.getFilteredRowModel().rows.length,
            }}
          />
        </Text>
      </Flex>
      <Flex align="center">
        <IconButton
          variant="ghost"
          size="sm"
          aria-label={t("first page")}
          onClick={() => table.setPageIndex(0)}
          isDisabled={!table.getCanPreviousPage()}
        >
          <Icon as={LuChevronsLeft} />
        </IconButton>
        <IconButton
          variant="ghost"
          size="sm"
          aria-label={t("previous page")}
          onClick={() => table.setPageIndex(currentPageIndex - 1)}
          isDisabled={!table.getCanPreviousPage()}
        >
          <Icon as={LuChevronLeft} />
        </IconButton>
        {generatePages().map((page) => (
          <Button
            key={page}
            variant={page === currentPageIndex + 1 ? "outline" : "ghost"}
            size="sm"
            fontWeight={page === currentPageIndex + 1 ? "bold" : "normal"}
            onClick={
              page === currentPageIndex + 1
                ? undefined
                : () => table.setPageIndex(page - 1)
            }
          >
            {page}
          </Button>
        ))}
        <IconButton
          variant="ghost"
          size="sm"
          aria-label={t("next page")}
          onClick={() => table.setPageIndex(currentPageIndex + 1)}
          isDisabled={!table.getCanNextPage()}
        >
          <Icon as={LuChevronLeft} transform="scaleX(-1)" />
        </IconButton>
        <IconButton
          variant="ghost"
          size="sm"
          aria-label={t("last page")}
          onClick={() => table.setPageIndex(totalPage - 1)}
          isDisabled={!table.getCanNextPage()}
        >
          <Icon as={LuChevronsLeft} transform="scaleX(-1)" />
        </IconButton>
      </Flex>
      <Flex align="center" gap={2}>
        <Text fontSize="xs">{t("jump to")}</Text>
        <Input
          type="number"
          size="xs"
          fontSize="small"
          rounded="md"
          minW={8}
          container={{ w: "fit-content" }}
          min={1}
          max={totalPage}
          value={currentPageIndex + 1}
          onChangeDebounce={(value) =>
            table.setPageIndex(
              value && Number(value) <= totalPage ? Number(value) - 1 : 0,
            )
          }
        />
      </Flex>
    </Flex>
  );
};

export default Pagination;
