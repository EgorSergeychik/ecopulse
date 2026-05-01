import { usePage } from '@inertiajs/vue3';
import type { Auth } from '@/types/auth';
import type { Permission } from '@/types/permissions';

export function usePermission() {
    const page = usePage<Auth>();

    const can = (permission: Permission | string): boolean => {
        const permissions = page.props.auth?.permissions ?? {};

        return !!permissions[permission as keyof typeof permissions];
    };

    return { can };
}
