import { Flex, FlexProps, Icon } from "@chakra-ui/react";
import { Column } from "@tanstack/react-table";
import { AiOutlineCaretUp } from "react-icons/ai";

import { TableData } from "@/utils/dataTable";

interface SortIndicatorProps<T extends TableData> extends FlexProps {
  column: Column<T>;
}

const SortIndicator = <T extends TableData>({
  column,
  ...props
}: SortIndicatorProps<T>) => {
  return (
    <Flex
      direction="column"
      transition="opacity 0.2s"
      opacity={column.getIsSorted() ? 1 : 0}
      _groupHover={{ opacity: 1 }}
      {...props}
    >
      <Icon
        as={AiOutlineCaretUp}
        boxSize={2.5}
        color={column.getIsSorted() === "asc" ? "inherit" : "gray.400"}
        _dark={{
          color: column.getIsSorted() === "asc" ? "inherit" : "whiteAlpha.400",
        }}
      />
      <Icon
        as={AiOutlineCaretUp}
        boxSize={2.5}
        color={column.getIsSorted() === "desc" ? "inherit" : "gray.400"}
        _dark={{
          color: column.getIsSorted() === "desc" ? "inherit" : "whiteAlpha.400",
        }}
        transform="rotate(180deg)"
      />
    </Flex>
  );
};

export default SortIndicator;
