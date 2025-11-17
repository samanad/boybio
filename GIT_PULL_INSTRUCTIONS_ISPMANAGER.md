# Git Pull Instructions for ISPmanager Debian

This guide will help you pull the latest changes from git on your ISPmanager Debian server.

## Quick Start - Connect to Repository

If this is your first time connecting to the repository, run these commands:

```bash
# 1. SSH into your server
ssh username@your-server-ip

# 2. Navigate to your project directory
cd /var/www/username/data/www/boybio.net/product
# (adjust path - find it in ISPmanager: WWW → WWW-domains → Path column)

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

In ISPmanager, your project is typically located in one of these directories:
- `/var/www/username/data/www/your-domain.com/`
- `/home/username/example.com/`
- `/var/www/username/example.com/`

**To find your exact path:**
1. Log into ISPmanager web interface
2. Go to **WWW** → **WWW-domains**
3. Find your domain and check the **Path** column

**Example:**
```bash
cd /var/www/username/data/www/boybio.net/product
# or wherever your project root is
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

# Navigate to your project directory (if not already there)
cd /var/www/username/data/www/boybio.net/product
# (adjust path based on your ISPmanager setup)

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

If you're already in the project directory and on the backup branch:

```bash
git pull origin backup && echo "Pull completed successfully!"
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
cd /var/www/username/data/www/boybio.net/product
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

## Need Help?

If you encounter issues:
1. Check git status: `git status`
2. Check git log: `git log --oneline -10`
3. Check file permissions: `ls -la`
4. Review error messages carefully

