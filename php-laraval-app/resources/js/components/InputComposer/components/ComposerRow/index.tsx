import {
  Flex,
  FlexProps,
  Icon,
  IconButton,
  IconButtonProps,
} from "@chakra-ui/react";
import { motion } from "framer-motion";
import React from "react";
import { useTranslation } from "react-i18next";
import { LuX } from "react-icons/lu";

import { useContext } from "@/hooks/context";

import { InputValue } from "@/types";

import { ComposerTemplateProps } from "../../ComposerTemplate";
import { ComposerRowContext, InputComposerContext } from "../../contexts";

export interface ComposerRowProps extends Omit<FlexProps, "children"> {
  index: number;
  children:
    | React.ReactElement<ComposerTemplateProps>
    | React.ReactElement<ComposerTemplateProps>[];
  buttonProps?: Omit<IconButtonProps, "aria-label" | "onClick">;
}

const ComposerRow = ({
  index,
  children,
  buttonProps,
  ...props
}: ComposerRowProps) => {
  const { t } = useTranslation();
  const { rows, onChange, onRemove } = useContext(InputComposerContext);
  const [removedValues, setRemovedValues] =
    React.useState<Record<string, InputValue>>();

  const getValue = (name: string) => {
    if (removedValues) {
      return removedValues[name] ?? "";
    }

    return rows[index]?.[name] ?? "";
  };

  const handleChange = (name: string, value: InputValue) => {
    onChange(index, name, value);
  };

  return (
    <ComposerRowContext.Provider
      value={{ index, getValue, onChange: handleChange }}
    >
      <Flex
        gap={4}
        p={1}
        mx={-1}
        overflow="hidden"
        {...props}
        as={motion.div}
        initial={{ opacity: 0, height: 0 }}
        animate={{ opacity: 1, height: "auto" }}
        exit={{ opacity: 0, height: 0 }}
      >
        {children}
        {rows.length > 1 && (
          <Flex>
            <IconButton
              variant="outline"
              colorScheme="red"
              size="sm"
              aria-label={t("remove row")}
              onClick={() => {
                setRemovedValues(rows[index]);
                onRemove(index);
              }}
              {...buttonProps}
            >
              <Icon as={LuX} />
            </IconButton>
          </Flex>
        )}
      </Flex>
    </ComposerRowContext.Provider>
  );
};

export default ComposerRow;
