import { Text, TextProps } from "@chakra-ui/react";

interface PrintTextProps extends TextProps {
  children: React.ReactNode;
}

const PrintText = ({ children, ...props }: PrintTextProps) => (
  <Text
    sx={{
      "@media print": {
        color: "#000000",
      },
    }}
    {...props}
  >
    {children}
  </Text>
);

export default PrintText;
