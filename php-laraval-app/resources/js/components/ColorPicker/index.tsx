import {
  Button,
  Center,
  FormControl,
  FormLabel,
  Input,
  InputProps,
  Popover,
  PopoverArrow,
  PopoverBody,
  PopoverCloseButton,
  PopoverContent,
  PopoverHeader,
  PopoverTrigger,
  SimpleGrid,
  useId,
} from "@chakra-ui/react";
import { forwardRef, useEffect, useState } from "react";

import { getColor } from "@/utils/color";
import { dispatchInputChangeEvent } from "@/utils/event";

export interface ColorPickerProps extends InputProps {
  value?: string;
  errorText?: string;
  labelText?: string;
  customColors?: string[];
}

const colors = [
  "gray.200",
  "gray.300",
  "gray.400",
  "gray.500",
  "red.200",
  "red.300",
  "red.400",
  "red.500",
  "orange.200",
  "orange.300",
  "orange.400",
  "orange.500",
  "yellow.200",
  "yellow.300",
  "yellow.400",
  "yellow.500",
  "green.200",
  "green.300",
  "green.400",
  "green.500",
  "teal.200",
  "teal.300",
  "teal.400",
  "teal.500",
  "blue.200",
  "blue.300",
  "blue.400",
  "blue.500",
  "cyan.200",
  "cyan.300",
  "cyan.400",
  "cyan.500",
  "purple.200",
  "purple.300",
  "purple.400",
  "purple.500",
  "pink.200",
  "pink.300",
  "pink.400",
  "pink.500",
  "brand.200",
  "brand.300",
  "brand.400",
  "brand.500",
];

const ColorPicker = forwardRef<HTMLInputElement, ColorPickerProps>(
  (
    {
      value,
      name,
      onChange,
      onBlur,
      errorText,
      labelText,
      customColors,
      ...props
    },
    ref,
  ) => {
    const [color, setColor] = useState(value);

    const inputId = useId(undefined, "colorpicker-input");

    useEffect(() => {
      setColor(value);
    }, [value]);

    return (
      <FormControl isRequired isInvalid={!!errorText}>
        {labelText && <FormLabel fontSize="sm">{labelText}</FormLabel>}
        <Popover variant="picker">
          <PopoverTrigger>
            <Input
              readOnly
              aria-label={color}
              background={color}
              height="40px"
              width="100%"
              padding={0}
              minWidth="unset"
              rounded="md"
              cursor="pointer"
              {...props}
            />
          </PopoverTrigger>
          <PopoverContent width="300px">
            <PopoverArrow bg={color} />
            <PopoverCloseButton color="white" />
            <PopoverHeader
              height="100px"
              backgroundColor={color}
              borderTopLeftRadius="md"
              borderTopRightRadius="md"
              color="white"
            >
              <Center height="100%">{color}</Center>
            </PopoverHeader>
            <PopoverBody height="100%">
              <SimpleGrid columns={10} spacing={2}>
                {(customColors || colors).map((color) => (
                  <Button
                    key={color}
                    aria-label={color}
                    background={color}
                    height="22px"
                    width="22px"
                    padding={0}
                    minWidth="unset"
                    borderRadius={3}
                    _hover={{ background: color }}
                    onClick={() => {
                      setColor(getColor(color));

                      const inputElement = document.getElementById(inputId);

                      if (inputElement) {
                        dispatchInputChangeEvent(inputElement, getColor(color));
                      }
                    }}
                    value={color}
                  />
                ))}
              </SimpleGrid>
              <Input
                ref={ref}
                id={inputId}
                name={name}
                onChange={onChange}
                onBlur={onBlur}
                marginTop={3}
                rounded="md"
                value={color ?? ""}
                size="sm"
              />
            </PopoverBody>
          </PopoverContent>
        </Popover>
      </FormControl>
    );
  },
);

export default ColorPicker;
