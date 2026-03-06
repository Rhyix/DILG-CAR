<script>
    (function () {
        if (window.__globalToastBridgeInit) return;
        window.__globalToastBridgeInit = true;

        const styleId = 'app-global-toast-style';
        const containerId = 'app-global-toast-container';
        const defaultDuration = 4200;

        function ensureStyles() {
            if (document.getElementById(styleId)) return;

            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                #${containerId} {
                    position: fixed;
                    top: 16px;
                    right: 16px;
                    z-index: 2147483647;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    pointer-events: none;
                    max-width: min(420px, calc(100vw - 24px));
                }
                .app-toast {
                    pointer-events: auto;
                    display: flex;
                    align-items: flex-start;
                    gap: 10px;
                    background: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-left-width: 4px;
                    border-radius: 10px;
                    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.15);
                    padding: 12px 14px;
                    transform: translateX(22px);
                    opacity: 0;
                    transition: opacity 0.18s ease, transform 0.18s ease;
                    font-family: 'Montserrat', system-ui, -apple-system, sans-serif;
                }
                .app-toast.show {
                    opacity: 1;
                    transform: translateX(0);
                }
                .app-toast.hide {
                    opacity: 0;
                    transform: translateX(22px);
                }
                .app-toast-icon {
                    margin-top: 1px;
                    font-size: 16px;
                    line-height: 1;
                    flex-shrink: 0;
                }
                .app-toast-message {
                    font-size: 13px;
                    line-height: 1.4;
                    color: #0f172a;
                    word-break: break-word;
                    flex: 1;
                }
                .app-toast-close {
                    border: 0;
                    background: transparent;
                    color: #64748b;
                    cursor: pointer;
                    font-size: 16px;
                    line-height: 1;
                    padding: 0 0 0 6px;
                    margin: 0;
                }
                .app-toast-close:hover {
                    color: #0f172a;
                }
                .app-toast-success { border-left-color: #10b981; }
                .app-toast-success .app-toast-icon { color: #059669; }
                .app-toast-error { border-left-color: #ef4444; }
                .app-toast-error .app-toast-icon { color: #dc2626; }
                .app-toast-warning { border-left-color: #f59e0b; }
                .app-toast-warning .app-toast-icon { color: #d97706; }
                .app-toast-info { border-left-color: #3b82f6; }
                .app-toast-info .app-toast-icon { color: #2563eb; }
                @media (max-width: 640px) {
                    #${containerId} {
                        left: 12px;
                        right: 12px;
                        top: 12px;
                        max-width: calc(100vw - 24px);
                    }
                }
            `;
            document.head.appendChild(style);
        }

        function ensureContainer() {
            let container = document.getElementById(containerId);
            if (container) return container;
            if (!document.body) return null;

            container = document.createElement('div');
            container.id = containerId;
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
            return container;
        }

        function inferType(message, preferred) {
            const normalizedPreferred = (preferred || '').toString().toLowerCase();
            if (['success', 'error', 'warning', 'info'].includes(normalizedPreferred)) {
                return normalizedPreferred;
            }

            const text = String(message || '').toLowerCase();
            if (/(error|failed|unable|cannot|invalid|denied|forbidden)/.test(text)) return 'error';
            if (/(warning|caution)/.test(text)) return 'warning';
            if (/(success|saved|sent|updated|completed|copied|started)/.test(text)) return 'success';
            return 'info';
        }

        function iconFor(type) {
            if (type === 'success') return '✓';
            if (type === 'error') return '!';
            if (type === 'warning') return '⚠';
            return 'i';
        }

        function showAppToast(message, type = 'info', duration = defaultDuration) {
            if (message === undefined || message === null) return;

            ensureStyles();
            const container = ensureContainer();
            if (!container) {
                document.addEventListener('DOMContentLoaded', function onceReady() {
                    document.removeEventListener('DOMContentLoaded', onceReady);
                    showAppToast(message, type, duration);
                });
                return;
            }

            const resolvedType = inferType(message, type);
            const toast = document.createElement('div');
            toast.className = `app-toast app-toast-${resolvedType}`;
            toast.setAttribute('role', resolvedType === 'error' ? 'alert' : 'status');

            const icon = document.createElement('span');
            icon.className = 'app-toast-icon';
            icon.textContent = iconFor(resolvedType);

            const text = document.createElement('div');
            text.className = 'app-toast-message';
            text.textContent = String(message);

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'app-toast-close';
            closeButton.setAttribute('aria-label', 'Dismiss notification');
            closeButton.textContent = '×';

            toast.appendChild(icon);
            toast.appendChild(text);
            toast.appendChild(closeButton);
            container.appendChild(toast);

            requestAnimationFrame(() => toast.classList.add('show'));

            let removed = false;
            const removeToast = () => {
                if (removed) return;
                removed = true;
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 180);
            };

            closeButton.addEventListener('click', removeToast);
            const timeout = Number.isFinite(Number(duration)) ? Number(duration) : defaultDuration;
            setTimeout(removeToast, Math.max(1000, timeout));
        }

        window.showAppToast = showAppToast;
        window.showToast = window.showToast || showAppToast;
        window.showToastNotification = window.showToastNotification || showAppToast;

        if (!window.__nativeAlert && typeof window.alert === 'function') {
            window.__nativeAlert = window.alert.bind(window);
        }

        window.alert = function (message) {
            const resolvedType = inferType(message, 'info');
            if (typeof window.showNotification === 'function') {
                try {
                    window.showNotification(String(message), resolvedType);
                    return;
                } catch (_) {
                    // Fallback to global toast if another notification system fails.
                }
            }
            showAppToast(message, resolvedType);
        };
    })();
</script>
