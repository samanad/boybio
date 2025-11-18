# PWA (Progressive Web App) Capabilities Report

## ✅ **YES - The script HAS PWA capabilities!**

Your application includes a comprehensive **PWA (Progressive Web App) system** that allows users to install your website as an app on Android, iOS, and desktop devices.

---

## 📱 **PWA Features Available**

### 1. **Main PWA System** (Site-wide)
- **Location:** Admin Settings → PWA
- **Status:** Plugin-based (requires PWA plugin activation)
- **Features:**
  - ✅ Web App Manifest generation (`manifest.json`)
  - ✅ Service Worker support (`sw.js`)
  - ✅ App installation prompts
  - ✅ Install helper bar (customizable display)
  - ✅ Theme color customization
  - ✅ Background color for splash screen
  - ✅ App icons (512x512px)
  - ✅ Maskable icons (for Android adaptive icons)
  - ✅ Mobile screenshots (up to 6)
  - ✅ Desktop screenshots (up to 8)
  - ✅ App shortcuts (up to 3)
  - ✅ UTM tracking for installs

### 2. **Custom PWA for Biolink Pages** (Per-user)
- **Location:** Individual biolink page settings
- **Requires:** Plan feature `custom_pwa_is_enabled`
- **Features:**
  - ✅ Custom manifest per biolink page
  - ✅ Custom app icon per biolink
  - ✅ Custom theme color per biolink
  - ✅ Custom install bar per biolink
  - ✅ Customizable install bar delay
  - ✅ UTM tracking per installation

### 3. **Install Bar Features**
- ✅ Display install helper bar in footer
- ✅ Show for guests (optional)
- ✅ Configurable delay before showing
- ✅ Minimum pageviews count before showing
- ✅ Platform-specific installation instructions:
  - Android Chrome
  - iOS Safari
  - Desktop browsers

### 4. **Service Worker Support**
- ✅ Service Worker controller (`ServiceWorker.php`)
- ✅ Custom service worker file upload
- ✅ 66pusher integration support
- ✅ Automatic service worker registration

---

## 🔧 **How to Enable PWA**

### Step 1: Activate PWA Plugin
1. Go to **Admin Panel → Plugins**
2. Find and activate the **"PWA system"** plugin
3. Plugin version: 4.0.0

### Step 2: Configure Main PWA Settings
1. Go to **Admin Settings → PWA**
2. Enable PWA system
3. Configure:
   - App name
   - Short app name (max 12 characters)
   - App description
   - App start URL
   - Theme color
   - Background color
   - App icon (512x512px)
   - Maskable icon (optional, 512x512px)
   - Mobile screenshots (optional)
   - Desktop screenshots (optional)
   - Shortcuts (optional)

### Step 3: Enable Install Bar (Optional)
- Enable "Display install helper bar in the footer"
- Set display delay (in seconds)
- Set minimum pageviews count
- Choose to show for guests or not

### Step 4: Upload Service Worker (Optional)
- Upload a custom `sw.js` file
- Or use the default empty service worker

---

## 📋 **PWA Configuration Options**

### Main Settings:
- **App Name:** Full name shown on home screen
- **Short App Name:** Shown when space is limited (max 12 chars)
- **App Description:** Shown in app stores/install prompts
- **App Start URL:** Where app opens when launched
- **Theme Color:** Browser UI color
- **Background Color:** Splash screen color
- **App Icon:** 512x512px icon
- **Maskable Icon:** 512x512px adaptive icon for Android

### Install Bar Settings:
- **Display Install Bar:** Show/hide install prompt
- **Display for Guests:** Show to non-logged-in users
- **Display Delay:** Seconds before showing (default: 3)
- **Minimum Pageviews:** Pageviews required before showing

### Screenshots (Optional):
- **Mobile Screenshots:** Up to 6 screenshots
- **Desktop Screenshots:** Up to 8 screenshots
- All screenshots must be same size

### Shortcuts (Optional):
- Up to 3 app shortcuts
- Each shortcut has:
  - Name
  - Description
  - URL
  - Icon (192x192px)

