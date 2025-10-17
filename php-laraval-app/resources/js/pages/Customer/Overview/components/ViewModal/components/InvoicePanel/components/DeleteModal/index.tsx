import { Page } from "@inertiajs/core";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";
import CustomerAddressReferenceModal from "@/components/CustomerAddressReferenceModal";

import Customer from "@/types/customer";

import { PageProps } from "@/types";

interface DeleteModalProps {
  userId: number;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
  customer?: Customer;
}

const DeleteModal = ({
  userId,
  customer,
  isOpen,
  onClose,
  onRefetch,
}: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);
  const [references, setReferences] = useState<Customer[]>([]);

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(`/customers/${userId}/addresses/${customer?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: (page) => {
        const {
          flash: { error, errorPayload },
        } = (
          page as Page<PageProps<Record<string, unknown>, unknown, Customer[]>>
        ).props;

        if (error) {
          setReferences(errorPayload ?? []);
          return;
        }

        onClose();
        onRefetch();
      },
    });
  };

  return (
    <>
      <AlertDialog
        title={t("delete address")}
        confirmButton={{
          isLoading,
          colorScheme: "red",
          loadingText: t("please wait"),
        }}
        confirmText={t("delete")}
        isOpen={isOpen}
        onClose={onClose}
        onConfirm={handleDelete}
      >
        <Trans
          i18nKey="address delete alert body"
          values={{
            address: customer?.address?.fullAddress ?? "",
          }}
        />
      </AlertDialog>
      <CustomerAddressReferenceModal
        isOpen={references.length > 0}
        onClose={() => setReferences([])}
        data={references}
      />
    </>
  );
};

export default DeleteModal;
