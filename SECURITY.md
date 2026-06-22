# Security

## Supported versions

| Version | Status              |
|---------|---------------------|
| 2.x     | Active support      |
| 1.x     | Security fixes only |

Security fixes are released on the latest `2.x` tag.

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security-sensitive
findings. Instead, email **opensource@simtabi.com** with:

- A description of the vulnerability and its impact.
- Steps to reproduce (proof-of-concept welcome).
- The affected version(s).

We aim to acknowledge reports within 72 hours and triage within 5
business days. Coordinated disclosure timelines are negotiated per case.

## Supply-chain posture

- `roave/security-advisories` (dev-latest) is in `require-dev` —
  composer install fails if any registered package has an open advisory.
- Weekly `composer audit` (GitHub/Packagist advisory database) runs in CI
  (`.github/workflows/security.yml`), failing the build on any advisory.
- Dependabot updates `composer` + `github-actions` weekly with
  Conventional Commits prefixes.
