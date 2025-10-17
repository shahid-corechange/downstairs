import { Button, Flex, FlexProps, Icon } from "@chakra-ui/react";
import { AnimatePresence } from "framer-motion";
import * as _ from "lodash-es";
import React, {
  forwardRef,
  useEffect,
  useImperativeHandle,
  useState,
} from "react";
import { useTranslation } from "react-i18next";
import { LuPlus } from "react-icons/lu";

import { InputValue } from "@/types";

import { ComposerTemplateProps } from "./ComposerTemplate";
import ComposerRow, { ComposerRowProps } from "./components/ComposerRow";
import { InputComposerContext } from "./contexts";
import { InputComposerState } from "./types";

interface InputComposerProps extends Omit<FlexProps, "children" | "onChange"> {
  children:
    | React.ReactElement<ComposerTemplateProps>
    | React.ReactElement<ComposerTemplateProps>[];
  defaultValues?: Record<string, InputValue>[];
  rowProps?: Omit<ComposerRowProps, "index" | "children">;
  maxRows?: number;
  onChange?: (rows: Record<string, InputValue>[]) => void;
}

const InputComposer = forwardRef<InputComposerState, InputComposerProps>(
  (
    { rowProps, maxRows, onChange, children, defaultValues = [{}], ...props },
    ref,
  ) => {
    const { t } = useTranslation();
    const [rows, setRows] = useState<InputComposerState["rows"]>(
      defaultValues.map((value, index) => ({ ...value, _id: index + 1 })),
    );

    const handleAdd = () => {
      setRows((prevState) => [
        ...prevState,
        { _id: (prevState.at(-1)?._id ?? 0) + 1 },
      ]);
    };

    const handleRemove = (index: number) => {
      setRows((prevState) => prevState.filter((_, i) => i !== index));
    };

    const handleChange = (index: number, name: string, value: InputValue) => {
      setRows((prevState) =>
        prevState.map((row, i) => {
          if (i === index) {
            return {
              ...row,
              [name]: value,
            };
          }

          return row;
        }),
      );
    };

    const handleReset = () => {
      setRows([{ _id: 1 }]);
    };

    useEffect(() => {
      if (maxRows !== undefined && rows.length > maxRows) {
        setRows((prevState) => prevState.slice(0, maxRows));
      }
    }, [maxRows, rows.length]);

    const state = {
      rows,
      maxRows,
      onAdd: handleAdd,
      onRemove: handleRemove,
      onChange: handleChange,
      reset: handleReset,
    };

    useEffect(() => {
      if (onChange) {
        onChange(rows.map((row) => _.omit(row, ["_id"])));
      }

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [rows]);

    useImperativeHandle(ref, () => state);

    return (
      <InputComposerContext.Provider value={state}>
        <Flex direction="column" gap={1} {...props}>
          <AnimatePresence initial={false}>
            {rows.map((row, i) => (
              <ComposerRow key={row._id} index={i} {...rowProps}>
                {children}
              </ComposerRow>
            ))}
          </AnimatePresence>
          {(!maxRows || rows.length < maxRows) && (
            <Button
              variant="outline"
              size="sm"
              mt={3}
              fontSize="xs"
              lineHeight="base"
              alignSelf="flex-end"
              leftIcon={<Icon as={LuPlus} />}
              onClick={handleAdd}
            >
              {t("new row")}
            </Button>
          )}
        </Flex>
      </InputComposerContext.Provider>
    );
  },
);

export default InputComposer;
