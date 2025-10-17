import {
  Button,
  Divider,
  Flex,
  Heading,
  Icon,
  TabPanel,
  TabPanelProps,
  Text,
  Tooltip,
} from "@chakra-ui/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { AiOutlineInfoCircle } from "react-icons/ai";

import Checkboxes from "@/components/Checkboxes";
import Input from "@/components/Input";

import ScheduleDeviation from "@/types/scheduleDeviation";

import { formatDateTime } from "@/utils/datetime";

import { FormValues } from "../../types";

interface SummaryPanelProps extends TabPanelProps {
  data: ScheduleDeviation;
  onHandle: (data: FormValues) => void;
}

const SummaryPanel = ({ data, onHandle, ...props }: SummaryPanelProps) => {
  const { t } = useTranslation();
  const {
    register,
    watch,
    reset,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();

  const addonOptions = useMemo(
    () =>
      data?.schedule?.addonSummaries?.map((item) => ({
        label: item.name ?? "",
        value: `${item.id}`,
      })) ?? [],
    [data],
  );

  const handleSubmit = formSubmitHandler((data) => {
    onHandle(data);
  });

  useEffect(() => {
    if (data) {
      reset({
        actualQuarters:
          (data.isHandled && data.meta?.actualQuarters
            ? Number(data.meta.actualQuarters)
            : data.schedule?.actualQuarters) ?? 0,
        items:
          (data.isHandled && data.meta?.items
            ? data.meta.items
            : data?.schedule?.addonSummaries
          )?.reduce<string[]>((acc, item) => {
            if (item.isCharge) {
              acc.push(`${item.id}`);
            }
            return acc;
          }, []) ?? [],
      });
    }
  }, [data]);

  return (
    <TabPanel {...props}>
      <Flex as="form" direction="column" onSubmit={handleSubmit}>
        <Heading
          size="sm"
          color="gray.600"
          fontWeight="medium"
          lineHeight="base"
          mb={8}
          _dark={{ color: "gray.300" }}
        >
          {t("deviation summary description")}
        </Heading>
        <Flex
          direction={{ base: "column", md: "row" }}
          gap={{ base: 3, md: 6 }}
          mb={8}
        >
          <Flex direction="column" flex={1} gap={3}>
            <Heading size="xs">{t("start time")}</Heading>
            <Text fontSize="sm">{formatDateTime(data?.schedule?.startAt)}</Text>
          </Flex>
          <Flex direction="column" flex={1} gap={3}>
            <Heading size="xs">{t("end time")}</Heading>
            <Text fontSize="sm">{formatDateTime(data?.schedule?.endAt)}</Text>
          </Flex>
          <Flex direction="column" flex={1} gap={3}>
            <Flex align="center" gap={1}>
              <Heading size="xs">{t("quarters")}</Heading>
              <Tooltip label={t("quarters info")}>
                <Flex>
                  <Icon as={AiOutlineInfoCircle} />
                </Flex>
              </Tooltip>
            </Flex>
            <Text fontSize="sm">
              {data?.schedule?.subscription?.detail?.quarters ?? "-"}
            </Text>
          </Flex>
        </Flex>
        <Flex
          direction={{ base: "column", md: "row" }}
          gap={{ base: 3, md: 6 }}
          mb={8}
        >
          <Flex direction="column" justify="flex-start" flex={1} gap={3}>
            <Flex align="center" gap={1}>
              <Heading size="xs">{t("actual start time")}</Heading>
              <Tooltip label={t("actual start time info")}>
                <Flex>
                  <Icon as={AiOutlineInfoCircle} />
                </Flex>
              </Tooltip>
            </Flex>
            <Text fontSize="sm">
              {data?.schedule?.actualStartAt
                ? formatDateTime(data?.schedule?.actualStartAt)
                : "-"}
            </Text>
          </Flex>
          <Flex direction="column" justify="flex-start" flex={1} gap={3}>
            <Flex align="center" gap={1}>
              <Heading size="xs">{t("actual end time")}</Heading>
              <Tooltip label={t("actual end time info")}>
                <Flex>
                  <Icon as={AiOutlineInfoCircle} />
                </Flex>
              </Tooltip>
            </Flex>
            <Text fontSize="sm">
              {data?.schedule?.actualEndAt
                ? formatDateTime(data?.schedule?.actualEndAt)
                : "-"}
            </Text>
          </Flex>
          <Flex direction="column" justify="center" flex={1} gap={3}>
            <Flex align="center" gap={1}>
              <Heading size="xs">{t("actual quarters")}</Heading>
              <Tooltip label={t("actual quarters info")}>
                <Flex>
                  <Icon as={AiOutlineInfoCircle} />
                </Flex>
              </Tooltip>
            </Flex>
            {data.isHandled ? (
              <Text fontSize="sm">{watch("actualQuarters")}</Text>
            ) : (
              <Input
                errorText={errors.actualQuarters?.message}
                {...register("actualQuarters", {
                  required: t("validation field required"),
                  valueAsNumber: true,
                })}
              />
            )}
          </Flex>
        </Flex>
        {data.types.includes("incomplete task") && addonOptions.length > 0 && (
          <Flex direction="column" gap={4} mb={8}>
            <Divider />
            <Heading
              size="xs"
              color="gray.600"
              fontWeight="medium"
              lineHeight="base"
              _dark={{ color: "gray.300" }}
            >
              {t("deviation summary charge addon description")}
            </Heading>
            <Checkboxes
              options={addonOptions}
              value={watch("items")}
              isReadOnly={data.isHandled}
              {...register("items")}
            />
          </Flex>
        )}
        {!data.isHandled && (
          <Flex justify="flex-end">
            <Button type="submit" fontSize="sm">
              {t("handle")}
            </Button>
          </Flex>
        )}
      </Flex>
    </TabPanel>
  );
};

export default SummaryPanel;
