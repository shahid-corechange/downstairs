import PERMISSIONS from "@/constants/permission";

import useAuthStore from "@/stores/auth";

import User from "@/types/user";

export const hasRole = (role: string, user?: User): boolean => {
  if (!user) {
    const { user: authUser } = useAuthStore.getState();
    return authUser?.roles?.some((item) => item.name === role) ?? false;
  }

  return user.roles?.some((item) => item.name === role) ?? false;
};

export const hasPermission = (
  permission: keyof typeof PERMISSIONS,
  user?: User,
): boolean => {
  if (hasRole("Superadmin", user)) {
    return true;
  }

  if (!user) {
    const { user: authUser } = useAuthStore.getState();
    return authUser ? authUser.permissions.includes(permission) : false;
  }

  return user.permissions.includes(permission);
};

export const hasAnyPermissions = (
  permissions: (keyof typeof PERMISSIONS)[],
  user?: User,
): boolean => {
  if (permissions.length === 0) {
    return true;
  }

  return permissions.some((permission) => hasPermission(permission, user));
};

export const hasAllPermissions = (
  permissions: (keyof typeof PERMISSIONS)[],
  user?: User,
): boolean => {
  if (permissions.length === 0) {
    return true;
  }

  return permissions.every((permission) => hasPermission(permission, user));
};
