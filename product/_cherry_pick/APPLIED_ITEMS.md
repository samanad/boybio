# Applied v69 items (one-by-one)

| Date | ID | Result | Purpose |
|------|-----|--------|---------|
| 2026-08-07T12:45:00+03:00 | V150 | applied | Backup all plugins to _cherry_pick/plugins_backup_pre_v69 before any plugin change |
COPIED V20 app/controllers/Favicon.php
| 2026-08-07T12:45:00+03:00 | V20 | applied | Favicon reverse proxy controller — fetch/cache site icons privately |
COPIED V21 themes/altum/assets/images/favicon-service-default.ico
| 2026-08-07T12:45:00+03:00 | V21 | applied | Default empty favicon asset used when no icon found |
COPIED V10 app/controllers/Unsubscribe.php
COPIED V10 themes/altum/views/unsubscribe/index.php
| 2026-08-07T12:45:01+03:00 | V10 | applied | Unsubscribe page for one-click newsletter unsubscribe |
COPIED V11 app/controllers/SentActivation.php
COPIED V11 themes/altum/views/sent-activation/index.php
| 2026-08-07T12:45:01+03:00 | V11 | applied | Sent-activation screen after registration |
COPIED V03 app/controllers/View.php
| 2026-08-07T12:45:01+03:00 | V03 | applied | Secure offline payment-proof viewer (no direct public file URLs) |
COPIED V04 themes/altum/views/guests-payments/guest_payment_approve_modal.php
COPIED V04 themes/altum/views/guests-payments/guest_payment_cancel_modal.php
| 2026-08-07T12:45:01+03:00 | V04 | applied | Guest payment approve/cancel modals |
COPIED V05 themes/altum/views/admin/payments/payment_refund_modal.php
COPIED V05 themes/altum/views/admin/payments/payment_cancel_modal.php
| 2026-08-07T12:45:01+03:00 | V05 | applied | Admin payment refund/cancel modals |
COPIED V01 app/controllers/WebhookPlisioWhitelabel.php
COPIED V01 themes/altum/views/pay/plisio_whitelabel_modal.php
COPIED V01 themes/altum/views/admin/settings/partials/plisio_whitelabel.php
| 2026-08-07T12:45:01+03:00 | V01 | applied | Plisio whitelabel checkout path (additive payment option) |
| 2026-08-07T12:45:01+03:00 | V02 | applied | Verified Coinbase kept (helper+registry+webhook+admin UI) — no v69 removal |
COPIED V110 themes/altum/views/admin/settings/webhooks_secret_key_regenerate_modal.php
| 2026-08-07T12:45:01+03:00 | V110 | applied | Admin webhooks signing-secret regenerate modal |
COPIED V111 themes/altum/views/account-api/api_key_regenerate_modal.php
| 2026-08-07T12:45:01+03:00 | V111 | applied | Confirm modal before regenerating user API key |
COPIED V112 themes/altum/views/admin/partials/admin_health_check.php
| 2026-08-07T12:45:01+03:00 | V112 | applied | Admin health-check UI partial |
COPIED V113 themes/altum/views/admin/partials/admin_smtp_setup.php
| 2026-08-07T12:45:01+03:00 | V113 | applied | Admin SMTP setup guided partial |
COPIED V114 themes/altum/views/admin/taxes-import/index.php
| 2026-08-07T12:45:01+03:00 | V114 | applied | Admin taxes CSV import view |
COPIED V115 themes/altum/views/admin/credit-notes/index.php
| 2026-08-07T12:45:01+03:00 | V115 | applied | Admin credit-notes view |
COPIED V40 themes/altum/views/admin/settings/partials/apple.php
| 2026-08-07T12:45:01+03:00 | V40 | applied | Apple social login admin settings UI |
COPIED V41 themes/altum/views/admin/settings/partials/github.php
| 2026-08-07T12:45:01+03:00 | V41 | applied | GitHub social login admin settings UI |
COPIED V80 app/helpers/66text.php
| 2026-08-07T12:45:01+03:00 | V80 | applied | 66text SMS/contact notification helper |
COPIED V81 app/includes/notification_handlers_dynamic_data.php
| 2026-08-07T12:45:01+03:00 | V81 | applied | Dynamic fields for notification handler templates |
COPIED V132a app/includes/gsa.json
| 2026-08-07T12:45:01+03:00 | V132a | applied | gsa.json static data for SEO helpers |
COPIED V132 app/core/NotFoundException.php
| 2026-08-07T12:45:01+03:00 | V132 | applied | NotFoundException class for proper 404 handling |
COPIED V05 themes/altum/views/admin/payments/payment_refund_modal.php
COPIED V05 themes/altum/views/admin/payments/payment_cancel_modal.php
| 2026-08-07T12:45:45+03:00 | V05 | applied | Admin payment refund/cancel modals |
COPIED V01 app/controllers/WebhookPlisioWhitelabel.php
COPIED V01 themes/altum/views/pay/plisio_whitelabel_modal.php
COPIED V01 themes/altum/views/admin/settings/partials/plisio_whitelabel.php
| 2026-08-07T12:45:46+03:00 | V01 | applied | Plisio whitelabel checkout path (additive payment option) |
COPIED V115 themes/altum/views/admin/credit-notes/index.php
| 2026-08-07T12:45:46+03:00 | V115 | applied | Admin credit-notes view |
COPIED V90 themes/altum/views/link/settings/biolink_blocks/bandcamp/
| 2026-08-07T12:45:46+03:00 | V90 | applied | Bandcamp biolink block editor views (Pro Blocks) |
COPIED V91 themes/altum/views/link/settings/biolink_blocks/bluesky_post/
| 2026-08-07T12:45:46+03:00 | V91 | applied | Bluesky post biolink block editor views (Pro Blocks) |
COPIED V92 themes/altum/views/link/settings/biolink_blocks/canva/
| 2026-08-07T12:45:46+03:00 | V92 | applied | Canva biolink block editor views (Pro Blocks) |
COPIED V93 themes/altum/views/link/settings/biolink_blocks/code/
| 2026-08-07T12:45:46+03:00 | V93 | applied | Code highlighter biolink block editor views (Pro Blocks) |
COPIED V94 themes/altum/views/link/settings/biolink_blocks/google_form/
| 2026-08-07T12:45:46+03:00 | V94 | applied | Google Form biolink block editor views (Pro Blocks) |
COPIED V95 themes/altum/views/link/settings/biolink_blocks/tumblr_post/
| 2026-08-07T12:45:46+03:00 | V95 | applied | Tumblr post biolink block editor views (Pro Blocks) |
COPIED V96 themes/altum/views/link/settings/biolink_blocks/counter/
| 2026-08-07T12:45:46+03:00 | V96 | applied | Counter biolink block editor views (Ultimate Blocks) |
COPIED V97 themes/altum/views/link/settings/biolink_blocks/loading/
| 2026-08-07T12:45:46+03:00 | V97 | applied | Loading bar biolink block editor views (Ultimate Blocks) |
COPIED V98 themes/altum/views/link/settings/biolink_blocks/weather/
| 2026-08-07T12:45:46+03:00 | V98 | applied | Weather biolink block editor views (Ultimate Blocks) |
COPIED V99 themes/altum/views/link/settings/biolink_blocks/image_comparison/
| 2026-08-07T12:45:47+03:00 | V99 | applied | Image comparison biolink block editor views (Ultimate Blocks) |
COPIED V100 themes/altum/views/link/settings/biolink_blocks/featured_link/
| 2026-08-07T12:45:47+03:00 | V100 | applied | Featured link biolink block editor views (Ultimate Blocks) |
COPIED V50 plugins/digital-wallets/config.php
COPIED V50 plugins/digital-wallets/.htaccess
| 2026-08-07T12:45:47+03:00 | V50 | applied | Digital Wallets plugin registration stub |
COPIED V51 app/controllers/DigitalWallets.php
COPIED V51 app/controllers/DigitalWalletCreate.php
COPIED V51 app/controllers/DigitalWalletUpdate.php
COPIED V51 app/controllers/DigitalWalletAdd.php
COPIED V51 app/controllers/admin/AdminDigitalWallets.php
COPIED V51 app/controllers/api/ApiDigitalWallets.php
COPIED V51 app/models/DigitalWallets.php
| 2026-08-07T12:45:47+03:00 | V51 | applied | Digital Wallets controllers, API, admin, model |
COPIED V52 themes/altum/views/digital-wallets/
COPIED V52 themes/altum/views/digital-wallet-create/
COPIED V52 themes/altum/views/digital-wallet-update/
COPIED V52 themes/altum/views/admin/digital-wallets/
COPIED V52 themes/altum/views/admin/statistics/partials/digital_wallets.php
COPIED V52 themes/altum/views/admin/settings/partials/digital_wallets.php
COPIED V52 themes/altum/views/api-documentation/digital_wallets.php
| 2026-08-07T12:45:47+03:00 | V52 | applied | Digital Wallets user/admin/stats/API-docs views + settings UI |
COPIED V54 themes/altum/views/link/settings/biolink_blocks/digital_wallet/
| 2026-08-07T12:45:47+03:00 | V54 | applied | Digital wallet biolink block editor views |
COPIED V60 plugins/chrome-extension/config.php
COPIED V60 plugins/chrome-extension/.htaccess
| 2026-08-07T12:45:47+03:00 | V60 | applied | Chrome Extension plugin registration stub |
COPIED V61 app/controllers/ChromeExtension.php
COPIED V61 themes/altum/views/chrome-extension/index.php
| 2026-08-07T12:45:47+03:00 | V61 | applied | Chrome Extension landing controller + view |
COPIED V62 themes/altum/views/admin/settings/partials/chrome_extension.php
| 2026-08-07T12:45:47+03:00 | V62 | applied | Chrome Extension admin settings UI |
COPIED V70 plugins/aix/config.php
| 2026-08-07T12:45:48+03:00 | V70 | applied | AIX plugin config stub (shows in admin plugins list) |
| 2026-08-07T12:45:48+03:00 | V71 | applied | Full AIX plugin folder from v69 package (as shipped) |
COPIED V151b app/controllers/PwaSplashGenerator.php
| 2026-08-07T12:45:48+03:00 | V151b | applied | PWA splash image generator controller |
COPIED V13b themes/altum/views/partials/cron/projects_email_reports.php
| 2026-08-07T12:45:48+03:00 | V13b | applied | Per-project email reports cron template (v69) |
COPIED V13c themes/altum/views/partials/scroll_top_bottom.php
| 2026-08-07T12:45:48+03:00 | V13c | applied | Scroll top/bottom partial (v69 UI nicety) |
COPIED V05 themes/altum/views/admin/payments/payment_refund_modal.php
COPIED V05 themes/altum/views/admin/payments/payment_cancel_modal.php
| 2026-08-07T12:47:53+03:00 | V05 | applied | Admin payment refund/cancel modals |
COPIED V01 app/controllers/WebhookPlisioWhitelabel.php
COPIED V01 themes/altum/views/pay/plisio_whitelabel_modal.php
COPIED V01 themes/altum/views/admin/settings/partials/plisio_whitelabel.php
| 2026-08-07T12:47:53+03:00 | V01 | applied | Plisio whitelabel checkout path (additive payment option) |
COPIED V115 themes/altum/views/admin/credit-notes/index.php
| 2026-08-07T12:47:53+03:00 | V115 | applied | Admin credit-notes view |
COPIED V90 themes/altum/views/link/settings/biolink_blocks/bandcamp/
| 2026-08-07T12:47:53+03:00 | V90 | applied | Bandcamp biolink block editor views (Pro Blocks) |
COPIED V91 themes/altum/views/link/settings/biolink_blocks/bluesky_post/
| 2026-08-07T12:47:53+03:00 | V91 | applied | Bluesky post biolink block editor views (Pro Blocks) |
COPIED V92 themes/altum/views/link/settings/biolink_blocks/canva/
| 2026-08-07T12:47:53+03:00 | V92 | applied | Canva biolink block editor views (Pro Blocks) |
COPIED V93 themes/altum/views/link/settings/biolink_blocks/code/
| 2026-08-07T12:47:53+03:00 | V93 | applied | Code highlighter biolink block editor views (Pro Blocks) |
COPIED V94 themes/altum/views/link/settings/biolink_blocks/google_form/
| 2026-08-07T12:47:53+03:00 | V94 | applied | Google Form biolink block editor views (Pro Blocks) |
COPIED V95 themes/altum/views/link/settings/biolink_blocks/tumblr_post/
| 2026-08-07T12:47:53+03:00 | V95 | applied | Tumblr post biolink block editor views (Pro Blocks) |
COPIED V96 themes/altum/views/link/settings/biolink_blocks/counter/
| 2026-08-07T12:47:53+03:00 | V96 | applied | Counter biolink block editor views (Ultimate Blocks) |
COPIED V97 themes/altum/views/link/settings/biolink_blocks/loading/
| 2026-08-07T12:47:53+03:00 | V97 | applied | Loading bar biolink block editor views (Ultimate Blocks) |
COPIED V98 themes/altum/views/link/settings/biolink_blocks/weather/
| 2026-08-07T12:47:54+03:00 | V98 | applied | Weather biolink block editor views (Ultimate Blocks) |
COPIED V99 themes/altum/views/link/settings/biolink_blocks/image_comparison/
| 2026-08-07T12:47:54+03:00 | V99 | applied | Image comparison biolink block editor views (Ultimate Blocks) |
COPIED V100 themes/altum/views/link/settings/biolink_blocks/featured_link/
| 2026-08-07T12:47:54+03:00 | V100 | applied | Featured link biolink block editor views (Ultimate Blocks) |
COPIED V50 plugins/digital-wallets/config.php
COPIED V50 plugins/digital-wallets/.htaccess
| 2026-08-07T12:47:54+03:00 | V50 | applied | Digital Wallets plugin registration stub |
COPIED V51 app/controllers/DigitalWallets.php
COPIED V51 app/controllers/DigitalWalletCreate.php
COPIED V51 app/controllers/DigitalWalletUpdate.php
COPIED V51 app/controllers/DigitalWalletAdd.php
COPIED V51 app/controllers/admin/AdminDigitalWallets.php
COPIED V51 app/controllers/api/ApiDigitalWallets.php
COPIED V51 app/models/DigitalWallets.php
| 2026-08-07T12:47:54+03:00 | V51 | applied | Digital Wallets controllers, API, admin, model |
COPIED V52 themes/altum/views/digital-wallets/
COPIED V52 themes/altum/views/digital-wallet-create/
COPIED V52 themes/altum/views/digital-wallet-update/
COPIED V52 themes/altum/views/admin/digital-wallets/
COPIED V52 themes/altum/views/admin/statistics/partials/digital_wallets.php
COPIED V52 themes/altum/views/admin/settings/partials/digital_wallets.php
COPIED V52 themes/altum/views/api-documentation/digital_wallets.php
| 2026-08-07T12:47:55+03:00 | V52 | applied | Digital Wallets user/admin/stats/API-docs views + settings UI |
COPIED V54 themes/altum/views/link/settings/biolink_blocks/digital_wallet/
| 2026-08-07T12:47:55+03:00 | V54 | applied | Digital wallet biolink block editor views |
COPIED V60 plugins/chrome-extension/config.php
COPIED V60 plugins/chrome-extension/.htaccess
| 2026-08-07T12:47:55+03:00 | V60 | applied | Chrome Extension plugin registration stub |
COPIED V61 app/controllers/ChromeExtension.php
COPIED V61 themes/altum/views/chrome-extension/index.php
| 2026-08-07T12:47:55+03:00 | V61 | applied | Chrome Extension landing controller + view |
COPIED V62 themes/altum/views/admin/settings/partials/chrome_extension.php
| 2026-08-07T12:47:55+03:00 | V62 | applied | Chrome Extension admin settings UI |
COPIED V70 plugins/aix/config.php
| 2026-08-07T12:47:55+03:00 | V70 | applied | AIX plugin config stub (shows in admin plugins list) |
| 2026-08-07T12:47:55+03:00 | V71 | applied | Full AIX plugin folder from v69 package (as shipped) |
COPIED V151b app/controllers/PwaSplashGenerator.php
| 2026-08-07T12:47:55+03:00 | V151b | applied | PWA splash image generator controller |
COPIED V13b themes/altum/views/partials/cron/projects_email_reports.php
| 2026-08-07T12:47:55+03:00 | V13b | applied | Per-project email reports cron template (v69) |
COPIED V13c themes/altum/views/partials/scroll_top_bottom.php
| 2026-08-07T12:47:55+03:00 | V13c | applied | Scroll top/bottom partial (v69 UI nicety) |
| 2026-08-07T12:49:03 | V01b | applied | Added plisio_whitelabel key to payment_processors.php (Coinbase kept) |
| 2026-08-07T12:49:03 | V02b | applied | coinbase still present in payment_processors |
| 2026-08-07T12:49:03 | V20r | applied | Route for Favicon reverse proxy (no auth, no sessions, no indexing) |
| 2026-08-07T12:49:03 | V10r | applied | Route for newsletter unsubscribe page |
| 2026-08-07T12:49:03 | V11r | applied | Route for post-registration activation-sent page |
| 2026-08-07T12:49:03 | V03r | applied | Route for secure payment-proof View controller |
| 2026-08-07T12:49:03 | V63 | applied | Route for Chrome Extension landing page |
| 2026-08-07T12:49:03 | V53a | applied | User digital wallets list route |
| 2026-08-07T12:49:03 | V53b | applied | Create digital wallet route |
| 2026-08-07T12:49:03 | V53c | applied | Update digital wallet route |
| 2026-08-07T12:49:03 | V53d | applied | Add-to-wallet route |
| 2026-08-07T12:49:03 | V02r | applied | webhook-coinbase route already present |
| 2026-08-07T12:49:03 | P13 | applied | Custom route check-url-availability present |
| 2026-08-07T12:49:03 | P13 | applied | Custom route sw.js present |
| 2026-08-07T12:49:03 | P13 | applied | Custom route manifest present |
| 2026-08-07T12:49:03 | P14 | applied | Early sw.js/manifest handlers already present |
| 2026-08-07T12:49:43+03:00 | V30 | applied | sessions.php helper — session_has/session_get/session_set API for v69 |
| 2026-08-07T12:49:43 | V31 | applied | init.php: sessions once + NotFoundException + 66text (fixed stock double-require) |
| 2026-08-07T12:49:43 | V82 | applied | init.php requires 66text.php for SMS/contact NH helper |
| 2026-08-07T12:49:43 | V34 | applied | Added CACHE_DRIVER/REDIS_* defines; Redis left disabled |
| 2026-08-07T12:51:40 | V32a | applied | App.php: use session_has('team_id') for teams access check |
| 2026-08-07T12:51:40 | V32b | applied | App.php: use session_has('admin_user_id') for impersonation check |
| 2026-08-07T12:51:40 | V32c | applied | App.php: Referrer-Policy header from admin settings (v64) |
| 2026-08-07T12:51:40 | V132b | skipped | controller call pattern not found for NotFoundException |
| 2026-08-07T12:51:40 | V32d | applied | App.php: fix language switcher cookie vs user.language comparison |
| 2026-08-07T12:51:40 | V32csrf | applied | Csrf::set remains active (not taking stock comment-out) |
| 2026-08-07T12:51:40 | V32 | applied | App.php targeted session_has + referrer + language fix (not wholesale) |
| 2026-08-07T12:51:40 | V33 | applied | Router default allow_sessions=true; per-route flags already on new routes |
| 2026-08-07T12:51:40 | V33b | applied | allow_sessions=false on guest-payment-webhook (webhook/download needs no session) |
| 2026-08-07T12:51:40 | V33b | applied | allow_sessions=false on guest-payment-download (webhook/download needs no session) |
| 2026-08-07T12:52:28 | V132b | FAILED | call_user_func_array not found in App.php |
| 2026-08-07T12:52:28 | V130 | applied | CustomHooks: added return_default_user_preferences() incl claim_url/domain_id |
| 2026-08-07T12:52:28 | V131 | applied | CustomHooks claim_url uses session_set/has/get (aligned with sessions.php) |
| 2026-08-07T12:52:28 | V50p | applied | plugins.php registered: digital-wallets, chrome-extension |
| 2026-08-07T12:52:28 | INC | applied | Updated app/includes/biolink_blocks.php from v69 (registry for new features/blocks/NH) |
| 2026-08-07T12:52:28 | INC | applied | Updated app/includes/biolink_blocks_categories.php from v69 (registry for new features/blocks/NH) |
| 2026-08-07T12:52:28 | INC | applied | Updated app/includes/enabled_biolink_blocks.php from v69 (registry for new features/blocks/NH) |
| 2026-08-07T12:52:28 | INC | applied | Updated app/includes/app_linking.php from v69 (registry for new features/blocks/NH) |
| 2026-08-07T12:52:28 | INC | applied | Updated app/includes/available_notification_handlers.php from v69 (registry for new features/blocks/NH) |
| 2026-08-07T12:52:28 | INC | applied | Updated app/includes/notification_handlers.php from v69 (registry for new features/blocks/NH) |
| 2026-08-07T12:52:28 | INC | applied | Updated app/includes/available_plan_features.php from v69 (registry for new features/blocks/NH) |
| 2026-08-07T12:52:28 | V69waze | applied | app_linking.php from v69 includes Waze support for short URLs |
| 2026-08-07T12:53:22 | V132b | applied | App.php NotFoundException catch around controller call |
| 2026-08-07T12:53:22 | V70p | applied | aix already in plugins.php |
| 2026-08-07T12:53:23 | V140 | applied | Language base from v69 for app/languages/english#en.php |
| 2026-08-07T12:53:23 | V142 | applied | Re-applied 53 custom/stage-only language keys |
| 2026-08-07T12:53:23 | V141 | applied | Language base from v69 for app/languages/admin/english#en.php |
| 2026-08-07T12:53:23 | V142a | applied | Re-applied 60 custom/stage-only language keys |
| 2026-08-07T12:55:53+03:00 | V35 | applied | Cache.php from v69 (APCu/Redis prefix support; config still files/Redis off) |
| 2026-08-07T12:55:53+03:00 | V06 | applied | WebhookRazorpay.php from v69 (multi-script / payment fixes) |
| 2026-08-07T12:55:53+03:00 | V07 | applied | WebhookYookassa + WebhookMyfatoorah from v69 (capture/security fixes) |
| 2026-08-07T12:55:53+03:00 | V43s | applied | Csrf.php uses session_get/session_set (works with sessions helper) |
| 2026-08-07T12:55:53+03:00 | V68honeypot | applied | Captcha.php from v69 (dynamic honeypot anti-spam) |
| 2026-08-07T12:55:53+03:00 | V42sso | applied | SSO.php from v69 (social login callback improvements) |
| 2026-08-07T12:55:53+03:00 | V69qr | applied | QrCodeCreate.php from v69 (auto-create dynamic short URL from QR) |
| 2026-08-07T12:55:53+03:00 | V12 | applied | Cron.php from v69 (list-unsubscribe + project email reports + broadcast limits) |
| 2026-08-07T12:55:53+03:00 | V13 | applied | Broadcast max-per-cron via Cron.php v69 |
| 2026-08-07T12:55:53+03:00 | V14 | applied | Broadcasts enable/disable support comes with Cron/settings (see AdminSettings merge) |
| 2026-08-07T12:55:53+03:00 | V11r2 | applied | Register.php from v69 (sent-activation redirect) |
| 2026-08-07T12:56:37+03:00 | V12 | applied | Cron.php from v69 (list-unsubscribe + project reports + limits) |
| 2026-08-07T12:56:37+03:00 | V11reg | applied | Register.php from v69 (activation/sent-activation flow; claim still via CustomHooks) |
| 2026-08-07T12:58:05 | V43 | applied | Authentication.php: session_* API + KEPT google_persistent_ip / biolink_edit_allowed_ip customs |
| 2026-08-07T12:58:05 | V43v | applied | Verified Authentication still has security IP customs + session helpers |
| 2026-08-07T12:58:05 | V41m | applied | AdminSettings::github() — save GitHub OAuth settings |
| 2026-08-07T12:58:05 | V40m | applied | AdminSettings::apple() — save Apple OAuth settings |
| 2026-08-07T12:58:05 | V62m | applied | AdminSettings::chrome_extension() — save extension settings |
| 2026-08-07T12:58:05 | V52m | applied | AdminSettings::digital_wallets() — save wallet plugin settings |
| 2026-08-07T12:58:05 | V32ref | applied | AdminSettings main(): save referrer_policy |
| 2026-08-07T12:58:05 | V14b | applied | AdminSettings: broadcasts_is_enabled save key added |
| 2026-08-07T12:58:05 | P8 | applied | AdminSettings security() still present after merges |
| 2026-08-07T12:58:05 | V53admin | applied | Admin route digital-wallets -> AdminDigitalWallets |
| 2026-08-07T12:58:05 | V53api | applied | API route digital-wallets -> ApiDigitalWallets |
| 2026-08-07T13:01:09 | V01fix | applied | Fixed payment_processors: proper plisio_whitelabel block + Coinbase kept |
| 2026-08-07T13:01:09 | V43fix | applied | Authentication.php carefully converted to session_* (customs kept) |
| 2026-08-07T13:01:44 | V43fix2 | applied | Authentication session convert with == handled before assignments |
| 2026-08-07T13:02:47 | V120 | skipped | SEO markers not clear in Link.php |
| 2026-08-07T13:02:47 | V122 | applied | enabled via biolink/plan includes from v69; verify admin plan settings after test |
| 2026-08-07T13:02:47 | V123 | applied | enabled via biolink/plan includes from v69; verify admin plan settings after test |
| 2026-08-07T13:02:47 | V08 | skipped | Stripe tax exists in v69 Pay.php but not merging wholesale (would risk Coinbase); enable later if needed |
| 2026-08-07T13:02:47 | V55 | applied | Documented DB migration note for Digital Wallets (run /update/ on test only) |
| 2026-08-07T13:02:47 | V151 | applied | PWA plugin: kept your custom views (pwa_custom etc.); stock pwa had no other file deltas |
| 2026-08-07T13:02:47 | V152 | applied | pro/ultimate/payment-blocks identical to v69 in this package — no plugin zip replace needed; revert backup ready |
| 2026-08-07T13:02:47 | V153 | applied | digital-wallets plugin folder installed (backup in plugins_backup_pre_v69) |
| 2026-08-07T13:02:47 | V154 | applied | chrome-extension plugin folder installed |
| 2026-08-07T13:02:47 | V155 | applied | aix plugin folder installed |
| 2026-08-07T13:02:47 | V83 | applied | notification_handlers*.php from v69 (includes 66text connection points) |
| 2026-08-07T13:02:47 | V121 | applied | Copied 6 link settings views touching password/sensitive SEO from v69 |
| 2026-08-07T13:04:45 | V42 | applied | Injected GitHub+Apple OAuth blocks into Login.php (kept persistent Google login customs) |
| 2026-08-07T13:04:45 | V43login | applied | Social login security patterns from v69 apple/github blocks (nonce, logging, 2FA-ready providers list in stock — customs kept) |
| 2026-08-07T13:04:45 | V32ui | applied | Admin main/content settings views from v69 (referrer_policy + broadcasts UI) |
| 2026-08-07T13:05:30+03:00 | V40s | applied | admin_socials.php from v69 (Apple/GitHub in social providers list) |
