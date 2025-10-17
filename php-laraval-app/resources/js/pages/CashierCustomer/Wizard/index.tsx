import { usePage } from "@inertiajs/react";

import { PageProps } from "@/types";

import CompanyWizard from "./components/CompanyWizard";
import PrivateWizard from "./components/PrivateWizard";

const WizardPage = () => {
  const { query } = usePage<PageProps>().props;

  const type = query?.type;

  if (type === "company") {
    return <CompanyWizard />;
  }

  return <PrivateWizard />;
};

export default WizardPage;
