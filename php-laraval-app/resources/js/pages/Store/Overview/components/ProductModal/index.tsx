import { Box, Flex } from "@chakra-ui/react";

import Modal from "@/components/Modal";
import ProductCatalogue from "@/components/ProductCatalogue";

import { useGetProducts } from "@/services/product";
import { useGetStore } from "@/services/store";

interface ProductModalProps {
  storeId?: number;
  isOpen: boolean;
  onClose: () => void;
}

const ProductModal = ({ storeId, isOpen, onClose }: ProductModalProps) => {
  const { data: products } = useGetProducts({
    request: {
      only: ["id", "name"],
    },
  });

  const { data: store } = useGetStore(storeId, {
    request: {
      include: ["products"],
      only: ["id", "name", "products.productId"],
    },
  });

  // combine products and store products.
  // Add mark in the product with inStore property if product is in store products
  const combinedProducts = products?.map((product) => {
    const storeProduct = store?.products?.find(
      (p) => p.productId === product.id,
    );
    return { ...product, inStore: !!storeProduct };
  });

  const handleClose = () => {
    onClose();
  };

  return (
    <Modal
      bodyContainer={{ p: 8 }}
      isOpen={isOpen}
      onClose={handleClose}
      size="lg"
    >
      <Flex justify="space-between" w="full" gap={8}>
        <Box w="50%" h="full">
          <ProductCatalogue products={combinedProducts} withMiscProduct />
        </Box>
      </Flex>
    </Modal>
  );
};

export default ProductModal;
