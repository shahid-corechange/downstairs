import { useState } from "react";

export const usePageModal = <TData, TAction extends string = never>() => {
  const [modal, setModal] = useState<
    "create" | "edit" | "delete" | "restore" | TAction
  >();
  const [modalData, setModalData] = useState<TData>();

  const openModal = (type: Exclude<typeof modal, undefined>, data?: TData) => {
    setModal(type);
    setModalData(data);
  };

  const closeModal = () => {
    setModal(undefined);
    setModalData(undefined);
  };

  return { modal, modalData, openModal, closeModal };
};
