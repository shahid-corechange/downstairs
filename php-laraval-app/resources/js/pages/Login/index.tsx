import { Button, Flex, Heading, Link, Text } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { Head, Link as InertiaLink, router, usePage } from "@inertiajs/react";
import dayjs from "dayjs";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { Trans, useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Input from "@/components/Input";
import InputOTP from "@/components/InputOTP";
import Modal from "@/components/Modal";
import PasswordInput from "@/components/PasswordInput";
import StoreSelectionModal from "@/components/StoreSelectionModal";

import {
  DEFAULT_CURRENCY,
  DEFAULT_LANGUAGE,
  DEFAULT_TIMEZONE,
} from "@/constants/user";

import { useTimer } from "@/hooks/time";
import useCart from "@/hooks/useCart";

import AuthLayout from "@/layouts/Auth";

import useAuthStore from "@/stores/auth";

import { Store } from "@/types/store";
import User from "@/types/user";

import i18n from "@/utils/localization";

import { PageProps } from "@/types";

interface Login2FASuccessPayload {
  action: "2FA";
  type: "email" | "sms";
  recipient: string;
  otpLength: number;
  expireAt: string;
}

interface LoginSuccessPayload {
  stores: Store[];
}

type FormValues = {
  user: string;
  password: string;
};

const onLoginSuccess = (user: User) => {
  const {
    language = DEFAULT_LANGUAGE,
    currency = DEFAULT_CURRENCY,
    timezone = DEFAULT_TIMEZONE,
  } = user.info ?? {};

  const { setLocale, setUser } = useAuthStore.getState();

  i18n.changeLanguage(language);

  localStorage.setItem("language", language);

  setLocale(currency, language, timezone);
  setUser(user);
  dayjs.updateLocale(language.split("_")[0], {
    weekStart: 1,
  });
  dayjs.locale(language.split("_")[0]);
  dayjs.tz.setDefault(timezone);
};

const Login = () => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage<PageProps>().props;
  const { reset } = useCart();
  const {
    register,
    getValues,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [twoFAInfo, setTwoFAInfo] = useState<Login2FASuccessPayload>();
  const [availableStores, setAvailableStores] = useState<Store[]>();
  const { duration: resendOTPDuration, restart: restartResendOTPTime } =
    useTimer({
      value: 1,
      unit: "minute",
      shouldStart: false,
    });

  const handleSubmit = formSubmitHandler((data) => {
    setIsSubmitting(true);
    router.post("/login", data, {
      headers: {
        "Accept-Language": localStorage.getItem("language") || "",
      },
      onFinish: () => {
        setIsSubmitting(false);
      },
      onSuccess: (page) => {
        const {
          user,
          flash: { successPayload },
        } = (
          page as Page<
            PageProps<
              Record<string, unknown>,
              Login2FASuccessPayload | LoginSuccessPayload | undefined,
              unknown
            >
          >
        ).props;

        if (user) {
          onLoginSuccess(user);
          reset();
        } else if (
          successPayload &&
          "action" in successPayload &&
          successPayload.action === "2FA"
        ) {
          setTwoFAInfo(successPayload);
        } else if (successPayload && "stores" in successPayload) {
          setAvailableStores(successPayload.stores);
        }
      },
    });
  });

  const handleVerifyOTP = (otp: string) => {
    setIsSubmitting(true);
    const { user, password } = getValues();

    router.post(
      "/two-factor/login",
      { user, password, otp },
      {
        headers: {
          "Accept-Language": localStorage.getItem("language") || "",
        },
        onFinish: () => {
          setIsSubmitting(false);
        },
        onSuccess: (page) => {
          const {
            user,
            flash: { successPayload },
          } = (
            page as Page<
              PageProps<
                Record<string, unknown>,
                LoginSuccessPayload | undefined,
                unknown
              >
            >
          ).props;

          if (user) {
            onLoginSuccess(user);
            reset();
          } else if (successPayload && "stores" in successPayload) {
            setAvailableStores(successPayload.stores);
          }
        },
      },
    );
  };

  const handleResendOTP = () => {
    setIsSubmitting(true);
    const { user, password } = getValues();

    router.post(
      "/two-factor/otp/resend",
      { user, password },
      {
        headers: {
          "Accept-Language": localStorage.getItem("language") || "",
        },
        onFinish: () => {
          setIsSubmitting(false);
        },
        onSuccess: () => {
          restartResendOTPTime();
        },
      },
    );
  };

  const handleStoreSelect = (storeId?: number): Promise<boolean> => {
    return new Promise((resolve) => {
      const { user, password } = getValues();

      const payload = {
        user,
        password,
        storeId,
      };

      router.post("/store-selection/login", payload, {
        headers: {
          "Accept-Language": localStorage.getItem("language") || "",
        },
        onFinish: () => resolve(true),
        onSuccess: (page) => {
          const { user } = (page as Page<PageProps>).props;
          if (user) {
            onLoginSuccess(user);
            reset();
          }
        },
      });
    });
  };

  return (
    <>
      <Head>
        <title>{t("login")}</title>
      </Head>
      <AuthLayout>
        <Heading size={{ base: "xl", md: "lg" }} mb={8}>
          {t("login")}
        </Heading>

        <Flex
          as="form"
          w="full"
          maxW={435}
          direction="column"
          gap={4}
          onSubmit={handleSubmit}
        >
          {Object.keys(serverErrors).length > 0 && (
            <Alert status="error" message={Object.values(serverErrors)[0]} />
          )}

          <Input
            placeholder={t("email or phone")}
            errorText={errors.user?.message || serverErrors.user}
            {...register("user", { required: t("validation field required") })}
          />
          <PasswordInput
            placeholder={t("password")}
            errorText={errors.password?.message || serverErrors.password}
            {...register("password", {
              required: t("validation field required"),
            })}
          />
          <Button
            type="submit"
            fontSize="sm"
            isLoading={isSubmitting}
            loadingText={t("please wait")}
          >
            {t("continue")}
          </Button>
        </Flex>

        <Link as={InertiaLink} href="/forgot-password" mt={4} fontSize="sm">
          {t("forgot password")}
        </Link>
      </AuthLayout>
      {twoFAInfo && (
        <Modal isOpen={!!twoFAInfo} onClose={() => setTwoFAInfo(undefined)}>
          <Flex direction="column" gap={8}>
            <Flex direction="column" gap={4}>
              <Heading size="md">{t("two factor authentication")}</Heading>
              <Text>
                <Trans
                  i18nKey={
                    twoFAInfo.type === "email"
                      ? "two factor authentication otp email message"
                      : "two factor authentication otp sms message"
                  }
                  values={{ recipient: twoFAInfo.recipient }}
                />
              </Text>
            </Flex>
            <InputOTP
              length={twoFAInfo.otpLength}
              onChange={handleVerifyOTP}
              isDisabled={isSubmitting}
            />
            <Flex textAlign="center" justify="center" gap={1}>
              <Text>{t("did not receive the code")}</Text>
              <Button
                variant="link"
                onClick={handleResendOTP}
                isDisabled={isSubmitting || resendOTPDuration.asSeconds() > 0}
              >
                {resendOTPDuration.asSeconds() > 0
                  ? t("resend otp in", {
                      time: resendOTPDuration.format("mm:ss"),
                    })
                  : t("resend otp")}
              </Button>
            </Flex>
          </Flex>
        </Modal>
      )}
      {availableStores && (
        <StoreSelectionModal
          isOpen={!!availableStores}
          stores={availableStores}
          onClose={() => setAvailableStores(undefined)}
          handleStoreSelect={handleStoreSelect}
          from="login"
        />
      )}
    </>
  );
};

export default Login;
