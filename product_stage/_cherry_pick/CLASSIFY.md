# v60→v63 selective classify (product_stage)

## APPLY
- New gateways: Plisio, Revolut, Klarna, paddle_billing (+ webhooks/helpers)
- payment_processors.php registry update
- Credit notes controllers + admin payment create / taxes import (if views present)
- Additive SQL for payment_processors / payments status columns (staging only)
- Isolated bugfixes after file-by-file review

## SKIP
- Redis / sessions.php / Docker / PHP 8.5
- Full Router rewrite (merge routes only)
- Full language file replace (merge keys only)
- Blind core overwrites (Register/User/Language/CustomHooks)
- AIX plugin full install unless already present

## PRESERVE (must remain)
- CustomHooks, CheckUrlAvailability, Manifest, ServiceWorker, offload_helpers
- Custom Router routes: check-url-availability, sw.js, manifest
- is_explore_things custom SQL (do not remove)
