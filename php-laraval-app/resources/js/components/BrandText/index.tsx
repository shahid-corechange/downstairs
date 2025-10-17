import { Link, useColorModeValue } from "@chakra-ui/react";

export interface BrandTextProps {
  text: string;
}

const BrandText = ({ text }: BrandTextProps) => {
  const mainText = useColorModeValue("brand.500", "white");

  return (
    <h1>
      <Link
        color={mainText}
        href="#"
        bg="inherit"
        borderRadius="inherit"
        fontWeight="bold"
        fontSize="34px"
        _hover={{ color: { mainText } }}
        _active={{
          bg: "inherit",
          transform: "none",
          borderColor: "transparent",
        }}
        _focus={{
          boxShadow: "none",
        }}
      >
        {text}
      </Link>
    </h1>
  );
};

export default BrandText;
