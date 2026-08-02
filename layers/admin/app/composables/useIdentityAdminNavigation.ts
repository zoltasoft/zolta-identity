import type { NavigationMenuItem } from '@nuxt/ui'
import type { Ref } from 'vue'
import type { IdentityBrowserSession } from '../../types/identity-access'

export function useIdentityAdminNavigation(
  identitySession: Ref<IdentityBrowserSession | null | undefined>,
  closeSidebar?: () => void
) {
  const route = useRoute()
  const localePath = useLocalePath()
  const onSelect = () => closeSidebar?.()

  const primaryLinks = computed<NavigationMenuItem[][]>(() => {
    const projectsPath = localePath('/admin/projects')
    const installationUsersPath = localePath('/admin/identity-users')
    const globalRolesPath = localePath('/admin/global-roles')
    const globalPermissionsPath = localePath('/admin/global-permissions')
    const items: NavigationMenuItem[] = [
      {
        label: 'Projects',
        icon: 'i-lucide-folder-key',
        to: projectsPath,
        active: route.path === projectsPath || route.path.startsWith(`${projectsPath}/`),
        onSelect
      }
    ]

    if (identitySession.value?.isSystemAdmin) {
      items.push({
        label: 'Users',
        icon: 'i-lucide-shield-user',
        to: installationUsersPath,
        active: route.path === installationUsersPath,
        onSelect
      })
      items.push({
        label: 'Global roles',
        icon: 'i-lucide-badge-check',
        to: globalRolesPath,
        active: route.path === globalRolesPath,
        onSelect
      })
      items.push({
        label: 'Global permissions',
        icon: 'i-lucide-key-round',
        to: globalPermissionsPath,
        active: route.path === globalPermissionsPath,
        onSelect
      })
    }

    return [items]
  })

  const secondaryLinks = computed<NavigationMenuItem[][]>(() => {
    const accountPath = localePath('/admin/account')

    return [[{
      label: 'My account',
      icon: 'i-lucide-user-cog',
      to: accountPath,
      active: route.path === accountPath,
      onSelect
    }]]
  })

  return { primaryLinks, secondaryLinks }
}
