import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";

import { RUT_DISCOUNT } from "@/constants/rut";

import { CartProduct } from "@/types/cartProduct";
import { LaundryOrderProduct } from "@/types/laundryOrderProduct";
import LaundryPreference from "@/types/laundryPreference";
import Product from "@/types/product";

export type Cart = {
  products: CartProduct[];
  totalPrice: number;
  totalRut: number;
  fixedPrice: number;
  laundryPreferencePrice: number;
  hasRut: boolean;
  hasFixedPrice: boolean;
};

type NewCartProduct = {
  product: Product;
  quantity: number;
  discount?: number;
  note?: string;
  isModified?: boolean;
  isFixedPrice?: boolean;
};

export type CartKey = {
  storeId: number;
  userId?: number;
  laundryOrderId?: number;
};

type FixedPriceProduct = {
  fixedPrice?: number;
  fixedPriceProducts?: Product[];
};

type AddToCartProps = NewCartProduct &
  FixedPriceProduct & {
    cartKey: CartKey;
  };

export type RemoveFromCartProps = {
  index: number;
  cartKey: CartKey;
};

export type UpdateCartProductProps = {
  cartKey: CartKey;
  index: number;
  updates: Partial<CartProduct>;
  oldProduct?: Product;
};

type AddToCartBulkProps = FixedPriceProduct & {
  cartKey: CartKey;
  products: LaundryOrderProduct[];
};

type AddLaundryPreferenceToCartProps = {
  cartKey: CartKey;
  laundryPreference: LaundryPreference;
};

interface State {
  cart: Record<string, Cart>;
}

interface Actions {
  getCartKey: (props: CartKey) => string;
  getCart: (props: CartKey) => Cart;
  addToCart: (props: AddToCartProps) => void;
  removeFromCart: (props: RemoveFromCartProps) => void;
  updateCart: (props: UpdateCartProductProps) => void;
  clearCart: (props: CartKey) => void;
  addToCartBulk: (props: AddToCartBulkProps) => void;
  addLaundryPreferenceToCart: (props: AddLaundryPreferenceToCartProps) => void;
  removeLaundryPreferenceFromCart: (props: CartKey) => void;
  reset: () => void;
}

const calculateBasePrice = (
  priceWithVat: number,
  quantity: number,
  discount: number,
) => {
  return priceWithVat * quantity * (1 - discount / 100);
};

const calculateTotalPriceProduct = (
  cartProduct: Pick<
    CartProduct,
    "hasRut" | "priceWithVat" | "quantity" | "discount" | "isFixedPrice"
  >,
): number => {
  const { hasRut, priceWithVat, quantity, discount, isFixedPrice } =
    cartProduct;

  if (isFixedPrice) {
    return 0;
  }

  const basePrice = calculateBasePrice(priceWithVat, quantity, discount);

  if (hasRut) {
    return basePrice * RUT_DISCOUNT;
  }

  return basePrice;
};

const createCartProduct = (props: NewCartProduct): CartProduct => {
  const {
    product,
    quantity,
    discount = 0,
    note = "",
    isModified = false,
    isFixedPrice = false,
  } = props;

  const totalPrice = calculateTotalPriceProduct({
    ...product,
    quantity,
    discount,
    isFixedPrice,
  });

  return {
    id: product.id,
    name: product.name,
    hasRut: product.hasRut || false,
    priceWithVat: product.priceWithVat,
    vatGroup: product.vatGroup,
    quantity,
    discount,
    note,
    totalPrice,
    isModified,
    isFixedPrice,
  };
};

const createLaundryPreferenceCartProduct = (
  laundryPreference: LaundryPreference,
  cartTotalPrice: number,
): CartProduct => {
  const totalPrice = laundryPreference.percentage
    ? cartTotalPrice * (laundryPreference.percentage / 100)
    : laundryPreference.price;

  return {
    id: 0,
    name: laundryPreference.name,
    hasRut: false,
    priceWithVat: totalPrice,
    vatGroup: 0,
    quantity: 1,
    discount: 0,
    note: "",
    totalPrice,
    isModified: true,
    isLaundryPreference: true,
    isFixedPrice: false,
  };
};

const calculateTotalPrice = (cartProducts: CartProduct[]): number => {
  return cartProducts.reduce(
    (acc, product) => acc + (product.isFixedPrice ? 0 : product.totalPrice),
    0,
  );
};

const calculateRut = ({
  priceWithVat,
  quantity,
  discount,
  totalPrice,
  isFixedPrice,
}: CartProduct): number => {
  if (isFixedPrice) {
    return 0;
  }

  const priceWithoutRut = calculateBasePrice(priceWithVat, quantity, discount);
  return priceWithoutRut - totalPrice;
};

const calculateTotalRut = (cartProducts: CartProduct[]): number => {
  return cartProducts.reduce((acc, product) => acc + calculateRut(product), 0);
};

const hasRutProducts = (cartProducts: CartProduct[]): boolean => {
  return cartProducts.some(
    (product) => !product.isFixedPrice && product.hasRut,
  );
};

