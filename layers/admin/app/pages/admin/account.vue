<script setup lang="ts">
import type { NavigationMenuItem, TableColumn } from '@nuxt/ui'
import type { IdentityAccountSession } from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-admin'] })

const access = useIdentityAccess()
const userSession = useUserSession()
const toast = useToast()
const route = useRoute()
const activeTab = computed<'profile' | 'security' | 'password' | 'sessions'>(
  () => {
    const tab = route.query.tab

    return tab === 'security' || tab === 'password' || tab === 'sessions'
      ? tab
      : 'profile'
  }
)
const user = computed(
  () =>
    userSession.user.value as {
      name?: string
      username?: string
      email?: string
    } | null
)
const profile = reactive({
  username: user.value?.username || user.value?.name || '',
  email: user.value?.email || '',
  avatarUrl: ''
})
const security = reactive({
  twoFactorEnabled: false,
  loginAlertsEnabled: true,
  backupEmail: ''
})
const password = reactive({ current: '', next: '', confirmation: '' })
const savingProfile = ref(false)
const savingSecurity = ref(false)
const savingPassword = ref(false)
const revokingId = ref<string | null>(null)
const {
  data: sessions,
  status: sessionsStatus,
  error: sessionsError,
  refresh: refreshSessions
} = await access.accountSessions()
const navigation = computed<NavigationMenuItem[][]>(() => [[
  {
    label: 'Profile',
    icon: 'i-lucide-user-round',
    to: { path: '/admin/account', query: { tab: 'profile' } },
    active: activeTab.value === 'profile'
  },
  {
    label: 'Security',
    icon: 'i-lucide-shield-check',
    to: { path: '/admin/account', query: { tab: 'security' } },
    active: activeTab.value === 'security'
  },
  {
    label: 'Password',
    icon: 'i-lucide-key-round',
    to: { path: '/admin/account', query: { tab: 'password' } },
    active: activeTab.value === 'password'
  },
  {
    label: 'Sessions',
    icon: 'i-lucide-monitor-smartphone',
    badge: sessions.value?.length ?? 0,
    to: { path: '/admin/account', query: { tab: 'sessions' } },
    active: activeTab.value === 'sessions'
  }
]])
const sessionColumns: TableColumn<IdentityAccountSession>[] = [
  { accessorKey: 'project', header: 'Project' },
  { accessorKey: 'client', header: 'Client' },
  { accessorKey: 'created_at', header: 'Started' },
  { accessorKey: 'expires_at', header: 'Expires' },
  { id: 'actions', header: '' }
]

async function saveProfile() {
  savingProfile.value = true
  try {
    await access.updateAccount({
      username: profile.username,
      email: profile.email,
      avatar_url: profile.avatarUrl || null
    })
    await userSession.fetch()
    toast.add({ title: 'Account profile updated', color: 'success' })
  } finally {
    savingProfile.value = false
  }
}

async function saveSecurity() {
  savingSecurity.value = true
  try {
    await access.updateAccountSecurity({
      two_factor_enabled: security.twoFactorEnabled,
      login_alerts_enabled: security.loginAlertsEnabled,
      backup_email: security.backupEmail || null
    })
    toast.add({ title: 'Security preferences updated', color: 'success' })
  } finally {
    savingSecurity.value = false
  }
}

async function savePassword() {
  savingPassword.value = true
  try {
    await access.changePassword({
      current_password: password.current,
      password: password.next,
      password_confirmation: password.confirmation
    })
    Object.assign(password, { current: '', next: '', confirmation: '' })
    toast.add({
      title: 'Password changed',
      description: 'Other sessions were revoked.',
      color: 'success'
    })
    await refreshSessions()
  } finally {
    savingPassword.value = false
  }
}