---

## 🎯 **Mobile Installation Support**

### ✅ **Android Support:**
- Chrome browser: Full support
- Install prompt appears automatically
- "Add to Home screen" option
- App icon on home screen
- Standalone app experience

### ✅ **iOS Support:**
- Safari browser: Full support
- "Add to Home Screen" option
- App icon on home screen
- Standalone app experience
- Custom install instructions provided

### ✅ **Desktop Support:**
- Chrome/Edge: Install button in address bar
- Standalone window mode
- App shortcuts support

---

## 🔍 **Technical Implementation**

### Files Involved:
1. **Service Worker Controller:**
   - `product/app/controllers/ServiceWorker.php`
   - Handles service worker file serving

2. **PWA Settings Controller:**
   - `product/app/controllers/admin/AdminSettings.php` (pwa method)
   - Handles PWA configuration

3. **Manifest Generation:**
   - Functions: `pwa_generate_manifest()`, `pwa_save_manifest()`
   - Generates `manifest.json` files

4. **View Integration:**
   - `product/themes/altum/views/app_wrapper.php` - Main app wrapper
   - `product/themes/altum/views/l/biolink_wrapper.php` - Biolink wrapper
   - Includes manifest links and install bars

5. **PWA Plugin:**
   - `product/plugins/pwa/config.php`
   - Plugin configuration

### Manifest.json Features:
- ✅ App name and short name
- ✅ Description
- ✅ Start URL (with UTM tracking)
- ✅ Scope
- ✅ Icons (regular and maskable)
- ✅ Theme color
- ✅ Background color
- ✅ Display mode (standalone)
- ✅ Screenshots
- ✅ Shortcuts

---

## 📱 **User Experience**

### For End Users:
1. Visit website on mobile device
2. See install prompt (if enabled)
3. Tap "Add to Home Screen" or install button
4. App icon appears on home screen
5. Launch app like native app
6. App opens in standalone mode (no browser UI)

### For Biolink Page Owners:
1. Enable custom PWA in plan settings
2. Go to biolink page settings
3. Enable PWA for that specific page
4. Upload custom icon
5. Set custom theme color
6. Configure install bar
7. Users can install that specific biolink as an app

---

## ⚠️ **Requirements**

### For PWA to Work:
1. ✅ HTTPS required (secure connection)
2. ✅ PWA plugin must be activated
3. ✅ Service worker file (can be empty default)
4. ✅ Manifest.json file (auto-generated)
5. ✅ App icon uploaded (512x512px)

### Browser Support:
- ✅ Chrome (Android & Desktop)
- ✅ Edge (Desktop)
- ✅ Safari (iOS)
- ✅ Firefox (Limited)
- ⚠️ Some features may vary by browser

---

## 🚀 **Installation Flow**

1. **User visits website** → Service worker registered
2. **Manifest detected** → Browser shows install prompt
3. **User taps install** → App added to home screen
4. **App launches** → Opens in standalone mode
5. **UTM tracking** → `utm_source=pwa&utm_medium=web-app&utm_campaign=install-or-pwa-launch`

---

## 📊 **Summary**

**YES, your script has full PWA capabilities including:**
- ✅ Android app installation
- ✅ iOS app installation  
- ✅ Desktop app installation
- ✅ Custom PWA per biolink page
- ✅ Install prompts and helper bars
- ✅ Service worker support
- ✅ Manifest generation
- ✅ App icons and screenshots
- ✅ App shortcuts
- ✅ UTM tracking

**To activate:** Enable the PWA plugin in Admin → Plugins, then configure it in Admin Settings → PWA.

---

## 📝 **Note**

The PWA plugin views (`views/partials/pwa.php` and `views/partials/pwa_custom.php`) are referenced in the code but may need to be part of the PWA plugin package. If these files are missing, you may need to:
1. Check if PWA plugin is properly installed
2. Verify plugin files are in `product/plugins/pwa/views/`
3. Contact plugin provider if files are missing