const hasFixedPriceProducts = (cartProducts: CartProduct[]): boolean => {
  return cartProducts.some((product) => product.isFixedPrice);
};

const isFixedPriceProduct = (
  productId: number,
  fixedPriceProducts?: Product[],
): boolean => {
  // productId 8 is Product Laundry Miscellaneous, this is not a fixed price product
  if (productId === 8 || !fixedPriceProducts) {
    return false;
  }

  // if fixed price products is an empty array, then all the products are fixed price
  if (fixedPriceProducts.length === 0) {
    return true;
  }

  const fixedPriceProductIds = new Set(fixedPriceProducts?.map((p) => p.id));
  return fixedPriceProductIds.has(productId);
};

const getProduct = (
  productId: number,
  products: CartProduct[],
): CartProduct | undefined => {
  if (productId === 0) {
    return undefined;
  }

  return products.find(
    (product) => !product.isModified && product.id === productId,
  );
};

const normalizeToBoolean = (value?: boolean | number): boolean => {
  if (value && typeof value === "number") {
    return value === 1;
  }

  return Boolean(value);
};

const getInitialState = (): State => ({
  cart: {},
});

const defaultCart: Cart = {
  products: [],
  totalPrice: 0,
  totalRut: 0,
  fixedPrice: 0,
  laundryPreferencePrice: 0,
  hasRut: false,
  hasFixedPrice: false,
};

