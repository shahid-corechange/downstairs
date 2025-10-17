import { Box, Grid, GridItem, Text } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";

import MainLayout from "@/layouts/Main";

import { ServiceStatus } from "@/types/servicesStatus";

import { PageProps } from "@/types";

import WidgetAddOn from "./components/WidgetAddOn";
import WidgetCanceledBooking from "./components/WidgetCanceledBooking";
import WidgetChangeRequest from "./components/WidgetChangeRequest";
import WidgetCredit from "./components/WidgetCredit";
import WidgetDeviation from "./components/WidgetDeviation";
import WidgetEmployeeDeviation from "./components/WidgetEmployeeDeviation";
import WidgetFortnox from "./components/WidgetFortnox";
import WidgetInvoiceSummation from "./components/WidgetInvoiceSummation";
import WidgetLeaveRegistration from "./components/WidgetLeaveRegistration";
import WidgetServicesStatus from "./components/WidgetServicesStatus";
import WidgetSummation from "./components/WidgetSummation";
import WidgetUnassignSubscription from "./components/WidgetUnassignSubscription";
import WidgetWorkHours from "./components/WidgetWorkHours";

type DashboardProps = {
  unsyncData: number;
  totalCredit: number;
  plannedToStartTomorrow: number[];
  plannedToStartNextWeek: number[];
  alreadyPassed: number[];
  leaveRegistrations: number[];
  servicesStatus: ServiceStatus[];
  canceledByCustomer: number;
  canceledByTeam: number;
  canceledByAdmin: number;
};

const Dashboard = ({
  unsyncData,
  totalCredit,
  plannedToStartTomorrow,
  plannedToStartNextWeek,
  alreadyPassed,
  leaveRegistrations,
  servicesStatus,
  canceledByCustomer,
  canceledByTeam,
  canceledByAdmin,
}: PageProps<DashboardProps>) => {
  const { t } = useTranslation();

  return (
    <>
      <Head>
        <title>{t("dashboard")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <BrandText text={t("dashboard")} />
        <Grid
          templateColumns={{
            base: "repeat(1, 1fr)",
            lg: "repeat(3, 1fr)",
            xl: "repeat(3, 1fr)",
          }}
          mt={8}
          gap={4}
        >
          <GridItem>
            <WidgetCredit totalCredit={totalCredit} />
          </GridItem>
          <GridItem>
            <WidgetChangeRequest />
          </GridItem>
          <GridItem>
            <WidgetDeviation />
          </GridItem>
          <GridItem>
            <WidgetEmployeeDeviation />
          </GridItem>
          {unsyncData > 0 && (
            <GridItem>
              <WidgetFortnox unsyncData={unsyncData} />
            </GridItem>
          )}
          <GridItem>
            <WidgetLeaveRegistration leaveRegistrations={leaveRegistrations} />
          </GridItem>
          <GridItem>
            <WidgetUnassignSubscription
              plannedToStartTomorrow={plannedToStartTomorrow}
              plannedToStartNextWeek={plannedToStartNextWeek}
              alreadyPassed={alreadyPassed}
            />
          </GridItem>
          <GridItem>
            <WidgetCanceledBooking
              canceledByCustomer={canceledByCustomer}
              canceledByTeam={canceledByTeam}
              canceledByAdmin={canceledByAdmin}
            />
          </GridItem>
          <GridItem>
            <WidgetInvoiceSummation />
          </GridItem>
        </Grid>

        <Grid
          templateColumns={{
            base: "repeat(1, 1fr)",
            lg: "repeat(2, 1fr)",
          }}
          alignItems="start"
          gap={4}
          mt={8}
        >
          <GridItem>
            <WidgetWorkHours />
          </GridItem>
          <GridItem>
            <WidgetAddOn />
          </GridItem>
          <GridItem>
            <WidgetSummation />
          </GridItem>
        </Grid>

        {servicesStatus.length > 0 ? (
          <Box mt={8}>
            <Text fontSize="lg" fontWeight="bold">
              {t("services status")}
            </Text>
            <WidgetServicesStatus servicesStatus={servicesStatus} />
          </Box>
        ) : null}
      </MainLayout>
    </>
  );
};

export default Dashboard;
