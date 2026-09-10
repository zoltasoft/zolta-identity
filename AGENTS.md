# Repository agent guidance

## pnpm in CI containers

- Configure a custom pnpm store with the `PNPM_CONFIG_STORE_DIR` environment variable.
- Do not invoke pnpm as `pnpm --store-dir <path> <command>`. pnpm 11 does not accept `--store-dir` as a global option, so this form breaks commands such as `pnpm lint` and `pnpm audit`.
- For the repository CI container, pass `-e PNPM_CONFIG_STORE_DIR=/tmp/pnpm-store` to Docker, use `corepack enable --install-directory <writable-path>` to create pnpm shims, and prepend that directory to `PATH`.
- Do not rely on a shell function that wraps `corepack pnpm`; package scripts run in child shells that do not inherit the function. Invoke commands normally as `pnpm <command>` after enabling the shim.
- Run `./scripts/ci fast` after changing `scripts/ci`, the pre-push hook, package-manager configuration, or dependency metadata.

