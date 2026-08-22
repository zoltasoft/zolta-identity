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
  const { t } = useI18n()
  const onSelect = () => closeSidebar?.()

  const primaryLinks = computed<NavigationMenuItem[][]>(() => {
    const projectsPath = localePath('/admin/projects')
    const installationUsersPath = localePath('/admin/identity-users')
    const accessCatalogPath = localePath('/admin/access-catalog')

    const workspaceItems: NavigationMenuItem[] = [
      {
        label: t('identityConsole.nav.projects'),
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
        label: t('identityConsole.nav.users'),
        icon: 'i-lucide-users',
        to: installationUsersPath,
        active: route.path === installationUsersPath || route.path.startsWith(`${installationUsersPath}/`),
        onSelect
      })
      accessItems.push({
        label: t('identityConsole.nav.accessCatalog'),
        icon: 'i-lucide-library',
        to: accessCatalogPath,
        active: route.path === accessCatalogPath || route.path.startsWith(`${accessCatalogPath}/`),
        onSelect
      })
    }

    return [
      workspaceItems,
      directoryItems,
      accessItems
    ].filter(items => items.length > 0)
  })

  const secondaryLinks = computed<NavigationMenuItem[][]>(() => {
    return []
  })

  const searchGroups = computed<CommandPaletteGroup<CommandPaletteItem>[]>(() => [{
    id: 'identity-navigation',
    label: t('identityConsole.navigate'),
    items: [
      ...primaryLinks.value.flat().map(item => ({
        label: item.label,
        icon: item.icon,
        to: item.to
      })),
      {
        label: t('identityConsole.nav.account'),
        icon: 'i-lucide-user-cog',
        to: localePath('/admin/account')
      }
    ]
  }])

  return { primaryLinks, secondaryLinks, searchGroups }
}
