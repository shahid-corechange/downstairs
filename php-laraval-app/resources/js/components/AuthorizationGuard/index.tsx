import { useMemo } from "react";

import PERMISSIONS from "@/constants/permission";

import {
  hasAllPermissions,
  hasAnyPermissions,
  hasPermission,
} from "@/utils/authorization";

interface AuthorizationGuardProps {
  permissions?: keyof typeof PERMISSIONS | (keyof typeof PERMISSIONS)[];
  strict?: boolean;
  children: React.ReactNode;
}

const AuthorizationGuard = ({
  permissions = [],
  children,
  strict = false,
}: AuthorizationGuardProps) => {
  const show = useMemo(
    () =>
      Array.isArray(permissions)
        ? strict
          ? hasAllPermissions(permissions)
          : hasAnyPermissions(permissions)
        : hasPermission(permissions),
    [permissions],
  );

  return show ? children : null;
};

export default AuthorizationGuard;
