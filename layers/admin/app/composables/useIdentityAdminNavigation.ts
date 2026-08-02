import type {
  CommandPaletteGroup,
  CommandPaletteItem,
  NavigationMenuItem
} from '@nuxt/ui'
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
    const workspaceItems: NavigationMenuItem[] = [
      {
        label: 'Projects',
        icon: 'i-lucide-layout-dashboard',
        to: projectsPath,
        active: route.path === projectsPath || route.path.startsWith(`${projectsPath}/`),
        onSelect
      }
    ]

    const directoryItems: NavigationMenuItem[] = []
    const accessItems: NavigationMenuItem[] = []

    if (identitySession.value?.isSystemAdmin) {
      directoryItems.push({
        label: 'Users',
        icon: 'i-lucide-users',
        to: installationUsersPath,
        active: route.path === installationUsersPath,
        onSelect
      })
      accessItems.push({
        label: 'Global roles',
        icon: 'i-lucide-badge-check',
        to: globalRolesPath,
        active: route.path === globalRolesPath,
        onSelect
      })
      accessItems.push({
        label: 'Global permissions',
        icon: 'i-lucide-key-round',
        to: globalPermissionsPath,
        active: route.path === globalPermissionsPath,
        onSelect
      })
    }

    return [workspaceItems, directoryItems, accessItems].filter(items => items.length > 0)
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

  const searchGroups = computed<CommandPaletteGroup<CommandPaletteItem>[]>(() => [{
    id: 'identity-navigation',
    label: 'Navigate',
    items: [...primaryLinks.value.flat(), ...secondaryLinks.value.flat()].map(item => ({
      label: item.label,
      icon: item.icon,
      to: item.to
    }))
  }])

  return { primaryLinks, secondaryLinks, searchGroups }
}
