import { Box, Flex, FlexProps, Icon, IconProps, Text } from "@chakra-ui/react";
import React from "react";
import { useTranslation } from "react-i18next";

const DefaultEmptyImage = (props: IconProps) => {
  return (
    <Icon viewBox="0 0 64 41" xmlns="http://www.w3.org/2000/svg" {...props}>
      <g transform="translate(0 1)" fill="none" fillRule="evenodd">
        <Box
          as="ellipse"
          fill="gray.100"
          cx="32"
          cy="33"
          rx="32"
          ry="7"
          _dark={{ fill: "gray.700" }}
        />
        <Box
          as="g"
          fillRule="nonzero"
          stroke="gray.200"
          _dark={{ stroke: "gray.600" }}
        >
          <path d="M55 12.76L44.854 1.258C44.367.474 43.656 0 42.907 0H21.093c-.749 0-1.46.474-1.947 1.257L9 12.761V22h46v-9.24z" />
          <Box
            as="path"
            d="M41.613 15.931c0-1.605.994-2.93 2.227-2.931H55v18.137C55 33.26 53.68 35 52.05 35h-40.1C10.32 35 9 33.259 9 31.137V13h11.16c1.233 0 2.227 1.323 2.227 2.928v.022c0 1.605 1.005 2.901 2.237 2.901h14.752c1.232 0 2.237-1.308 2.237-2.913v-.007z"
            fill="gray.50"
            _dark={{ fill: "gray.800" }}
          />
        </Box>
      </g>
    </Icon>
  );
};

interface EmptyProps extends FlexProps {
  image?: React.ReactNode;
  imageProps?: IconProps;
  description?: React.ReactNode;
}

const Empty: React.FC<EmptyProps> = ({
  image,
  imageProps,
  description,
  ...props
}) => {
  const { t } = useTranslation();

  return (
    <Flex direction="column" align="center" justify="center" gap={2} {...props}>
      {image ?? <DefaultEmptyImage h={10} w={16} {...imageProps} />}
      {description ?? (
        <Text align="center" fontSize="sm" color="gray.500">
          {t("no data")}
        </Text>
      )}
    </Flex>
  );
};

export default Empty;
