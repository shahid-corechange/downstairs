import { Avatar, Button, Flex, Text } from "@chakra-ui/react";
import { Head, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import BrandText from "@/components/BrandText";
import FileInput from "@/components/FileInput";
import Input from "@/components/Input";

import { TWO_FACTOR_OPTIONS } from "@/constants/2fa";
import {
  DEFAULT_CURRENCY,
  DEFAULT_LANGUAGE,
  DEFAULT_TIMEZONE,
  DEFAULT_TWO_FACTOR_AUTH,
} from "@/constants/user";

import locales from "@/data/locales.json";

import CashierLayout from "@/layouts/Cashier";
import MainLayout from "@/layouts/Main";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { validateFilesExtension, validateFilesSize } from "@/utils/validation";

import { PageProps } from "@/types";

type FormValues = {
  firstName: string;
  lastName: string;
  language: string;
  timezone: string;
  twoFactorAuth: string;
  avatarImage?: FileList;
  avatar?: File;
};

const ProfileContent = () => {
  const { t } = useTranslation();
  const { user, storeId } = usePage<PageProps>().props;
  const {
    language = DEFAULT_LANGUAGE,
    timezone = DEFAULT_TIMEZONE,
    twoFactorAuth = DEFAULT_TWO_FACTOR_AUTH,
    currency = DEFAULT_CURRENCY,
    avatar = "",
  } = user.info ?? {};

  const [isSubmitting, setIsSubmitting] = useState(false);

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    values: {
      firstName: user.firstName,
      lastName: user.lastName,
      language,
      timezone,
      twoFactorAuth,
    },
  });

  const roles = user.roles?.map((item) => item.name) ?? [];
  const twoFactorOptions = getTranslatedOptions(
    TWO_FACTOR_OPTIONS as unknown as string[],
  );

  const handleSubmit = formSubmitHandler(({ avatarImage, ...values }) => {
    const newValues = {
      ...values,
    };

    if (avatarImage && avatarImage.length > 0) {
      const file = avatarImage[0];
      newValues.avatar = file;
    }

    setIsSubmitting(true);
    const url = storeId ? `/cashier/profile` : "/profile";
    router.post(
      url,
      { _method: "PATCH", ...newValues },
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => setTimeout(() => window.location.reload(), 2000),
      },
    );
  });

  return (
    <>
      <Flex direction="column" mb={8}>
        <BrandText text={t("profile")} />
      </Flex>

      <Flex
        as="form"
        maxW="2xl"
        w="full"
        mx="auto"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Avatar
          size="2xl"
          name={user.fullname}
          src={avatar}
          alignSelf="center"
        />

        <Text p={6} alignSelf="center" color="gray.500">
          {t("roles")}: {roles.join(", ")}
        </Text>

        <Flex gap={4}>
          <Input
            labelText={t("first name")}
            errorText={errors.firstName?.message || serverErrors.firstName}
            {...register("firstName", {
              required: t("validation field required"),
            })}
            isRequired
          />
          <Input
            labelText={t("last name")}
            errorText={errors.lastName?.message || serverErrors.lastName}
            {...register("lastName", {
              required: t("validation field required"),
            })}
            isRequired
          />
        </Flex>
        <Autocomplete
          options={locales}
          labelText={t("language")}
          value={watch("language")}
          errorText={errors.language?.message || serverErrors.language}
          {...register("language", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Flex gap={4}>
          <Input
            labelText={t("timezone")}
            defaultValue={timezone}
            isRequired
            isDisabled
          />
          <Input
            labelText="Currency"
            defaultValue={currency}
            isRequired
            isDisabled
          />
        </Flex>
        <Autocomplete
          options={twoFactorOptions}
          labelText={t("two factor authentication")}
          helperText={t("two factor authentication helper text")}
          value={watch("twoFactorAuth")}
          errorText={
            errors.twoFactorAuth?.message || serverErrors.twoFactorAuth
          }
          {...register("twoFactorAuth", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <FileInput
          labelText={t("avatar")}
          errorText={errors.avatarImage?.message || serverErrors.avatar}
          accept="image/jpg, image/jpeg, image/png"
          {...register("avatarImage", {
            validate: {
              size: (v) => validateFilesSize(v),
              extension: (v) =>
                validateFilesExtension(v, [
                  "image/jpg",
                  "image/jpeg",
                  "image/png",
                ]),
            },
          })}
        />
        <Flex justify="right" mt={4} gap={4}>
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
    </>
  );
};

const Profile = () => {
  const { t } = useTranslation();
  const { storeId } = usePage<PageProps>().props;
  const Layout = storeId ? CashierLayout : MainLayout;

  return (
    <>
      <Head>
        <title>{t("profile")}</title>
      </Head>
      <Layout content={{ p: 6 }}>
        <ProfileContent />
      </Layout>
    </>
  );
};

export default Profile;
