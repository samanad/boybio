<?php defined('ALTUMCODE') || die(); ?>

<?php
$id = $id ?? 'pwa-install-bar';
$display_delay = $display_delay ?? 0;
?>

<div id="<?= $id ?>" class="pwa-install-bar" style="display: none;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-fw fa-mobile-alt mr-2"></i>
                <div>
                    <strong><?= l('pwa_install.header') ?></strong>
                    <div class="small text-muted pwa-install-instructions"></div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-primary mr-2 pwa-install-button" style="display: none;">
                    Install
                </button>
                <button type="button" class="btn btn-sm btn-link text-muted pwa-install-close" onclick="document.getElementById('<?= $id ?>').style.display='none';">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
'use strict';

(function() {
    const installBar = document.getElementById('<?= $id ?>');
    if(!installBar) return;
    
    const installButton = installBar.querySelector('.pwa-install-button');
    const instructionsElement = installBar.querySelector('.pwa-install-instructions');
    
    let deferredPrompt;
    
    // Detect device/browser and show appropriate instructions
    function getInstallInstructions() {
        const userAgent = navigator.userAgent.toLowerCase();
        const isIOS = /iphone|ipad|ipod/.test(userAgent);
        const isAndroid = /android/.test(userAgent);
        const isChrome = /chrome/.test(userAgent) && !/edge|edg/.test(userAgent);
        const isSafari = /safari/.test(userAgent) && !/chrome/.test(userAgent);
        const isDesktop = !isIOS && !isAndroid;
        
        if(isDesktop) {
            return '<?= l('pwa_install.subheader.desktop') ?>'.replace('%s', '<i class="fas fa-plus-circle"></i>');
        } else if(isAndroid && isChrome) {
            return '<?= l('pwa_install.subheader.android_chrome') ?>'.replace(/%s/g, '<i class="fas fa-ellipsis-v"></i>');
        } else if(isIOS && isSafari) {
            return '<?= l('pwa_install.subheader.ios_safari') ?>';
        } else if(isIOS) {
            return '<?= l('pwa_install.subheader.ios') ?>'.replace(/%s/g, '<i class="fas fa-share"></i>');
        }
        
        return '';
    }
    
    // Show install bar with delay
    function showInstallBar() {
        instructionsElement.innerHTML = getInstallInstructions();
        
        setTimeout(() => {
            installBar.style.display = 'block';
        }, <?= (int) $display_delay * 1000 ?>);
    }
    
    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        if(installButton) {
            installButton.style.display = 'block';
            installButton.onclick = () => {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if(choiceResult.outcome === 'accepted') {
                        installBar.style.display = 'none';
                    }
                    deferredPrompt = null;
                });
            };
        }
        
        showInstallBar();
    });
    
    // Check if already installed
    if(window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        // Already installed, don't show
        return;
    }
    
    // Show instructions even if beforeinstallprompt doesn't fire (for iOS, etc.)
    setTimeout(() => {
        if(!deferredPrompt && installBar.style.display === 'none') {
            showInstallBar();
        }
    }, <?= (int) ($display_delay + 2) * 1000 ?>);
})();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<style>
.pwa-install-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 1rem;
    z-index: 1050;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .pwa-install-bar {
        padding: 0.75rem;
    }
    
    .pwa-install-bar .container {
        padding: 0;
    }
}
</style>