async function revokeSession(id: string) {
  revokingId.value = id
  try {
    await access.revokeAccountSession(id)
    await refreshSessions()
  } finally {
    revokingId.value = null
  }
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-account"
    title="My account"
    icon="i-lucide-user-cog"
    description="Manage the global identity shared across your project memberships and active sessions."
  >
    <div class="grid gap-6 lg:grid-cols-[12rem_minmax(0,1fr)]">
      <IdentityAdminSettingsNavigation
        :items="navigation"
        label="Account settings"
      />

      <section class="min-w-0 space-y-6">
        <UPageCard
          v-if="activeTab === 'profile'"
          title="Profile details"
          description="This information identifies you throughout the installation."
          variant="subtle"
        >
          <form
            class="grid gap-5 md:grid-cols-2"
            @submit.prevent="saveProfile"
          >
            <UFormField
              label="Username"
              required
            >
              <UInput
                v-model="profile.username"
                class="w-full"
              />
            </UFormField>
            <UFormField
              label="Email"
              required
            >
              <UInput
                v-model="profile.email"
                type="email"
                class="w-full"
              />
            </UFormField>
            <UFormField
              label="Avatar URL"
              class="md:col-span-2"
            >
              <UInput
                v-model="profile.avatarUrl"
                type="url"
                class="w-full"
              />
            </UFormField>
            <div class="md:col-span-2">
              <UButton
                type="submit"
                label="Save profile"
                icon="i-lucide-save"
                :loading="savingProfile"
              />
            </div>
          </form>
        </UPageCard>

        <UPageCard
          v-else-if="activeTab === 'security'"
          title="Security preferences"
          description="Configure identity-level security signals and recovery contact details."
          variant="subtle"
        >
          <form
            class="space-y-5"
            @submit.prevent="saveSecurity"
          >
            <div
              class="divide-y divide-default rounded-xl border border-default px-4"
            >
              <div class="flex items-center justify-between gap-6 py-4">
                <div>
                  <p class="font-medium text-highlighted">
                    Two-factor authentication
                  </p>
                  <p class="text-sm text-muted">
                    Require a second verification factor when signing in.
                  </p>
                </div>
                <USwitch
                  v-model="security.twoFactorEnabled"
                  aria-label="Two-factor authentication"
                />
              </div>
              <div class="flex items-center justify-between gap-6 py-4">
                <div>
                  <p class="font-medium text-highlighted">
                    Login alerts
                  </p>
                  <p class="text-sm text-muted">
                    Notify you when a new session is established.
                  </p>
                </div>
                <USwitch
                  v-model="security.loginAlertsEnabled"
                  aria-label="Login alerts"
                />
              </div>
            </div>
            <UFormField
              label="Backup email"
              description="Used for security notices and account recovery."
            >
              <UInput
                v-model="security.backupEmail"
                type="email"
                class="w-full max-w-xl"
              />
            </UFormField>
            <UButton
              type="submit"
              label="Save security preferences"
              icon="i-lucide-save"
              :loading="savingSecurity"
            />
          </form>
        </UPageCard>

        <UPageCard
          v-else-if="activeTab === 'password'"
          title="Change password"
          description="Changing your password keeps this session and revokes the others."
          variant="subtle"
        >
          <form
            class="grid gap-5 md:grid-cols-3"
            @submit.prevent="savePassword"
          >
            <UFormField
              label="Current password"
              required
            >
              <UInput
                v-model="password.current"
                type="password"
                class="w-full"
              />
            </UFormField>
            <UFormField
              label="New password"
              required
            >
              <UInput
                v-model="password.next"
                type="password"
                class="w-full"
              />
            </UFormField>
            <UFormField
              label="Confirm password"
              required
            >
              <UInput
                v-model="password.confirmation"
                type="password"
                class="w-full"
              />
            </UFormField>
            <div class="md:col-span-3">
              <UButton
                type="submit"
                label="Change password"
                icon="i-lucide-key-round"
                :loading="savingPassword"
              />
            </div>
          </form>
        </UPageCard>

        <template v-else>
          <IdentityShellState
            v-if="sessionsStatus === 'pending'"
            state="loading"
            title="Loading sessions"
          />
          <IdentityShellState
            v-else-if="sessionsError"
            state="error"
            title="Unable to load sessions"
            :description="
              sessionsError.statusMessage
                || 'Your active sessions could not be loaded.'
            "
            @retry="refreshSessions()"
          />
          <IdentityTableCard
            v-else
            title="Active sessions"
            description="Review and revoke project-scoped identity sessions."
            :count="sessions?.length ?? 0"
          >
            <UTable
              :data="sessions ?? []"
              :columns="sessionColumns"
              empty="No active sessions were found."
              class="min-w-4xl"
            >
              <template #project-cell="{ row }">
                <div class="flex items-center gap-2">
                  <span class="font-medium text-highlighted">{{
                    row.original.project?.name || "Identity project"
                  }}</span><UBadge
                    v-if="row.original.current"
                    color="primary"
                    variant="soft"
                  >
                    Current
                  </UBadge>
                </div>
              </template>
              <template #client-cell="{ row }">
                <span class="text-sm text-muted">{{
                  row.original.client?.name || "Confidential client"
                }}</span>
              </template>
              <template #created_at-cell="{ row }">
                <span class="whitespace-nowrap text-sm text-muted">{{
                  formatIdentityDate(row.original.created_at)
                }}</span>
              </template>
              <template #expires_at-cell="{ row }">
                <span class="whitespace-nowrap text-sm text-muted">{{
                  formatIdentityDate(row.original.expires_at)
                }}</span>
              </template>
              <template #actions-cell="{ row }">
                <div class="flex justify-end">
                  <UButton
                    v-if="!row.original.current"
                    label="Revoke"
                    icon="i-lucide-log-out"
                    color="error"
                    variant="ghost"
                    :loading="revokingId === row.original.id"
                    @click="revokeSession(row.original.id)"
                  />
                </div>
              </template>
            </UTable>
          </IdentityTableCard>
        </template>
      </section>
    </div>
  </IdentityPanel>
</template>
