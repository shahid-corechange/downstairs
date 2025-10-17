import {
  Box,
  Button,
  Flex,
  List,
  ListItem,
  Spinner,
  Text,
} from "@chakra-ui/react";
import { forwardRef, useState } from "react";
import { Trans, useTranslation } from "react-i18next";
import { Virtuoso } from "react-virtuoso";

import { AutocompleteOption } from "@/components/Autocomplete/types";
import Empty from "@/components/Empty";

import { useFilterOptions } from "@/hooks/autocomplete";

import { renderHighlightedText } from "@/utils/autocomplete";
import { transparentize } from "@/utils/color";

export interface AutocompleteOptionsProps {
  options: (string | AutocompleteOption)[];
  onChange: (optionIndex: number) => void;
  onClose: () => void;
  activeIndex?: number;
  filter?: string;
  renderOption?: (
    option: AutocompleteOptionsProps["options"][number],
    filter?: string,
  ) => React.ReactNode;
  isLoading?: boolean;
  stickyFooterOption?: (onClose: () => void) => React.ReactNode;
}

const AutocompleteOptions = ({
  options,
  activeIndex,
  filter,
  isLoading,
  onChange,
  onClose,
  renderOption,
  stickyFooterOption,
}: AutocompleteOptionsProps) => {
  const { t } = useTranslation();
  const [listHeight, setListHeight] = useState(1);

  const filteredOptionIndexes = useFilterOptions(options, filter);

  return (
    <Box>
      <Virtuoso
        totalCount={
          isLoading || filteredOptionIndexes.length === 0
            ? 1
            : filteredOptionIndexes.length
        }
        components={{
          List: forwardRef((props, ref) => (
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            <List ref={ref} {...props} />
          )),
          Item: (props) => {
            if (isLoading) {
              return (
                <ListItem {...props} role="listitem">
                  <Flex align="center" justify="center" p={3}>
                    <Spinner size="md" color="gray.500" />
                  </Flex>
                </ListItem>
              );
            } else if (!isLoading && filteredOptionIndexes.length === 0) {
              return (
                <ListItem {...props} role="listitem">
                  <Empty
                    py={6}
                    px={3}
                    description={
                      filter ? (
                        <Text
                          color="gray.500"
                          fontSize="small"
                          textAlign="center"
                        >
                          <Trans
                            i18nKey="no results found with filter"
                            values={{ filter }}
                          />
                        </Text>
                      ) : (
                        <Text
                          color="gray.500"
                          fontSize="small"
                          textAlign="center"
                        >
                          {t("no results found")}
                        </Text>
                      )
                    }
                  />
                </ListItem>
              );
            }

            const index = props["data-index"];

            return (
              <ListItem
                {...props}
                role="listitem"
                data-optionindex={filteredOptionIndexes[index]}
                w="full"
              >
                <Button
                  variant="ghost"
                  size="md"
                  w="full"
                  display="inline-block"
                  fontSize="small"
                  fontWeight="normal"
                  textAlign="left"
                  rounded="none"
                  whiteSpace="pre-wrap"
                  bg={activeIndex === index ? "brand.50" : undefined}
                  _dark={{
                    bg:
                      activeIndex === index
                        ? transparentize("brand.200", 0.12)
                        : undefined,
                  }}
                  onClick={() => onChange(filteredOptionIndexes[index])}
                >
                  {renderOption
                    ? renderOption(
                        options[filteredOptionIndexes[index]],
                        filter,
                      )
                    : renderHighlightedText(
                        options[filteredOptionIndexes[index]],
                        filter,
                      )}
                </Button>
              </ListItem>
            );
          },
        }}
        totalListHeightChanged={
          (height) => setListHeight(height < 360 ? height : 360) // 360 is the max height => 9 * 40
        }
        style={{
          height: listHeight,
        }}
      />
      {stickyFooterOption && stickyFooterOption(onClose)}
    </Box>
  );
};

export default AutocompleteOptions;
