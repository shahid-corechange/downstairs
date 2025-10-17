import {
  Flex,
  Heading,
  Icon,
  TabPanel,
  TabPanelProps,
  Text,
  useConst,
} from "@chakra-ui/react";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import {
  AiOutlineCalendar,
  AiOutlineMinus,
  AiOutlinePlus,
} from "react-icons/ai";
import { FiEdit3 } from "react-icons/fi";
import { LuTrash } from "react-icons/lu";

import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { usePageModal } from "@/hooks/modal";

import ScheduleModal from "@/pages/Schedule/Overview/components/Schedule/components/ScheduleModal";

import { useGetCustomerCredits } from "@/services/customer";

import Credit from "@/types/credit";
import { PagePagination } from "@/types/pagination";

import { RequestQueryStringOptions } from "@/utils/request";

import getColumns from "./column";
import EditForm from "./components/EditForm";
import GrantForm from "./components/GrantForm";
import RemoveModal from "./components/RemoveModal";
import UseForm from "./components/UseForm";

interface CreditPanelProps extends TabPanelProps {
  userId: number;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const CreditPanel = ({
  userId,
  onModalExpansion,
  onModalShrink,
  ...props
}: CreditPanelProps) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } = usePageModal<
    Credit,
    "schedule"
  >();
  const columns = useConst(getColumns(t));
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<Credit>>
  >({});
  const [totalCredits, setTotalCredits] = useState(0);

  const credits = useGetCustomerCredits(userId, {
    request: {
      ...requestOptions,
      include: ["issuer"],
      only: [
        "id",
        "scheduleId",
        "type",
        "remainingAmount",
        "description",
        "validUntil",
        "isSystemCreated",
        "issuer.id",
        "issuer.fullname",
      ],
      filter: requestOptions.filter,
      pagination: "page",
    },
  });

  useEffect(() => {
    setTotalCredits(Number(credits.data?.meta?.totalCredits ?? 0));
  }, [credits.data]);

  return (
    <>
      <TabPanel {...props}>
        <Flex direction="column" mb={8}>
          <Heading size="lg" textAlign="center">
            {totalCredits}
          </Heading>
          <Text textAlign="center" color="gray.500">
            {t("available credits")}
          </Text>
        </Flex>
        <DataTable
          size="md"
          data={credits.data?.data || []}
          columns={columns}
          fetchFn={(options) => setRequestOptions(options)}
          isFetching={credits.isFetching}
          pagination={credits.data?.pagination as PagePagination}
          tableActions={[
            {
              label: t("grant credits"),
              leftIcon: <Icon as={AiOutlinePlus} boxSize={4} />,
              onClick: () =>
                onModalExpansion({
                  title: t("grant credits"),
                  content: (
                    <GrantForm
                      userId={userId}
                      onCancel={onModalShrink}
                      onRefetch={credits.refetch}
                    />
                  ),
                }),
            },
            {
              label: t("use credits"),
              colorScheme: "red",
              leftIcon: <Icon as={AiOutlineMinus} boxSize={4} />,
              onClick: () =>
                onModalExpansion({
                  title: t("use credits"),
                  content: (
                    <UseForm
                      userId={userId}
                      onCancel={onModalShrink}
                      onRefetch={credits.refetch}
                    />
                  ),
                }),
            },
          ]}
          actions={[
            {
              label: t("schedule"),
              icon: AiOutlineCalendar,
              isHidden: (row) => !row.original.scheduleId,
              onClick: (row) => openModal("schedule", row.original),
            },
            {
              label: t("edit"),
              icon: FiEdit3,
              isHidden: (row) => row.original.isSystemCreated,
              onClick: (row) =>
                onModalExpansion({
                  title: t("edit credits"),
                  content: (
                    <EditForm
                      credit={row.original}
                      onCancel={onModalShrink}
                      onRefetch={credits.refetch}
                    />
                  ),
                }),
            },
            {
              label: t("remove"),
              colorScheme: "red",
              color: "red.500",
              _dark: { color: "red.200" },
              icon: LuTrash,
              isHidden: (row) => row.original.isSystemCreated,
              onClick: (row) => openModal("delete", row.original),
            },
          ]}
          serverSide
        />
      </TabPanel>
      {modalData?.scheduleId && (
        <ScheduleModal
          scheduleId={modalData.scheduleId}
          isOpen={modal === "schedule"}
          onClose={closeModal}
        />
      )}
      <RemoveModal
        data={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
        onRefetch={credits.refetch}
      />
    </>
  );
};

export default CreditPanel;