const useCart = create<State & Actions>()(
  persist(
    (set, get) => ({
      ...getInitialState(),
      getCartKey: (props: CartKey): string => {
        const { storeId, userId, laundryOrderId } = props;
        const userIdString = userId ? `-${userId}` : "";
        const laundryOrderIdString =
          userId && laundryOrderId ? `-${laundryOrderId}` : "";

        return `${storeId}${userIdString}${laundryOrderIdString}`;
      },
      getCart: (props: CartKey): Cart => {
        const cartKey = get().getCartKey(props);
        return (
          get().cart[cartKey] || {
            products: [],
            totalPrice: 0,
            totalRut: 0,
            laundryPreferencePrice: 0,
            hasRut: false,
            fixedPrice: 0,
            hasFixedPrice: false,
          }
        );
      },
      addToCart: (props) => {
        const cartKey = get().getCartKey(props.cartKey);
        set((state) => {
          const savedCart = state.cart[cartKey] || defaultCart;

          // get the same product from the saved cart
          const product = getProduct(props.product.id, savedCart.products);
          if (product) {
            product.quantity =
              Number(product.quantity) + Number(props.quantity);
            product.totalPrice = calculateTotalPriceProduct({
              ...product,
              quantity: product.quantity,
              discount: product.discount,
              isFixedPrice: product.isFixedPrice,
            });

            return {
              cart: {
                ...state.cart,
                [cartKey]: {
                  ...savedCart,
                  products: [...savedCart.products],
                  totalPrice: calculateTotalPrice(savedCart.products),
                  totalRut: savedCart.hasRut
                    ? calculateTotalRut(savedCart.products)
                    : 0,
                },
              },
            };
          }

          const isFixedPrice = isFixedPriceProduct(
            props.product.id,
            props.fixedPriceProducts,
          );
          const newCartProducts = [
            ...savedCart.products,
            createCartProduct({
              ...props,
              product: {
                ...props.product,
                hasRut: !isFixedPrice ? props.product.hasRut : false,
              },
              isFixedPrice,
            }),
          ];
          const hasRut = hasRutProducts(newCartProducts);
          const hasFixedPrice = hasFixedPriceProducts(newCartProducts);

          return {
            cart: {
              ...state.cart,
              [cartKey]: {
                ...savedCart,
                products: newCartProducts,
                totalPrice: calculateTotalPrice(newCartProducts),
                totalRut: hasRut ? calculateTotalRut(newCartProducts) : 0,
                fixedPrice: hasFixedPrice
                  ? savedCart.fixedPrice || props.fixedPrice || 0
                  : 0,
                laundryPreferencePrice: savedCart.laundryPreferencePrice || 0,
                hasRut,
                hasFixedPrice,
              },
            },
          };
        });
      },
      removeFromCart: (props) => {
        const cartKey = get().getCartKey(props.cartKey);
        set((state) => {
          const savedCart = state.cart[cartKey];
          if (savedCart) {
            const newCartProducts = savedCart.products.filter(
              (_, i) => i !== props.index,
            );
            const hasRut = hasRutProducts(newCartProducts);
            const hasFixedPrice = hasFixedPriceProducts(newCartProducts);

            return {
              cart: {
                ...state.cart,
                [cartKey]: {
                  ...savedCart,
                  products: newCartProducts,
                  totalPrice: calculateTotalPrice(newCartProducts),
                  totalRut: hasRut ? calculateTotalRut(newCartProducts) : 0,
                  fixedPrice: hasFixedPrice ? savedCart.fixedPrice : 0,
                  laundryPreferencePrice: savedCart.laundryPreferencePrice || 0,
                  hasRut,
                  hasFixedPrice,
                },
              },
            };
          }
          return state;
        });
      },
      updateCart: (props) => {
        const cartKey = get().getCartKey(props.cartKey);
        set((state) => {
          const savedCart = state.cart[cartKey];
          if (savedCart) {
            const fieldsToCompare = ["name", "hasRut", "priceWithVat"] as const;

            const updatedCartProducts = savedCart.products.map((product, i) => {
              if (i === props.index) {
                const updatedProduct = { ...product, ...props.updates };
                if (props.oldProduct) {
                  // find if fieldsToCompare are different from the old product
                  const isModified = fieldsToCompare.some((field) => {
                    if (field === "hasRut") {
                      return (
                        normalizeToBoolean(updatedProduct[field]) !==
                        normalizeToBoolean(props.oldProduct?.[field])
                      );
                    }
                    return updatedProduct[field] !== props.oldProduct?.[field];
                  });

                  // if the product is not fixed price, and the hasRut is removed from the product, update the totalPrice (this is only for remove RUT from product)
                  if (
                    !updatedProduct.isFixedPrice &&
                    normalizeToBoolean(updatedProduct.hasRut) !==
                      normalizeToBoolean(props.oldProduct.hasRut)
                  ) {
                    updatedProduct.totalPrice = updatedProduct.hasRut
                      ? updatedProduct.priceWithVat *
                        updatedProduct.quantity *
                        (1 - updatedProduct.discount / 100) *
                        RUT_DISCOUNT
                      : updatedProduct.priceWithVat *
                        updatedProduct.quantity *
                        (1 - updatedProduct.discount / 100);
                  }

                  return { ...updatedProduct, isModified };
                }
                return updatedProduct;
              }
              return product;
            });

            const hasRut = hasRutProducts(updatedCartProducts);

            return {
              cart: {
                ...state.cart,
                [cartKey]: {
                  ...savedCart,
                  products: updatedCartProducts,
                  totalPrice: calculateTotalPrice(updatedCartProducts),
                  totalRut: hasRut ? calculateTotalRut(updatedCartProducts) : 0,
                  laundryPreferencePrice: savedCart.laundryPreferencePrice || 0,
                  hasRut,
                },
              },
            };
          }
          return state;
        });
      },
      clearCart: (props) => {
        const cartKey = get().getCartKey(props);
        set((state) => {
          const updatedCart = { ...state.cart };
          delete updatedCart[cartKey];
          return {
            cart: updatedCart,
          };
        });
      },
      addToCartBulk: (props) => {
        const cartKey = get().getCartKey(props.cartKey);
        set((state) => {
          const products = props.products.map((product) => {
            const isFixedPrice = isFixedPriceProduct(
              product.productId,
              props.fixedPriceProducts,
            );

            return createCartProduct({
              product: {
                id: product.productId,
                name: product.name,
                hasRut: !isFixedPrice ? product.hasRut : false,
                priceWithVat: !isFixedPrice
                  ? product.priceWithVat
                  : product?.product?.priceWithVat || 0,
                vatGroup: product.vatGroup,
              } as Product,
              quantity: product.quantity,
              discount: product.discount,
              note: product.note,
              isModified: product.isModified,
              isFixedPrice,
            });
          });
          const hasRut = hasRutProducts(products);
          const hasFixedPrice = hasFixedPriceProducts(products);

          return {
            cart: {
              ...state.cart,
              [cartKey]: {
                products,
                totalPrice: calculateTotalPrice(products),
                totalRut: hasRut ? calculateTotalRut(products) : 0,
                laundryPreferencePrice: 0,
                hasRut,
                fixedPrice: hasFixedPrice ? props.fixedPrice || 0 : 0,
                hasFixedPrice,
              },
            },
          };
        });
      },
      addLaundryPreferenceToCart: (props) => {
        const cartKey = get().getCartKey(props.cartKey);
        set((state) => {
          const savedCart = state.cart[cartKey] || defaultCart;
          const products = savedCart.products.filter(
            (product) => !product.isLaundryPreference,
          );
          const laundryPreferenceProduct = createLaundryPreferenceCartProduct(
            props.laundryPreference,
            savedCart.totalPrice,
          );
          const updatedCartProducts = [...products, laundryPreferenceProduct];

          return {
            cart: {
              ...state.cart,
              [cartKey]: {
                ...savedCart,
                products: updatedCartProducts,
                laundryPreferencePrice: laundryPreferenceProduct.totalPrice,
              },
            },
          };
        });
      },
      removeLaundryPreferenceFromCart: (props) => {
        const cartKey = get().getCartKey(props);
        set((state) => {
          const savedCart = state.cart[cartKey];
          if (savedCart) {
            const products = savedCart.products.filter(
              (product) => !product.isLaundryPreference,
            );

            return {
              cart: {
                ...state.cart,
                [cartKey]: {
                  ...savedCart,
                  totalPrice: calculateTotalPrice(products),
                  products,
                  laundryPreferencePrice: 0,
                },
              },
            };
          }
          return state;
        });
      },
      reset: () => {
        set(getInitialState());
      },
    }),
    {
      name: "customer-cart",
      storage: createJSONStorage(() => localStorage),
    },
  ),
);

export default useCart;
