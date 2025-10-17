import {
  Card,
  CardBody,
  CardHeader,
  Checkbox,
  Flex,
  Grid,
  GridItem,
  Heading,
  Text,
  Textarea,
} from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect } from "react";
import { useFormContext } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";

import { MAX_MESSAGE_LENGTH } from "@/constants/message";

import { PageProps } from "@/types";

import { CheckoutFormType } from "../../types";

interface MessageProps {
  notificationMethod?: string;
}

const Message = ({ notificationMethod }: MessageProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    watch,
    setValue,
    formState: { errors },
  } = useFormContext<CheckoutFormType>();

  const message = watch("message");

  useEffect(() => {
    setValue("message", t("message new laundry order", { code: "IDN" }));
  }, [t, setValue]);

  const messageLength = message?.length || 0;

  const notificationMethodText =
    notificationMethod === "app" ? t("app notif") : t(notificationMethod || "");

  return (
    <Card>
      <CardHeader>
        <Heading size="sm">
          {t("message title", {
            notificationMethod: notificationMethodText,
          })}
        </Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        <Flex direction="column" gap={4}>
          <Input
            as={Textarea}
            labelText={t("message")}
            container={{
              display: "grid",
              gridTemplateColumns: "1fr 2fr",
              alignItems: "center",
              rowGap: 1,
              columnGap: 4,
            }}
            error={{
              gridColumn: "2",
            }}
            helper={{
              gridColumn: "2",
            }}
            label={{ mt: 2 }}
            helperText={
              <Flex justifyContent="space-between" alignItems="start">
                <Text>
                  {t("message character count", { count: messageLength })}
                </Text>
                <Text>{t("max character", { value: MAX_MESSAGE_LENGTH })}</Text>
              </Flex>
            }
            value={message}
            placeholder={t("message")}
            errorText={errors.message?.message || serverErrors["message"]}
            {...register("message", {
              maxLength: {
                value: MAX_MESSAGE_LENGTH,
                message: t("validation field max length", {
                  max: MAX_MESSAGE_LENGTH,
                }),
              },
              required: t("validation field required"),
            })}
            isRequired={true}
          />

          <Grid gridTemplateColumns="1fr 2fr" columnGap={4}>
            <GridItem gridColumn="2">
              <Checkbox {...register("sendMessage")} isChecked isReadOnly>
                <Text fontSize="small">
                  {t("auto send message", {
                    notificationMethod: notificationMethodText,
                  })}
                </Text>
              </Checkbox>
            </GridItem>
          </Grid>
        </Flex>
      </CardBody>
    </Card>
  );
};

export default Message;
