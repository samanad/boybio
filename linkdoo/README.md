# LinkDooni Landing Page

This folder contains the custom landing page for `linkdooni.com` that redirects all functionality to `cloub.io`.

## Setup Instructions

1. **Upload files to linkdooni.com root:**
   - Upload all files from this `linkdoo` folder to the root directory of `linkdooni.com`
   - Make sure `index.php` and `.htaccess` are in the root

2. **Configure in Admin Panel:**
   - Go to Admin → Settings → Main
   - Set "Custom index page URL" to: `https://linkdooni.com`
   - Save settings

3. **How it works:**
   - When users visit `linkdooni.com`, they see the custom landing page
   - All other routes (login, directory, dashboard, etc.) automatically redirect to `cloub.io`
   - The landing page has buttons that link directly to `cloub.io` features

## Files

- `index.php` - Custom landing page for linkdooni.com
- `.htaccess` - Apache rewrite rules to redirect all non-index requests to cloub.io
- `README.md` - This file

## Customization

You can customize the landing page by editing `index.php`:
- Change colors, text, and styling
- Add more features or sections
- Modify the buttons and links

All links should point to `https://cloub.io` for functionality.

## Design Features

The current design features:
- Elegant, fashion-forward aesthetic
- Modern gradient backgrounds
- Smooth animations and transitions
- Responsive design for all devices
- Fast loading with optimized assets


