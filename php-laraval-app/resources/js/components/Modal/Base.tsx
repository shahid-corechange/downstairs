import {
  Divider,
  Modal,
  ModalBody,
  ModalBodyProps,
  ModalCloseButton,
  ModalContent,
  ModalContentProps,
  ModalHeader,
  ModalHeaderProps,
  ModalOverlay,
  ModalProps,
} from "@chakra-ui/react";

export interface BaseModalProps extends ModalProps {
  contentContainer?: ModalContentProps;
  headerContainer?: ModalHeaderProps;
  bodyContainer?: ModalBodyProps;
  title?: string;
  withDivider?: boolean;
}

const BaseModal = ({
  contentContainer,
  headerContainer,
  bodyContainer,
  title,
  children,
  withDivider = true,
  ...props
}: BaseModalProps) => {
  return (
    <Modal size="4xl" scrollBehavior="inside" isCentered {...props}>
      <ModalOverlay />
      <ModalContent {...contentContainer}>
        <ModalHeader {...headerContainer}>
          {title || headerContainer?.children}
        </ModalHeader>
        <ModalCloseButton />
        {(title || headerContainer?.children) && withDivider && <Divider />}
        <ModalBody p={12} {...bodyContainer}>
          {children}
        </ModalBody>
      </ModalContent>
    </Modal>
  );
};

export default BaseModal;
