import { Flex, Icon, ListItem, Text, UnorderedList } from "@chakra-ui/react";
import dayjs from "dayjs";
import { forwardRef } from "react";
import { useTranslation } from "react-i18next";
import { FaPhoneAlt } from "react-icons/fa";

import { DATE_FORMAT } from "@/constants/datetime";

import { LaundryOrder } from "@/types/laundryOrder";

type LaundryReceiptProps = {
  laundryOrder: LaundryOrder;
};

const LaundryReceipt = forwardRef<HTMLDivElement, LaundryReceiptProps>(
  (props, ref) => {
    const { t } = useTranslation();
    const { laundryOrder } = props;

    const completionDate = laundryOrder.dueAt;

    return (
      <Flex
        ref={ref}
        direction="column"
        gap={2}
        sx={{
          "@media print": {
            margin: "8px",
          },
        }}
      >
        <Text fontSize="xl" textTransform="uppercase" fontWeight="normal">
          {t("laundry receipt")}
        </Text>

        <Flex flexDirection="column" gap={1}>
          <Text fontSize="xs">{t("completion date")}:</Text>
          {completionDate && (
            <Text fontSize="lg" fontWeight="normal" lineHeight={1.1}>
              {dayjs(completionDate).format(DATE_FORMAT)}
            </Text>
          )}
        </Flex>

        <Text fontSize="3xl" fontWeight="normal">
          # {laundryOrder.id}
        </Text>

        <Flex flexDirection="column" gap={1}>
          <Text fontSize="xs" fontWeight="extrabold" textDecoration="underline">
            {t("submitted articles")}
          </Text>

          <UnorderedList styleType="none" spacing={1} fontSize="xs" mx={0}>
            {laundryOrder.products?.map((product, index) => (
              <UnorderedList key={index} styleType="none" spacing={0} mx={0}>
                <ListItem display="flex" gap={2}>
                  <Text minW="8px">{laundryOrder.store?.id ?? "-"}</Text>
                  <Text>|</Text>
                  <Text flex={1}>
                    {product.quantity} x {product.name}
                  </Text>
                </ListItem>
              </UnorderedList>
            ))}
          </UnorderedList>
        </Flex>

        <Flex direction="column" gap={1}>
          <Text fontSize="xs" fontWeight="extrabold" textDecoration="underline">
            {t("receipt customer information")}
          </Text>

          <UnorderedList styleType="none" spacing={0} fontSize="xs" mx={0}>
            <ListItem>{laundryOrder.user?.fullname}</ListItem>
            <ListItem>
              <Icon as={FaPhoneAlt} /> {laundryOrder.user?.formattedCellphone}
            </ListItem>
          </UnorderedList>
        </Flex>
      </Flex>
    );
  },
);

export default LaundryReceipt;
