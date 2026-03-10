/**
 * Web Push Notification Handler for Missing People Reporter
 */

document.addEventListener('DOMContentLoaded', function () {
    const pushButton = document.getElementById('mpr-push-subscribe');
    const globalPushButton = document.getElementById('mpr-push-subscribe-global');

    if (!pushButton && !globalPushButton) return;

    const caseId = pushButton ? pushButton.dataset.caseId : 0;
    const adminUrl = mpr_push_vars.ajax_url;

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register(mpr_push_vars.sw_url)
            .then(function (swReg) {
                if (pushButton) {
                    checkSubscription(swReg, pushButton);
                    pushButton.addEventListener('click', () => handleSubClick(swReg, pushButton, 'single', ''));
                }
                if (globalPushButton) {
                    globalPushButton.addEventListener('click', () => {
                        const subTypeSelect = document.getElementById('mpr-global-sub-type');
                        const selectedOption = subTypeSelect.options[subTypeSelect.selectedIndex];
                        handleSubClick(swReg, globalPushButton, selectedOption.value, selectedOption.dataset.filter || '');
                    });
                }
            })
            .catch(err => console.error('Service Worker Error', err));
    }

    function checkSubscription(swReg, btn) {
        swReg.pushManager.getSubscription()
            .then(subscription => {
                if (subscription) {
                    btn.innerHTML = '<span class="dashicons dashicons-bell"></span> Subscribed';
                    btn.classList.add('is-subscribed');
                }
            });
    }

    function handleSubClick(swReg, btn, type, filter) {
        swReg.pushManager.getSubscription()
            .then(subscription => {
                if (subscription) {
                    unsubscribeUser(subscription, btn);
                } else {
                    subscribeUser(swReg, btn, type, filter);
                }
            });
    }

    function subscribeUser(swReg, btn, type, filter) {
        const applicationServerKey = mpr_push_vars.vapid_public_key;
        swReg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        })
            .then(subscription => {
                saveSubscription(subscription, type, filter, btn);
            })
            .catch(err => console.error('Failed to subscribe:', err));
    }

    function unsubscribeUser(subscription, btn) {
        subscription.unsubscribe()
            .then(() => {
                btn.innerHTML = '<span class="dashicons dashicons-bell"></span> Subscribe to Alerts';
                btn.classList.remove('is-subscribed');
            })
            .catch(err => console.error('Unsubscribe error:', err));
    }

    function saveSubscription(subscription, type, filter, btn) {
        const data = new FormData();
        data.append('action', 'mpr_register_push_subscription');
        data.append('subscription', JSON.stringify(subscription));
        data.append('case_id', caseId);
        data.append('subscription_type', type);
        data.append('filter_value', filter);
        data.append('nonce', mpr_push_vars.nonce);

        fetch(adminUrl, {
            method: 'POST',
            body: data
        })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    btn.innerHTML = '<span class="dashicons dashicons-bell"></span> Subscribed';
                    btn.classList.add('is-subscribed');
                    alert(res.data.message || 'Subscribed successfully!');
                }
            });
    }
});
