<script setup lang="ts">
definePageMeta({ layout: 'identity-admin', middleware: ['identity-admin'] })

const access = useIdentityAccess()
const userSession = useUserSession()
const toast = useToast()
const user = computed(() => userSession.user.value as {
  name?: string
  username?: string
  email?: string
} | null)
const profile = reactive({ username: user.value?.username || user.value?.name || '', email: user.value?.email || '', avatarUrl: '' })
const security = reactive({ twoFactorEnabled: false, loginAlertsEnabled: true, backupEmail: '' })
const password = reactive({ current: '', next: '', confirmation: '' })
const savingProfile = ref(false)
const savingSecurity = ref(false)
const savingPassword = ref(false)
const revokingId = ref<string | null>(null)
const { data: sessions, status: sessionsStatus, refresh: refreshSessions } = await access.accountSessions()

async function saveProfile() {
  savingProfile.value = true
  try {
    await access.updateAccount({
      username: profile.username,
      email: profile.email,
      avatar_url: profile.avatarUrl || null
    })
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
    toast.add({ title: 'Password changed', description: 'Other sessions were revoked.', color: 'success' })
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
  <div class="space-y-6">
    <IdentityPanel
      panel-id="identity-account-profile"
      title="My account"
      description="Update the global identity shared by your project memberships."
    >
      <form
        class="grid gap-4 md:grid-cols-2"
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
            :loading="savingProfile"
          />
        </div>
      </form>
    </IdentityPanel>

    <IdentityPanel
      panel-id="identity-account-security"
      title="Security preferences"
      description="Configure identity-level security signals and recovery contact details."
    >
      <form
        class="space-y-4"
        @submit.prevent="saveSecurity"
      >
        <USwitch
          v-model="security.twoFactorEnabled"
          label="Two-factor authentication preference"
        />
        <USwitch
          v-model="security.loginAlertsEnabled"
          label="Login alerts"
        />
        <UFormField label="Backup email">
          <UInput
            v-model="security.backupEmail"
            type="email"
            class="w-full max-w-xl"
          />
        </UFormField>
        <UButton
          type="submit"
          label="Save security preferences"
          :loading="savingSecurity"
        />
      </form>
    </IdentityPanel>

    <IdentityPanel
      panel-id="identity-account-password"
      title="Change password"
      description="Changing your password keeps this session and revokes the others."
    >
      <form
        class="grid gap-4 md:grid-cols-3"
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
            :loading="savingPassword"
          />
        </div>
      </form>
    </IdentityPanel>

    <IdentityPanel
      panel-id="identity-account-sessions"
      title="Sessions"
      description="Review and revoke project-scoped identity sessions."
    >
      <IdentityShellState
        v-if="sessionsStatus === 'pending'"
        state="loading"
        title="Loading sessions"
      />
      <div
        v-else
        class="divide-y divide-default rounded-2xl border border-default px-5"
      >
        <div
          v-for="session in sessions"
          :key="session.id"
          class="flex items-center justify-between gap-4 py-4"
        >
          <div>
            <p class="font-medium">
              {{ session.project?.name || 'Identity project' }}
              <UBadge
                v-if="session.current"
                color="primary"
                variant="soft"
              >
                Current
              </UBadge>
            </p>
            <p class="text-sm text-muted">
              {{ session.client?.name || 'Confidential client' }} · expires {{ session.expires_at }}
            </p>
          </div>
          <UButton
            v-if="!session.current"
            label="Revoke"
            icon="i-lucide-log-out"
            color="error"
            variant="ghost"
            :loading="revokingId === session.id"
            @click="revokeSession(session.id)"
          />
        </div>
      </div>
    </IdentityPanel>
  </div>
</template>
