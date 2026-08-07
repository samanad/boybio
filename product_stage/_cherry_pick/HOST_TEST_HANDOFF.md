# Test root: product_stage (product/ stays live)

## On server
1. Keep `product/` as the live app (do not point domain here for this test).
2. Run `bash pull-product-stage-test.sh` from the site root (creates `product_stage` from `product` once, then overlays git files).
3. In ISPmanager, set the domain document root to `.../boybio.net/product_stage` (or your domain path).
4. Adjust `product_stage/config.php` SITE_URL if needed.
5. Run `/update/` or import `FINAL_READY.sql`.
6. If OK: keep domain on `product_stage` or later promote files into `product/`.
7. If bad: point domain back to `product/`.
