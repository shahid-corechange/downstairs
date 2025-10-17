import {
  Accordion,
  AccordionButton,
  AccordionIcon,
  AccordionItem,
  AccordionPanel,
  Box,
  Button,
  Checkbox,
  CheckboxGroup,
  Flex,
  FormControl,
  FormErrorMessage,
  FormLabel,
  Grid,
  GridItem,
  Text,
} from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import PERMISSIONS from "@/constants/permission";

import { PageProps } from "@/types";

type PermissionKey = keyof typeof PERMISSIONS;
type PermissionValue = (typeof PERMISSIONS)[PermissionKey];

const groupedPermissions = Object.entries(PERMISSIONS).reduce<
  Record<string, PermissionValue[]>
>((acc, [, permission]) => {
  const { group } = permission;
  acc[group] = [...(acc[group] || []), permission];
  return acc;
}, {});

type FormValues = {
  name: string;
  permissions: PermissionKey[];
};

export interface CreateModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ onClose, isOpen }: CreateModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    setValue,
    watch,
    getValues,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({ defaultValues: { permissions: [] } });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const isPortalAccessChecked = watch("permissions").includes("access portal");
  const requiredPermissions: PermissionKey[] = [
    ...new Set(
      watch("permissions").flatMap((value) => {
        const permission = PERMISSIONS[value];
        return "requires" in permission ? [...permission.requires] : [];
      }),
    ),
  ];

  const handlePermissionsChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value as PermissionKey;
    const permission = PERMISSIONS[value];

    const oldPermissions = getValues("permissions");
    let newPermissions: PermissionKey[] = [];

    if (e.target.checked) {
      newPermissions = [...oldPermissions, value];
      if ("requires" in permission) {
        newPermissions.push(...permission.requires);
      }
    } else {
      newPermissions = oldPermissions.filter((item) => item !== value);

      if ("requires" in permission) {
        const requires: PermissionKey[] = [...permission.requires];
        const otherRequires: PermissionKey[] = newPermissions.flatMap(
          (value) => {
            const perm = PERMISSIONS[value];
            return "requires" in perm ? [...perm.requires] : [];
          },
        );

        newPermissions = newPermissions.filter(
          (item) => !requires.includes(item) || otherRequires.includes(item),
        );
      }

      if (value === "access portal") {
        newPermissions = newPermissions.filter(
          (item) =>
            item.includes("access") || requiredPermissions.includes(item),
        );
      }
    }

    setValue("permissions", [...new Set(newPermissions)]);
  };

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.post("/roles", values, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: onClose,
    });
  });

  useEffect(() => {
    if (isOpen) {
      setTimeout(() => reset({ permissions: [] }), 0);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal title={t("create role")} isOpen={isOpen} onClose={onClose}>
      <Alert
        status="info"
        title={t("info")}
        message={t("role info 1") + "\n" + t("role info 2")}
        fontSize="small"
        mb={6}
      />
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Input
          labelText={t("name")}
          errorText={errors.name?.message || serverErrors.name}
          isRequired
          {...register("name", { required: t("validation field required") })}
        />
        <FormControl isRequired={true} isInvalid={!!errors.permissions}>
          <FormLabel fontSize="small" htmlFor="">
            Permissions
          </FormLabel>
          <CheckboxGroup value={watch("permissions")}>
            <Grid templateColumns="repeat(3, 1fr)">
              {groupedPermissions["access"].map((permission) => (
                <GridItem key={permission.value}>
                  <Checkbox
                    value={permission.value}
                    _invalid={{
                      "& .chakra-checkbox__control": { borderColor: "inherit" },
                    }}
                    {...register("permissions", {
                      required: t("validation field required"),
                      onChange: handlePermissionsChange,
                    })}
                  >
                    {t(permission.label)}
                  </Checkbox>
                </GridItem>
              ))}
            </Grid>
            {errors.permissions && (
              <FormErrorMessage fontSize="small">
                {errors.permissions.message}
              </FormErrorMessage>
            )}
            <Accordion
              allowToggle
              mt={8}
              index={!isPortalAccessChecked ? -1 : undefined}
            >
              {Object.entries(groupedPermissions).map(
                ([group, permissions]) =>
                  group !== "access" && (
                    <AccordionItem
                      key={group}
                      isDisabled={!isPortalAccessChecked}
                    >
                      <AccordionButton>
                        <Box flex="1" textAlign="left">
                          <Text as="span">{t(group)}</Text>
                          <Text as="span" fontSize="small" color="GrayText">
                            {` (${permissions
                              .reduce<string[]>((acc, { value, label }) => {
                                if (getValues("permissions").includes(value)) {
                                  acc.push(t(label));
                                }
                                return acc;
                              }, [])
                              .join(", ")})`}
                          </Text>
                        </Box>
                        <AccordionIcon />
                      </AccordionButton>
                      <AccordionPanel>
                        <Grid templateColumns="repeat(3, 1fr)">
                          {permissions.map((permission) => (
                            <GridItem key={permission.value}>
                              <Checkbox
                                value={permission.value}
                                readOnly={requiredPermissions.includes(
                                  permission.value,
                                )}
                                _invalid={{
                                  "& .chakra-checkbox__control": {
                                    borderColor: "inherit",
                                  },
                                }}
                                _readOnly={{
                                  opacity: 0.4,
                                  cursor: "not-allowed",
                                }}
                                {...register("permissions", {
                                  onChange: handlePermissionsChange,
                                })}
                              >
                                {t(permission.label)}
                              </Checkbox>
                            </GridItem>
                          ))}
                        </Grid>
                      </AccordionPanel>
                    </AccordionItem>
                  ),
              )}
            </Accordion>
          </CheckboxGroup>
        </FormControl>
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
  );
};

export default CreateModal;
