# Git Pull Instructions for ISPmanager Debian

This guide will help you pull the latest changes from git on your ISPmanager Debian server.

## Quick Start - Connect to Repository

**Important:** The git repository should be in the `boybio.net` directory (root), and the `product` folder should be inside it.

**Structure:**
```
boybio.net/
  .git/              (git repository root)
  product/           (application files)
    app/
    themes/
    ...
```

If this is your first time connecting to the repository, run these commands:

```bash
# 1. SSH into your server
ssh username@your-server-ip

# 2. Navigate to your domain root directory (NOT the product folder)
cd /var/www/username/data/www/boybio.net
# (adjust path - find it in ISPmanager: WWW → WWW-domains → Path column)
# Make sure you're in boybio.net, NOT boybio.net/product

# 3. Check if git is installed
git --version

# 4. If not a git repo, initialize and connect:
git init
git remote add origin https://github.com/samanad/boybio.git
git fetch origin
git checkout -b backup origin/backup

# 5. If already a git repo, just check remote:
git remote -v
# Should show: https://github.com/samanad/boybio.git

# 6. Pull latest changes
git pull origin backup
```

---

## Prerequisites

- SSH access to your server
- Git installed on the server
- Your project directory path

---

## Step 1: Connect to Your Server via SSH

```bash
ssh username@your-server-ip
# or
ssh username@your-domain.com
```

---

## Step 2: Navigate to Your Project Directory

**Important:** Navigate to the domain root (`boybio.net`), NOT the `product` folder. The git repository should be at the domain root level.

In ISPmanager, your project is typically located in one of these directories:
- `/var/www/username/data/www/boybio.net/` ← **This is where git should be**
- `/home/username/boybio.net/`
- `/var/www/username/boybio.net/`

