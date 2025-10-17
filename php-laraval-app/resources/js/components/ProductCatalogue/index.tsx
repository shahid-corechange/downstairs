import {
  Badge,
  Button,
  Card,
  Flex,
  FlexProps,
  Grid,
  Icon,
  IconButton,
  Image,
  Text,
} from "@chakra-ui/react";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineMinus, AiOutlinePlus } from "react-icons/ai";
import { MdOutlineAddShoppingCart } from "react-icons/md";

import Input from "@/components/Input";

import { RUT_DISCOUNT } from "@/constants/rut";
import { DEFAULT_VAT } from "@/constants/vat";

import useAuthStore from "@/stores/auth";

import Product from "@/types/product";

import { formatCurrency } from "@/utils/currency";

import Empty from "../Empty";

interface ProductCatalogueProps extends FlexProps {
  products?: Product[];
  fixedPriceProducts?: Product[];
  onAddToCartClick?: (product: Product, quantity: number) => void;
  withMiscProduct?: boolean;
  withRut?: boolean;
}

type Category = {
  id: number;
  name: string;
};

export default function ProductCatalogue({
  products = [],
  fixedPriceProducts = [],
  onAddToCartClick,
  withMiscProduct = false,
  withRut = true,
  ...props
}: ProductCatalogueProps) {
  const { t } = useTranslation();

  const { currency, language } = useAuthStore.getState();

  const [selectedCategory, setSelectedCategory] = useState<Category>();
  const [quantities, setQuantities] = useState<Record<number, number>>({});

  // Get unique categories
  const categories: Category[] = useMemo(() => {
    const uniqueCategories = [{ id: 0, name: t("all") }];

    if (!products) {
      return uniqueCategories;
    }

    products.forEach((product) => {
      product.categories?.forEach((category) => {
        if (!uniqueCategories.some((c) => c.id === category.id)) {
          uniqueCategories.push({ id: category.id, name: category.name });
        }
      });
    });

    return uniqueCategories;
  }, [products, t]);

  const filteredProducts: (Product & { isFixedPrice?: boolean })[] =
    useMemo(() => {
      let result = products;

      if (selectedCategory && selectedCategory.id !== 0) {
        result = products.filter(
          (product) =>
            product.categories?.some(
              (category) => category.name === selectedCategory.name,
            ),
        );
      }

      if (fixedPriceProducts.length > 0) {
        result = result.map((product) => ({
          ...product,
          isFixedPrice: !!fixedPriceProducts.find((p) => p.id === product.id),
        }));
      }

      if (withMiscProduct) {
        const miscProduct = {
          id: 0,
          name: t("product sale miscellaneous"),
          price: 0,
          vatGroup: DEFAULT_VAT,
        } as Product;
        result = [miscProduct, ...result];
      }

      if (!withRut) {
        result = result.map((product) => ({
          ...product,
          hasRut: false,
        }));
      }

      return result;
    }, [
      products,
      selectedCategory,
      withMiscProduct,
      t,
      fixedPriceProducts,
      withRut,
    ]);

  const handleQuantityChange = (productId: number, value: number) => {
    setQuantities((prev) => ({
      ...prev,
      [productId]: Math.min(Math.max(value, 1), 999),
    }));
  };

  const handleIncreaseQuantity = (productId: number) => {
    setQuantities((prev) => ({
      ...prev,
      [productId]: Math.min((prev[productId] || 1) + 1, 999),
    }));
  };

  const handleDecreaseQuantity = (productId: number) => {
    setQuantities((prev) => ({
      ...prev,
      [productId]: Math.max((prev[productId] || 1) - 1, 1),
    }));
  };

  const handleAddToCart = (product: Product) => {
    onAddToCartClick?.(product, quantities[product.id] || 1);
    setQuantities((prev) => ({
      ...prev,
      [product.id]: 1,
    }));
  };

  const getPrice = (product: Product & { isFixedPrice?: boolean }): number => {
    const basePrice = product.priceWithVat || 0;

    if (product.isFixedPrice) {
      return basePrice;
    }

    if (product.hasRut) {
      return basePrice * RUT_DISCOUNT;
    }

    return basePrice;
  };

  useEffect(() => {
    setSelectedCategory(categories[0]);
  }, [categories]);

  return (
    <Flex direction="column" gap={4} {...props}>
      <Flex wrap="wrap" gap={2} w="full">
        {categories.map((category) => (
          <Button
            key={category.id}
            variant="solid"
            size="sm"
            colorScheme={
              selectedCategory?.id === category.id ? "brand" : "gray"
            }
            onClick={() => setSelectedCategory(category)}
          >
            {category.name}
          </Button>
        ))}
      </Flex>
      {/* Products grid */}
      <Grid
        templateColumns={{
          base: "repeat(auto-fill, minmax(150px, 1fr))",
        }}
        gap={4}
      >
        {filteredProducts.map((product) => (
          <Card
            key={product.id}
            variant="solid"
            backgroundColor="gray.100"
            _dark={{
              backgroundColor: "gray.800",
            }}
            overflow="hidden"
            borderRadius="lg"
          >
            <Flex direction="column" justify="space-between" h="full">
              {/* Product image */}
              <Flex
                position="relative"
                w="full"
                h={36}
                align="center"
                justify="center"
                p={1}
                bg={
                  product.thumbnailImage && product.color
                    ? product.color
                    : "gray.300"
                }
                _dark={{
                  bg:
                    product.thumbnailImage && product.color
                      ? product.color
                      : "gray.700",
                }}
                cursor="pointer"
                overflow="hidden"
                onClick={() => handleAddToCart(product)}
              >
                {product.thumbnailImage ? (
                  <Image
                    w={28}
                    h={28}
                    objectFit="cover"
                    src={product.thumbnailImage}
                    alt={product.name}
                  />
                ) : (
                  <Empty
                    h="full"
                    description={
                      <Text align="center" fontSize="xs" color="gray.500">
                        {t("no image")}
                      </Text>
                    }
                  />
                )}
              </Flex>

              {/* Product details */}
              <Flex flexGrow={1} direction="column" p={2} gap={1}>
                <Text fontWeight="bold" fontSize="md" lineHeight="short">
                  {formatCurrency(language, currency, getPrice(product), 2)}
                </Text>

                <Text
                  flexGrow={1}
                  fontSize="small"
                  noOfLines={2}
                  overflow="hidden"
                  textOverflow="ellipsis"
                  title={product.name}
                >
                  {product.name}
                </Text>

                <Flex
                  direction={{ base: "column", xl: "row" }}
                  align={{ base: "flex-start", xl: "center" }}
                  gap={2}
                >
                  {product.id === 0 && (
                    <Badge
                      variant="subtle"
                      colorScheme="blue"
                      fontSize="xs"
                      textTransform="none"
                    >
                      {t("manual")}
                    </Badge>
                  )}
                  {product.isFixedPrice && (
                    <Badge
                      variant="subtle"
                      colorScheme="purple"
                      fontSize="xs"
                      textTransform="none"
                    >
                      {t("fixed price")}
                    </Badge>
                  )}
                  {product.hasRut && withRut && (
                    <Badge
                      variant="subtle"
                      colorScheme="brand"
                      fontSize="xs"
                      textTransform="none"
                    >
                      {t("rut")}
                    </Badge>
                  )}
                </Flex>

                <Flex justify="space-between" align="center" gap={2} mt={2}>
                  <Input
                    variant="unstyled"
                    type="number"
                    min={1}
                    max={999}
                    value={quantities[product.id] || 1}
                    onChange={(e) =>
                      handleQuantityChange(
                        product.id,
                        parseInt(e.target.value, 10),
                      )
                    }
                    size="xs"
                    flexGrow={1}
                    minW={undefined}
                    textAlign="center"
                    inputContainer={{
                      fontSize: "xs",
                    }}
                    overflow="hidden"
                    borderRadius="md"
                    prefix={
                      <IconButton
                        aria-label="Decrease"
                        icon={<Icon as={AiOutlineMinus} boxSize={4} />}
                        variant="ghost"
                        colorScheme="gray"
                        _dark={{
                          _hover: { color: "brand.700" },
                        }}
                        _hover={{ backgroundColor: "brand.200" }}
                        size="sm"
                        onClick={(e) => {
                          e.preventDefault();
                          handleDecreaseQuantity(product.id);
                        }}
                      />
                    }
                    suffix={
                      <IconButton
                        aria-label="Increase"
                        icon={<Icon as={AiOutlinePlus} boxSize={4} />}
                        variant="ghost"
                        colorScheme="gray"
                        _dark={{
                          _hover: { color: "brand.700" },
                        }}
                        _hover={{ backgroundColor: "brand.200" }}
                        size="sm"
                        onClick={(e) => {
                          e.preventDefault();
                          handleIncreaseQuantity(product.id);
                        }}
                      />
                    }
                  />
                  <IconButton
                    aria-label="Add to cart"
                    variant="solid"
                    colorScheme="brand"
                    size="sm"
                    px={4}
                    flexShrink={0}
                    onClick={() => handleAddToCart(product)}
                    icon={<Icon as={MdOutlineAddShoppingCart} boxSize={4} />}
                  />
                </Flex>
              </Flex>
            </Flex>
          </Card>
        ))}
      </Grid>
    </Flex>
  );
}
