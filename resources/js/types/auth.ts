import type { Permission } from '@/types/permissions';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    permissions: Permissions;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

export type Permissions = Record<Permission, boolean>;
