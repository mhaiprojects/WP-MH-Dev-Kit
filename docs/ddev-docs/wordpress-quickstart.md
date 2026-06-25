# DDEV WordPress Quickstart

> Source: https://docs.ddev.com/en/stable/users/quickstart/#wordpress (saved 2025-06-25)

DDEV has built-in support for WP-CLI. Three approaches: **WP-CLI**, **Bedrock**, **Git clone**.

## WP-CLI (standard WordPress) — used by this project

### 1. Create project directory and configure DDEV

```bash
mkdir -p my-wp-cli-site && cd my-wp-cli-site
# Primary URL automatically set to https://<folder>.ddev.site
ddev config --project-type=wordpress
```

Use `--project-name=` to override the URL segment (this project uses `cursor-wpdev2`).

### 2. Start DDEV

```bash
ddev start
```

### 3. Download WordPress core

```bash
ddev wp core download
```

### 4. Install WordPress

Browser option:

```bash
ddev launch
```

CLI option (single quotes required so `$DDEV_PRIMARY_URL` expands inside the container):

```bash
ddev wp core install \
  --url='$DDEV_PRIMARY_URL' \
  --title='My WordPress site' \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.com
```

### 5. Open admin

```bash
ddev launch wp-admin/
```

### Full setup script (from official docs)

```bash
cat > setup-wordpress.sh << 'EOF'
#!/usr/bin/env bash
set -euo pipefail
mkdir -p my-wp-cli-site && cd my-wp-cli-site
ddev config --project-type=wordpress
ddev start -y
ddev wp core download
ddev wp core install --url='$DDEV_PRIMARY_URL' --title='My WordPress site' --admin_user=admin --admin_password=admin --admin_email=admin@example.com
ddev launch wp-admin/
EOF
chmod +x setup-wordpress.sh
./setup-wordpress.sh
```

## Existing WordPress project (git clone)

```bash
PROJECT_GIT_URL=https://github.com/ddev/test-wordpress.git
git clone ${PROJECT_GIT_URL} my-wp-git-site
cd my-wp-git-site
ddev config --project-type=wordpress
ddev start
ddev wp core install --url='$DDEV_PRIMARY_URL' --title='My WordPress site' --admin_user=admin --admin_password=admin --admin_email=admin@example.com
ddev launch wp-admin/
```

DDEV may write `wp-config-ddev.php` and prompt you to include it from `wp-config.php`. DDEV auto-manages this for new installs.

## Bedrock (Composer-based — not used here)

```bash
mkdir -p my-wp-bedrock-site && cd my-wp-bedrock-site
ddev config --project-type=wp-bedrock
ddev start
ddev composer create-project roots/bedrock
ddev wp core install --url='$DDEV_PRIMARY_URL' --title='My Bedrock Site' --admin_user=admin --admin_password=admin --admin_email=admin@example.com
ddev launch /wp/wp-admin/
```
