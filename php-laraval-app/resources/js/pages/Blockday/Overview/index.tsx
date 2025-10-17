import { Button, Flex, Grid, GridItem, useConst } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import dayjs from "dayjs";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import Calendar from "@/components/Calendar";
import DataTable from "@/components/DataTable";

import { DATE_FORMAT } from "@/constants/datetime";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { BlockDay } from "@/types/blockday";
import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";
import { parseQueryString } from "@/utils/querystring";

import { PageProps } from "@/types";

import getColumns from "./column";
import HandleModal from "./components/HandleModal";

type BlockDayProps = {
  blockdays: BlockDay[];
  schedules: Schedule[];
};

const BlockDays = ({ blockdays, schedules }: PageProps<BlockDayProps>) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  const [selectedDate, setSelectedDate] = useState(toDayjs().startOf("day"));

  const { modal, modalData, openModal, closeModal } = usePageModal<
    BlockDay | string,
    "handle"
  >();

  const isBlockedDay = useMemo(() => {
    return blockdays.some(
      (obj) => obj.blockDate === selectedDate.format(DATE_FORMAT),
    );
  }, [selectedDate, blockdays]);

  const handleChangeDate = (date: dayjs.Dayjs) => {
    setSelectedDate(date);

    router.get(`/blockdays?day=${date.format(DATE_FORMAT)}`, undefined, {
      preserveScroll: true,
      preserveState: true,
    });
  };

  const handleSyncSchedule = () => {
    router.reload();
  };

  useEffect(() => {
    const qs = parseQueryString();

    if (qs.day) {
      setSelectedDate(toDayjs(qs.day, false));
    }
  }, []);

  return (
    <>
      <Head>
        <title>{t("block days")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column">
          <Breadcrumb />
          <BrandText text={t("block days")} />
        </Flex>

        <Grid gap={4} templateColumns="repeat(2, 1fr)">
          <GridItem colSpan={2}>
            <DataTable
              title={t("schedules")}
              data={schedules}
              columns={columns}
              filterable={false}
              searchable={false}
              showEntries={[10]}
              actions={[
                {
                  label: t("view"),
                  icon: RiExternalLinkLine,
                  onClick: (row) => {
                    const id = row.original.id;
                    const startAt = toDayjs(row.original.startAt);
                    const startOfWeek = startAt.weekday(0).format(DATE_FORMAT);
                    const endOfWeek = startAt.weekday(6).format(DATE_FORMAT);
                    const shownTeamIds = row.original.teamId;

                    window.open(
                      `/schedules?scheduleId=${id}&startAt.gte=${startOfWeek}&endAt.lte=${endOfWeek}&view.shownTeamIds=${shownTeamIds}&view.showWeekend=true&view.showEarlyHours=true&view.showLateHours=true`,
                      "_blank",
                    );
                  },
                },
              ]}
            />
          </GridItem>

          <GridItem colSpan={1} colEnd={6}>
            <AuthorizationGuard
              permissions={
                isBlockedDay ? "blockdays delete" : "blockdays create"
              }
            >
              <Flex m={6} gap={4} justifyContent="flex-end">
                <Button
                  size="sm"
                  variant="outline"
                  onClick={handleSyncSchedule}
                >
                  {t("sync schedule")}
                </Button>
                <Button
                  size="sm"
                  onClick={() => {
                    const handleModalData = isBlockedDay
                      ? blockdays.find(
                          (obj) =>
                            obj.blockDate === selectedDate.format(DATE_FORMAT),
                        )
                      : selectedDate.format(DATE_FORMAT);

                    openModal("handle", handleModalData);
                  }}
                >
                  {t("block day add or remove", {
                    action: isBlockedDay ? t("remove") : t("add"),
                  })}
                </Button>
              </Flex>
            </AuthorizationGuard>

            <Calendar
              selectedDate={selectedDate}
              blockdays={blockdays}
              size="sm"
              p={6}
              onChange={handleChangeDate}
            />
          </GridItem>
        </Grid>
      </MainLayout>

      <HandleModal
        data={modalData}
        isOpen={modal === "handle"}
        onClose={closeModal}
      />
    </>
  );
};

export default BlockDays;
