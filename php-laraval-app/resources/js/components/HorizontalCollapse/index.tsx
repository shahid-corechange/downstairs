import { Box, BoxProps } from "@chakra-ui/react";
import { AnimatePresence, motion } from "framer-motion";
import { useMemo, useRef } from "react";

interface HorizontalCollapseProps extends BoxProps {
  in?: boolean;
}

const HorizontalCollapse = ({
  children,
  in: inProp,
  ...props
}: HorizontalCollapseProps) => {
  const ref = useRef<HTMLDivElement>(null);
  const width = useMemo(() => {
    if (!ref.current) {
      return "auto";
    }

    return ref.current.children[0].getBoundingClientRect().width;

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ref.current]);

  return (
    <AnimatePresence initial={false}>
      {inProp && (
        <Box
          {...props}
          ref={ref}
          as={motion.div}
          initial={{ width: 0 }}
          animate={{ width }}
          exit={{ width: 0 }}
          overflow="hidden"
        >
          {children}
        </Box>
      )}
    </AnimatePresence>
  );
};

export default HorizontalCollapse;
