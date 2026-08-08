# product69.5 apply summary

- Plan items listed: **74** unique V-IDs
- Log applied rows: **161**
- Log skipped rows: **3**
- Log failed rows: **1**

## Intentionally careful / skipped wholesale
- **V08** Stripe automatic tax — not wholesale Pay.php (would risk Coinbase)
- **V120** Link.php model wholesale — kept stage due to possible customs; SEO views (V121) copied
- Stock **plisio_whitelabel** was commented in v69 processors — we added a proper enabled block
- Stock App.php CSRF comment-out — **not taken**; Csrf::set kept
- **security.php** + AdminSettings::security() — preserved
- Customs PWA/claim/anchor/scroll/Coinbase — preserved

## Plugin revert
```bash
rsync -a product69.5/_cherry_pick/plugins_backup_pre_v69/ product69.5/plugins/
```

## Next
1. Test product69.5 locally/on server (not overwriting live product/)
2. Run /update/ on test after DB backup (Digital Wallets tables etc.)
3. If OK, point root domain to product69.5

## Hotfix 2026-08-08 (go-live blocker)
- Restored missing `spl_autoload_register` in `app/init.php` (root cause of HTTP 500 after selective pull).
- Restored pre-v69 Files `Cache.php` (avoid Redis/vendor mismatch on live).
- Added captcha font `themes/altum/assets/fonts/Inter-Bold.ttf`.
- Dropped `gsa.json` from selective list.
- See `DEPLOY_SAFE.md` for server steps.
