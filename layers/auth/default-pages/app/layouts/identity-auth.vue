<template>
  <main class="identity-auth-layout">
    <div class="identity-auth-controls">
      <IdentityConsoleControls />
    </div>
    <slot />
  </main>
</template>

<style>
:root {
  --identity-auth-bg: #f6f7fb;
  --identity-auth-card: #fff;
  --identity-auth-text: #172033;
  --identity-auth-muted: #59657b;
  --identity-auth-border: #e1e5ee;
  --identity-auth-input-border: #cdd3df;
  --identity-auth-status: #f1f4fb;
  color-scheme: light;
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.dark {
  --identity-auth-bg: #0f172a;
  --identity-auth-card: #111827;
  --identity-auth-text: #f8fafc;
  --identity-auth-muted: #cbd5e1;
  --identity-auth-border: #334155;
  --identity-auth-input-border: #475569;
  --identity-auth-status: #1e293b;
  color-scheme: dark;
}

body {
  margin: 0;
  background: var(--identity-auth-bg);
  color: var(--identity-auth-text);
}

.identity-auth-controls {
  position: absolute;
  inset-block-start: 1rem;
  inset-inline-end: 1rem;
}

.identity-auth-layout {
  box-sizing: border-box;
  display: grid;
  min-height: 100vh;
  place-items: center;
  padding: 2rem 1rem;
  position: relative;
}

.identity-auth-card {
  box-sizing: border-box;
  width: min(100%, 28rem);
  border: 1px solid var(--identity-auth-border);
  border-radius: 1rem;
  background: var(--identity-auth-card);
  box-shadow: 0 1rem 3rem rgba(23, 32, 51, 0.08);
  padding: 2rem;
}

.identity-auth-eyebrow {
  margin: 0 0 0.5rem;
  color: var(--identity-auth-muted);
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.identity-auth-card h1 {
  margin: 0;
  font-size: 1.75rem;
}

.identity-auth-intro {
  margin: 0.75rem 0 1.5rem;
  color: var(--identity-auth-muted);
  line-height: 1.5;
}

.identity-auth-form {
  display: grid;
  gap: 1rem;
}

.identity-auth-field {
  display: grid;
  gap: 0.4rem;
  font-size: 0.9rem;
  font-weight: 650;
}

.identity-auth-field input {
  min-height: 2.75rem;
  box-sizing: border-box;
  border: 1px solid var(--identity-auth-input-border);
  background: var(--identity-auth-card);
  border-radius: 0.65rem;
  padding: 0.7rem 0.8rem;
  color: inherit;
  font: inherit;
  font-weight: 400;
}

.identity-auth-field input:focus {
  border-color: #3157d5;
  outline: 3px solid rgba(49, 87, 213, 0.14);
}

.identity-auth-button {
  min-height: 2.75rem;
  border: 0;
  border-radius: 0.65rem;
  background: #3157d5;
  color: #fff;
  cursor: pointer;
  font: inherit;
  font-weight: 700;
}

.identity-auth-button:disabled {
  cursor: wait;
  opacity: 0.65;
}

.identity-auth-error,
.identity-auth-success,
.identity-auth-status {
  border-radius: 0.65rem;
  margin: 0;
  padding: 0.75rem;
  font-size: 0.9rem;
  line-height: 1.4;
}

.identity-auth-error {
  background: #fff0f0;
  color: #9f2525;
}

.identity-auth-success {
  background: #edf9f1;
  color: #22643a;
}

.identity-auth-status {
  background: var(--identity-auth-status);
  color: var(--identity-auth-muted);
}

.identity-auth-demo {
  display: grid;
  gap: 1rem;
  margin-top: 1.25rem;
  border-top: 1px solid var(--identity-auth-border);
  padding-top: 1.25rem;
}

.identity-auth-form + .identity-auth-demo {
  margin-top: 1.5rem;
}

.identity-auth-button--secondary {
  background: #172033;
}

.identity-auth-expiry {
  margin: 0;
  color: var(--identity-auth-muted);
  font-size: 0.85rem;
  line-height: 1.45;
}

.identity-auth-field input[readonly] {
  background: var(--identity-auth-bg);
  color: var(--identity-auth-muted);
}

.identity-auth-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1rem;
  justify-content: center;
  margin: 1.25rem 0 0;
  font-size: 0.9rem;
}

.identity-auth-links a {
  color: #3157d5;
  text-decoration: none;
}

.identity-auth-links a:hover {
  text-decoration: underline;
}
</style>
