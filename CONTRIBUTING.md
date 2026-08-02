# Contributing

Thanks for helping improve Laravel Zolta Identity.

## Development workflow

1. Fork the repository and create a focused branch from `main`.
2. Follow the setup instructions in the root README.
3. Keep changes within the existing Nuxt layer or Laravel service-slice boundaries.
4. Add or update tests for behavior changes.
5. Run the local checks before opening a pull request:

   ```bash
   pnpm check
   pnpm build
   ```

6. Describe the user-visible impact, migration needs, and security implications in the pull request.

Use focused commits with imperative messages. Do not commit environment files, credentials, databases, dependency directories, build output, or generated Laravel cache files.

## Architecture expectations

- Browser code calls the Nuxt BFF; it does not call Laravel or third-party services directly.
- Feature-specific Nuxt code stays in its feature layer, while genuinely shared foundations stay in the root app.
- Laravel HTTP declarations, application orchestration, domain behavior, and infrastructure persistence remain separated in the `UserManagementService` slice.
- Authentication, authorization, token rotation, and introspection changes require focused feature coverage.

## Security reports

Do not open public issues for suspected vulnerabilities. Follow the private reporting process in `SECURITY.md`.
