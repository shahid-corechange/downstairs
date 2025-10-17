import {
  AlertDialogBody,
  AlertDialogCloseButton,
  AlertDialogContent,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogOverlay,
  Button,
  ButtonProps,
  AlertDialog as ChakraAlertDialog,
  AlertDialogProps as ChakraAlertDialogProps,
  ModalBodyProps,
  ModalContentProps,
  ModalFooterProps,
  ModalHeaderProps,
  Tooltip,
} from "@chakra-ui/react";
import { useRef } from "react";

import i18n from "@/utils/localization";

interface AlertDialogProps
  extends Omit<ChakraAlertDialogProps, "leastDestructiveRef"> {
  title: string;
  onSecondary?: () => void;
  onConfirm: () => void;
  body?: ModalBodyProps;
  container?: ModalContentProps;
  header?: ModalHeaderProps;
  footer?: ModalFooterProps;
  cancelButton?: ButtonProps;
  cancelText?: string;
  secondaryButton?: ButtonProps & { tooltip?: string };
  secondaryText?: string;
  confirmButton?: ButtonProps & { tooltip?: string };
  confirmText?: string;
}

const AlertDialog = ({
  title,
  children,
  header,
  body,
  container,
  footer,
  cancelButton,
  secondaryButton,
  confirmButton,
  secondaryText,
  onClose,
  onSecondary,
  onConfirm,
  cancelText = i18n.t("close"),
  confirmText = i18n.t("confirm"),
  ...props
}: AlertDialogProps) => {
  const cancelRef = useRef<HTMLButtonElement>(null);

  return (
    <ChakraAlertDialog
      motionPreset="slideInBottom"
      leastDestructiveRef={cancelRef}
      onClose={onClose}
      {...props}
    >
      <AlertDialogOverlay />
      <AlertDialogContent {...container}>
        <AlertDialogHeader {...header}>{title}</AlertDialogHeader>
        <AlertDialogCloseButton />
        <AlertDialogBody {...body}>{children}</AlertDialogBody>
        <AlertDialogFooter {...footer}>
          <Button
            ref={cancelRef}
            colorScheme="gray"
            fontSize="sm"
            onClick={onClose}
            {...cancelButton}
          >
            {cancelText}
          </Button>
          {secondaryText && (
            <Tooltip label={secondaryButton?.tooltip}>
              <Button
                variant="outline"
                colorScheme="linkedin"
                fontSize="sm"
                ml={3}
                onClick={onSecondary}
                {...secondaryButton}
              >
                {secondaryText}
              </Button>
            </Tooltip>
          )}
          <Tooltip label={confirmButton?.tooltip}>
            <Button
              colorScheme="brand"
              fontSize="sm"
              ml={3}
              onClick={onConfirm}
              {...confirmButton}
            >
              {confirmText}
            </Button>
          </Tooltip>
        </AlertDialogFooter>
      </AlertDialogContent>
    </ChakraAlertDialog>
  );
};

export default AlertDialog;
