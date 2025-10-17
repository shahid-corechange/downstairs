import {
  AlertDescription,
  AlertIcon,
  AlertTitle,
  Box,
  Alert as ChakraAlert,
  AlertProps as ChakraAlertProps,
  CloseButton,
  Text,
} from "@chakra-ui/react";
import { ReactNode } from "react";
import { useTranslation } from "react-i18next";

const titles = {
  success: "success",
  error: "error",
  warning: "warning",
  info: "info",
  loading: "loading",
};

interface AlertProps extends ChakraAlertProps {
  title?: string;
  message?: string;
  richMessage?: ReactNode;
  withIcon?: boolean;
  onClose?: () => void;
}

const Alert = ({
  title,
  message,
  richMessage,
  onClose,
  withIcon = true,
  status = "info",
  ...props
}: AlertProps) => {
  const { t } = useTranslation();

  return (
    <ChakraAlert status={status} rounded="lg" fontSize="sm" {...props}>
      {withIcon && <AlertIcon alignSelf="flex-start" />}
      <Box flex={1}>
        <AlertTitle as={Text} fontSize="sm">
          {title || t(titles[status])}
        </AlertTitle>
        {(richMessage || message) && (
          <AlertDescription as={Text} whiteSpace="pre-wrap">
            {richMessage || message}
          </AlertDescription>
        )}
      </Box>
      {onClose && (
        <CloseButton
          alignSelf="flex-start"
          position="relative"
          rounded="full"
          right={-1}
          top={-1}
          onClick={onClose}
        />
      )}
    </ChakraAlert>
  );
};

export default Alert;