**To find your exact path:**
1. Log into ISPmanager web interface
2. Go to **WWW** → **WWW-domains**
3. Find your domain and check the **Path** column
4. Use that path directly (don't add `/product` to it)

**Example:**
```bash
# Correct - git repository root
cd /var/www/username/data/www/boybio.net

# Wrong - don't go into product folder
# cd /var/www/username/data/www/boybio.net/product
```

**Directory Structure:**
```
boybio.net/                    ← Git repository root (initialize git here)
├── .git/                      ← Git folder
├── product/                   ← Application files (pulled from git)
│   ├── app/
│   ├── themes/
│   └── ...
├── README.md
└── ...
```

---

## Step 3: Connect to Git Repository

### Option A: If Git Repository Already Exists

```bash
# Check if you're in a git repository
git status

# Check current branch
git branch

# Check remote repository
git remote -v
```

If you see the remote URL `https://github.com/samanad/boybio.git`, you're already connected! Skip to Step 4.

### Option B: If Git Repository Doesn't Exist (First Time Setup)

```bash
# Check if git is installed
git --version

# If not installed, install it:
sudo apt-get update
sudo apt-get install git -y

# Navigate to your domain root directory (NOT the product folder)
cd /var/www/username/data/www/boybio.net
# (adjust path based on your ISPmanager setup)
# Important: This should be the domain root, not the product subfolder

# Initialize git repository (if not already initialized)
git init

# Add the remote repository
git remote add origin https://github.com/samanad/boybio.git

# Verify remote was added
git remote -v

# Fetch from remote
git fetch origin

# Checkout the backup branch
git checkout -b backup origin/backup

# Or if branch already exists locally:
git checkout backup
git branch --set-upstream-to=origin/backup backup
```

### Option C: If Remote is Wrong or Missing

```bash
# Remove existing remote (if any)
git remote remove origin

# Add correct remote
git remote add origin https://github.com/samanad/boybio.git

# Verify
git remote -v

# Fetch branches
git fetch origin

# Switch to backup branch
git checkout backup
```

---

## Step 4: Pull Latest Changes

### Option A: Simple Pull (if no local changes)

```bash
# Make sure you're on the backup branch
git checkout backup

# Pull latest changes
git pull origin backup
```

### Option B: Pull with Stash (if you have local changes)

```bash
# Stash any local changes
git stash

# Pull latest changes
git pull origin backup

# If you had local changes, restore them (optional)
git stash pop
```

### Option C: Force Pull (if you want to overwrite local changes)

⚠️ **Warning:** This will discard any local changes!

```bash
# Reset to match remote exactly
git fetch origin backup
git reset --hard origin/backup
```

---

## Step 5: Verify the Pull

```bash
# Check recent commits
git log --oneline -5

# Check if files are updated
git status
```

---

## Step 6: Set File Permissions (if needed)

After pulling, you may need to set correct permissions:

```bash
# Set ownership (replace username with your actual user)
sudo chown -R username:username /var/www/username/data/www/boybio.net/product

# Set directory permissions
find /var/www/username/data/www/boybio.net/product -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/username/data/www/boybio.net/product -type f -exec chmod 644 {} \;

# Make specific files executable if needed
chmod +x /var/www/username/data/www/boybio.net/product/clear_cache.php
```

---

## Troubleshooting

### Issue: "Permission denied" errors

**Solution:**
```bash
# Check current user
whoami

# Check file ownership
ls -la

# Fix ownership (replace username with your actual user)
sudo chown -R username:username .
```

### Issue: "Not a git repository"

**Solution:**
```bash
# Initialize git if needed (only if starting fresh)
git init
git remote add origin https://github.com/samanad/boybio.git
git fetch origin
git checkout -b backup origin/backup
```

### Issue: Merge conflicts

**Solution:**
```bash
# See conflicted files
git status

# Resolve conflicts manually, then:
git add .
git commit -m "Resolve merge conflicts"
```

### Issue: Need to update specific files only

**Solution:**
```bash
# Pull specific files
git checkout origin/backup -- path/to/file.php
```

---

## Quick One-Liner Command

If you're already in the domain root directory (`boybio.net`) and on the backup branch:

```bash
cd /var/www/username/data/www/boybio.net && git pull origin backup && echo "Pull completed successfully!"
```

---

## Automated Pull Script (Optional)

Create a script for easier pulling:

```bash
# Create script
nano ~/pull-updates.sh
```

Add this content:
```bash
#!/bin/bash
cd /var/www/username/data/www/boybio.net
git pull origin backup
echo "Updates pulled successfully!"
```

Make it executable:
```bash
chmod +x ~/pull-updates.sh
```

Run it:
```bash
~/pull-updates.sh
```

---

## Files Updated in Latest Pull

The latest pull includes these changes:

1. **Biolink Edit Features:**
   - Admin Settings → Links: "Enable edit link in branding" option
   - Branding HTML links now point to edit page when enabled

2. **Custom HTML Block:**
   - Removed character limit from custom HTML embed block

3. **Admin User Update Fix:**
   - Fixed form submission issue with hidden fields

4. **Cloudflare IP Detection:**
   - Updated `get_ip()` function to prioritize Cloudflare headers

---

## Important Notes

- **Always backup before pulling** if you have custom changes
- **Test after pulling** to ensure everything works
- **Check file permissions** after pulling
- **Clear cache** if your application uses caching:
  ```bash
  php /var/www/username/data/www/boybio.net/product/clear_cache.php
  ```

---

## Important: Directory Structure

**Correct Structure:**
```
/var/www/username/data/www/boybio.net/     ← Git repository root (.git folder here)
├── .git/
├── product/                               ← Application files
│   ├── app/
│   ├── themes/
│   └── ...
├── README.md
└── ...
```

**When pulling updates:**
- Always run `git pull` from `boybio.net/` (domain root)
- The `product/` folder will be updated automatically
- Your web server should point to `boybio.net/product/` as the document root

---

## Need Help?

If you encounter issues:
1. Check git status: `git status`
2. Check git log: `git log --oneline -10`
3. Check file permissions: `ls -la`
4. Review error messages carefully

