import {
  Flex,
  FlexProps,
  FormControl,
  FormControlProps,
  FormErrorMessage,
  FormErrorMessageProps,
  FormHelperText,
  FormHelperTextProps,
  FormLabel,
  FormLabelProps,
  Heading,
  Icon,
  Image,
  Input,
  InputProps,
  RequiredIndicator,
  Text,
  useId,
} from "@chakra-ui/react";
import { forwardRef, useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { PiUploadLight } from "react-icons/pi";

import i18n from "@/utils/localization";

const fontSizes = {
  xs: "xs",
  sm: "small",
  md: "sm",
  lg: "md",
};

export interface FileInputProps extends Omit<InputProps, "type"> {
  size?: "xs" | "sm" | "md" | "lg";
  container?: FormControlProps;
  label?: FormLabelProps;
  labelText?: string;
  previewContainer?: FlexProps;
  preview?: string;
  error?: FormErrorMessageProps;
  errorText?: string;
  helper?: FormHelperTextProps;
  helperText?: string;
  title?: string;
  description?: string;
  showPreview?: boolean;
}

const FileInput = forwardRef<HTMLInputElement, FileInputProps>(
  (
    {
      container,
      label,
      labelText,
      previewContainer,
      preview,
      error,
      errorText,
      helper,
      helperText,
      isRequired,
      size = "sm",
      title = i18n.t("file input default title"),
      description = i18n.t("file input default description"),
      showPreview = true,
      ...props
    },
    ref,
  ) => {
    const { t } = useTranslation();
    const id = useId(undefined, "field");
    const [previewImage, setPreviewImage] = useState<string>();

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      props.onChange?.(e);

      if (showPreview && e.target.files && e.target.files.length > 0) {
        const file = e.target.files[0];
        setPreviewImage(URL.createObjectURL(file));
      }
    };

    useEffect(() => {
      if (preview) {
        setPreviewImage(preview);
      }
    }, [preview]);

    return (
      <FormControl
        {...container}
        isInvalid={!!errorText}
        isRequired={isRequired}
      >
        {labelText && (
          <FormLabel
            htmlFor={id}
            id={`${id}-label`}
            fontSize={fontSizes[size]}
            mb={0}
            mr={0}
            requiredIndicator={<></>}
            {...label}
          >
            {labelText}
            {isRequired && <RequiredIndicator />}
            <Flex
              role="group"
              direction="column"
              align="center"
              justify="center"
              mt={2}
              border="1px"
              borderColor={errorText ? "red.500" : "inherit"}
              borderStyle="dashed"
              rounded="md"
              cursor="pointer"
              transition="all 0.2s"
              overflow="hidden"
              _hover={{
                borderColor: errorText ? "red.500" : "brand.500",
              }}
            >
              <Input
                {...props}
                ref={ref}
                type="file"
                id={id}
                display="none"
                required={false}
                onChange={handleChange}
              />

              {previewImage ? (
                <Flex w="full" justify="center" {...previewContainer}>
                  <Image
                    src={previewImage}
                    alt={t("uploaded image")}
                    objectFit="cover"
                  />
                </Flex>
              ) : (
                <Flex direction="column" align="center" justify="center" p={4}>
                  <Icon
                    as={PiUploadLight}
                    boxSize={12}
                    transition="all 0.2s"
                    color={errorText ? "red.500" : "gray.500"}
                  />
                  <Heading fontSize="sm" mt={4} mb={2}>
                    {title}
                  </Heading>
                  <Text fontSize="small" color="gray.500">
                    {description}
                  </Text>
                </Flex>
              )}
            </Flex>
          </FormLabel>
        )}
        {errorText && (
          <FormErrorMessage {...error}>{errorText}</FormErrorMessage>
        )}
        {helperText && !errorText && (
          <FormHelperText {...helper}>{helperText}</FormHelperText>
        )}
      </FormControl>
    );
  },
);

export default FileInput;
