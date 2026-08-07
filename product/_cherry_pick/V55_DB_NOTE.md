# V55 Digital Wallets DB

Digital Wallets needs DB tables from the official updater.

On test host after pointing docroot to product69.5:
1. Open /update/ (API updater in v69) OR apply plugin SQL if provided in plugin folder
2. Do NOT run on live product/ until tested

Plugin revert:
  rsync -a product69.5/_cherry_pick/plugins_backup_pre_v69/ product69.5/plugins/
