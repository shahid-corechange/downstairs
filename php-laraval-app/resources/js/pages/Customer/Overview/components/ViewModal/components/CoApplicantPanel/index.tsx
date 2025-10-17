import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineCheckCircle, AiOutlineCloseCircle } from "react-icons/ai";
import { TbCalendarBolt, TbCalendarPause } from "react-icons/tb";

import Alert from "@/components/Alert";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { usePageModal } from "@/hooks/modal";

import { useGetCustomerRutCoApplicant } from "@/services/customer";

import { RutCoApplicant } from "@/types/rutCoApplicant";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import ChangeStatusModal from "./components/ChangeStatusModal";
import ContinueModal from "./components/ContinueModal";
import DeleteModal from "./components/DeleteModal";
import EditForm from "./components/EditForm";
import PauseForm from "./components/PauseForm";
import PauseNowModal from "./components/PauseNowModal";

interface RutCoApplicantPanelProps extends TabPanelProps {
  userId: number;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const RutCoApplicantPanel = ({
  userId,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: RutCoApplicantPanelProps) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } = usePageModal<
    RutCoApplicant,
    "changeStatus" | "pause" | "continue"
  >();
  const columns = useConst(getColumns(t));

  const rutCoApplicants = useGetCustomerRutCoApplicant(userId, {
    request: {
      only: [
        "id",
        "name",
        "identityNumber",
        "formattedPhone",
        "isEnabled",
        "isPaused",
        "pauseStartDate",
        "pauseEndDate",
      ],
      pagination: "page",
      size: -1,
    },
  });

  const enableLimitReached = useMemo(() => {
    if (!rutCoApplicants.data) {
      return false;
    }

    return (
      rutCoApplicants.data.filter((rutCoApplicant) => rutCoApplicant.isEnabled)
        .length >= 2
    );
  }, [rutCoApplicants.data]);

  const totalActiveApplicants = useMemo(() => {
    if (!rutCoApplicants.data) {
      return 0;
    }

    return rutCoApplicants.data.filter(
      (rutCoApplicant) => rutCoApplicant.isEnabled && !rutCoApplicant.isPaused,
    ).length;
  }, [rutCoApplicants.data]);

  return (
    <TabPanel {...props}>
      {totalActiveApplicants === 0 && (
        <Alert
          status="warning"
          title={t("warning")}
          message={t("no active rut co applicant alert body")}
          fontSize="small"
          mb={6}
        />
      )}

      <DataTable
        size="md"
        data={rutCoApplicants.data || []}
        columns={columns}
        title={t("rut co applicant")}
        withCreate={hasPermission("customer rut co applicant create")}
        withEdit={(row) =>
          hasPermission("customer rut co applicant update") &&
          !row.original.deletedAt
        }
        withDelete={(row) =>
          hasPermission("customer rut co applicant delete") &&
          !row.original.deletedAt
        }
        onCreate={() =>
          onModalExpansion({
            title: t("add rut co applicant"),
            content: (
              <AddForm
                userId={userId}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        onEdit={(row) =>
          onModalExpansion({
            title: t("edit rut co applicant"),
            content: (
              <EditForm
                userId={userId}
                rutCoApplicant={row.original}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        onDelete={(row) => openModal("delete", row.original)}
        actions={[
          (row) => ({
            label: row.original.isEnabled ? t("disable") : t("enable"),
            icon: row.original.isEnabled
              ? AiOutlineCloseCircle
              : AiOutlineCheckCircle,
            colorScheme: row.original.isEnabled ? "red" : "green",
            color: row.original.isEnabled ? "red.500" : "green.500",
            _dark: {
              color: row.original.isEnabled ? "red.200" : "green.200",
            },
            isHidden:
              !hasPermission(
                row.original.isEnabled
                  ? "customer rut co applicant disable"
                  : "customer rut co applicant enable",
              ) ||
              (!row.original.isEnabled && enableLimitReached),
            onClick: (row) => openModal("changeStatus", row.original),
          }),
          {
            label: t("pause"),
            icon: TbCalendarPause,
            colorScheme: "red",
            color: "red.500",
            _dark: { color: "red.200" },
            isHidden: (row) =>
              !hasPermission("customer rut co applicant pause") ||
              row.original.isPaused ||
              !row.original.isEnabled,
            onClick: (row) =>
              onModalExpansion({
                title: t("pause rut co applicant"),
                content: (
                  <PauseForm
                    userId={userId}
                    rutCoApplicant={row.original}
                    onCancel={onModalShrink}
                    onRefetch={onRefetch}
                  />
                ),
              }),
          },
          {
            label: t("pause now"),
            icon: TbCalendarPause,
            colorScheme: "red",
            color: "red.500",
            _dark: { color: "red.200" },
            isHidden: (row) =>
              !hasPermission("customer rut co applicant pause") ||
              row.original.isPaused ||
              !row.original.isEnabled,
            onClick: (row) => {
              openModal("pause", row.original);
            },
          },
          {
            label: t("continue"),
            icon: TbCalendarBolt,
            colorScheme: "green",
            color: "green.500",
            _dark: { color: "green.200" },
            isHidden: (row) =>
              !hasPermission("customer rut co applicant continue") ||
              !row.original.isPaused,
            onClick: (row) => {
              openModal("continue", row.original);
            },
          },
        ]}
        paginatable={false}
      />

      <DeleteModal
        userId={userId}
        rutCoApplicant={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
        onRefetch={onRefetch}
      />
      <ChangeStatusModal
        userId={userId}
        rutCoApplicant={modalData}
        isOpen={modal === "changeStatus"}
        onClose={closeModal}
        onRefetch={onRefetch}
      />
      <PauseNowModal
        userId={userId}
        rutCoApplicant={modalData}
        isOpen={modal === "pause"}
        onClose={closeModal}
        onRefetch={onRefetch}
      />
      <ContinueModal
        userId={userId}
        rutCoApplicant={modalData}
        isOpen={modal === "continue"}
        onClose={closeModal}
        onRefetch={onRefetch}
      />
    </TabPanel>
  );
};

export default RutCoApplicantPanel;
