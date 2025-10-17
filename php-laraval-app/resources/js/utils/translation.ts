import i18n from "@/utils/localization";

export const getPriceTypeTranslation = (priceType: string) => {
  const { t } = i18n;

  switch (priceType) {
    case "fixed_price_with_vat":
      return t("fixed price");
    case "dynamic_percentage":
      return t("dynamic percentage");
    case "dynamic_fixed_with_vat":
      return t("dynamic fixed");
    default:
      return priceType;
  }
};
