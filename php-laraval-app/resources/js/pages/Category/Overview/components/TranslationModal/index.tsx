import { Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";

import Category from "@/types/category";

import EnglishPanel from "./components/EnglishPanel";
import SwedishPanel from "./components/SwedishPanel";

interface TranslationModalProps {
  data?: Category;
  isOpen: boolean;
  onClose: () => void;
}

const TranslationModal = ({ data, onClose, isOpen }: TranslationModalProps) => {
  const { t } = useTranslation();

  return data ? (
    <Modal bodyContainer={{ p: 8 }} isOpen={isOpen} onClose={onClose}>
      <Tabs>
        <TabList>
          <Tab>{t("language sv_se")}</Tab>
          <Tab>{t("language en_us")}</Tab>
        </TabList>
        <TabPanels>
          <SwedishPanel category={data} />
          <EnglishPanel category={data} />
        </TabPanels>
      </Tabs>
    </Modal>
  ) : null;
};

export default TranslationModal;
