import {
  Button,
  Flex,
  Tab,
  TabList,
  TabPanels,
  Tabs,
  useDisclosure,
} from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { Trans, useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";
import Modal from "@/components/Modal";

import Property from "@/types/property";

import AddressPanel from "./components/AddressPanel";
import KeyPanel from "./components/KeyPanel";
import PropertyPanel from "./components/PropertyPanel";
import { FormValues } from "./types";

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Property;
  onSuccess?: () => void;
}

const EditModal = ({ data, isOpen, onClose, onSuccess }: EditModalProps) => {
  const { t } = useTranslation();
  const {
    register,
    reset,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const {
    isOpen: isAlertOpen,
    onOpen: onAlertOpen,
    onClose: onAlertClose,
  } = useDisclosure();

  const note = watch("note");
  const frontDoorCode = watch("frontDoorCode");
  const alarmCodeOff = watch("alarmCodeOff");
  const alarmCodeOn = watch("alarmCodeOn");
  const information = watch("information");

  const isNoteChanged = (note ?? "") !== (data?.meta?.note ?? "");
  const isKeyInformationChanged =
    (frontDoorCode ?? "") !== (data?.keyInformation?.frontDoorCode ?? "") ||
    (alarmCodeOff ?? "") !== (data?.keyInformation?.alarmCodeOff ?? "") ||
    (alarmCodeOn ?? "") !== (data?.keyInformation?.alarmCodeOn ?? "") ||
    (information ?? "") !== (data?.keyInformation?.information ?? "");

  const handleSubmit = formSubmitHandler((values) => {
    const newValues = {
      squareMeter: values.squareMeter,
      meta: {
        note: values.note,
      },
      keyInformation: {
        keyPlace: values.keyPlace,
        frontDoorCode: values.frontDoorCode,
        alarmCodeOff: values.alarmCodeOff,
        alarmCodeOn: values.alarmCodeOn,
        information: values.information,
      },
      address: {
        cityId: values.cityId,
        address: values.address,
        postalCode: values.postalCode,
        latitude: values.latitude,
        longitude: values.longitude,
      },
    };

    setIsSubmitting(true);
    router.patch(`/companies/properties/${data?.id}`, newValues, {
      onFinish: () => {
        setIsSubmitting(false);
        onAlertClose();
      },
      onSuccess: () => {
        onSuccess?.();
        onClose();
      },
    });
  });

  useEffect(() => {
    reset({
      squareMeter: data?.squareMeter,
      note: data?.meta?.note,
      keyPlace: data?.keyInformation?.keyPlace,
      frontDoorCode: data?.keyInformation?.frontDoorCode,
      alarmCodeOff: data?.keyInformation?.alarmCodeOff,
      alarmCodeOn: data?.keyInformation?.alarmCodeOn,
      information: data?.keyInformation?.information,
      cityId: data?.address?.cityId,
      address: data?.address?.address,
      postalCode: data?.address?.postalCode,
      latitude: data?.address?.latitude,
      longitude: data?.address?.longitude,
      countryId: data?.address?.city?.countryId,
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <>
      <Modal title={t("edit property")} isOpen={isOpen} onClose={onClose}>
        <Flex
          as="form"
          direction="column"
          onSubmit={(e) => {
            e.preventDefault();

            // Show alert if note or key information is changed
            if (isNoteChanged || isKeyInformationChanged) {
              onAlertOpen();
              return;
            }

            handleSubmit();
          }}
          autoComplete="off"
          noValidate
        >
          <Tabs>
            <TabList>
              <Tab>{t("address")}</Tab>
              <Tab>{t("info")}</Tab>
              <Tab>{t("key information")}</Tab>
            </TabList>
            <TabPanels>
              <AddressPanel
                register={register}
                errors={errors}
                watch={watch}
                setValue={setValue}
                data={data}
                py={8}
                display="flex"
                flexDirection="column"
                gap={4}
              />
              <PropertyPanel
                register={register}
                errors={errors}
                py={8}
                display="flex"
                flexDirection="column"
                gap={4}
              />
              <KeyPanel
                register={register}
                errors={errors}
                keyPlace={watch("keyPlace")}
                initialKeyPlace={data?.keyInformation?.keyPlace}
                py={8}
                display="flex"
                flexDirection="column"
                gap={4}
              />
            </TabPanels>
          </Tabs>
          <Flex justify="right" mt={4} gap={4}>
            <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
              {t("close")}
            </Button>
            <Button
              type="submit"
              fontSize="sm"
              isLoading={isSubmitting}
              loadingText={t("please wait")}
            >
              {t("submit")}
            </Button>
          </Flex>
        </Flex>
      </Modal>
      <AlertDialog
        size="2xl"
        title={t("edit property")}
        confirmButton={{
          isLoading: isSubmitting,
          loadingText: t("please wait"),
        }}
        confirmText={t("continue")}
        isOpen={isAlertOpen}
        onClose={onAlertClose}
        onConfirm={handleSubmit}
      >
        {isNoteChanged && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("note changed alert warning")}
            fontSize="small"
            mb={6}
          />
        )}
        {isKeyInformationChanged && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("key information changed alert warning")}
            fontSize="small"
            mb={6}
          />
        )}
        <Trans
          i18nKey="property edit alert body"
          values={{ property: data?.address?.fullAddress ?? "" }}
        />
      </AlertDialog>
    </>
  );
};

export default EditModal;
