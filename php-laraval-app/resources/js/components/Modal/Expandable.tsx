import {
  ChakraProps,
  Flex,
  Icon,
  IconButton,
  Modal,
  ModalBody,
  ModalContent,
  ModalOverlay,
  ModalProps,
  ResponsiveValue,
  SlideFade,
  useBreakpointValue,
} from "@chakra-ui/react";
import { LuX } from "react-icons/lu";

export interface ExpandableModalProps extends Omit<ModalProps, "size"> {
  expandableContent: React.ReactNode;
  isExpanded: boolean;
  onShrink: () => void;
  title?: string;
  size?: ChakraProps["maxW"] extends ResponsiveValue<infer T> ? T : never;
  expandableTitle?: string;
  expandableSize?: ChakraProps["maxW"] extends ResponsiveValue<infer T>
    ? T
    : never;
}

const ExpandableModal = ({
  isExpanded,
  title,
  expandableTitle,
  expandableContent,
  children,
  onShrink,
  onClose,
  size = "4xl",
  expandableSize = "md",
  ...props
}: ExpandableModalProps) => {
  const slideFadeProps = useBreakpointValue({
    base: { offsetY: "200px" },
    xl: { offsetX: "200px" },
  });

  return (
    <Modal scrollBehavior="outside" isCentered onClose={onClose} {...props}>
      <ModalOverlay />
      <ModalContent
        maxW={{ base: size, xl: "none" }}
        display="flex"
        flexDirection={{ base: "column", xl: "row" }}
        alignItems={{ base: "stretch", xl: "flex-start" }}
        justifyContent="center"
        gap={4}
        py={4}
        px={4}
        bg="transparent"
        boxShadow="none"
        rounded="none"
        pointerEvents="none"
      >
        <ModalBody
          maxW={{ base: "none", xl: size }}
          minW={{ base: "none", xl: "xl" }}
          w="full"
          position="relative"
          p={0}
          bg="white"
          boxShadow="lg"
          rounded="md"
          pointerEvents="all"
          zIndex={2}
          _dark={{ bg: "gray.700" }}
        >
          <Flex
            as="header"
            py={4}
            px={6}
            alignItems="center"
            fontSize="xl"
            fontWeight="600"
          >
            {title}
          </Flex>

          <IconButton
            variant="ghost"
            size="sm"
            position="absolute"
            top={2}
            right={3}
            aria-label="Close"
            onClick={onClose}
          >
            <Icon as={LuX} fontSize="xl" />
          </IconButton>

          <Flex direction="column" p={8}>
            {children}
          </Flex>
        </ModalBody>

        <Flex
          as={SlideFade}
          in={isExpanded && !!expandableContent}
          maxW={{ base: "none", xl: expandableSize }}
          w="full"
          {...slideFadeProps}
          unmountOnExit
        >
          <Flex
            w="full"
            direction="column"
            position="relative"
            p={0}
            bg="white"
            boxShadow="lg"
            rounded="md"
            pointerEvents="all"
            _dark={{ bg: "gray.700" }}
          >
            <Flex
              as="header"
              py={4}
              px={6}
              alignItems="center"
              fontSize="xl"
              fontWeight="600"
            >
              {expandableTitle}
            </Flex>

            <IconButton
              variant="ghost"
              size="sm"
              position="absolute"
              top={2}
              right={3}
              aria-label="Close"
              onClick={onShrink}
            >
              <Icon as={LuX} fontSize="xl" />
            </IconButton>

            <Flex direction="column" p={8}>
              {expandableContent}
            </Flex>
          </Flex>
        </Flex>
      </ModalContent>
    </Modal>
  );
};

export default ExpandableModal;
