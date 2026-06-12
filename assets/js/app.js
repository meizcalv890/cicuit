const App = {
    init() {
        this.initSidebar();
        this.initModals();
        this.initNotifications();
        this.initForms();
        this.initConfirmDelete();
    },

    initSidebar() {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (toggle && sidebar) {
            toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            });
        }
    },

    initModals() {
        document.querySelectorAll('[data-modal]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.dataset.modal;
                const modal = document.getElementById(modalId);
                if (modal) modal.classList.add('active');
            });
        });

        document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target === el) {
                    el.closest('.modal-overlay')?.classList.remove('active');
                }
            });
        });

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => e.stopPropagation());
        });
    },

    initNotifications() {
        const bell = document.getElementById('notificationBell');
        if (!bell) return;

        fetch(`${window.APP_URL}/api/notifications.php?action=count`)
            .then(r => r.json())
            .then(data => {
                if (data.count > 0) {
                    const countEl = document.getElementById('notifCount');
                    countEl.textContent = data.count > 99 ? '99+' : data.count;
                    countEl.style.display = 'flex';
                }
            })
            .catch(() => {});
    },

    initForms() {
        document.querySelectorAll('form[data-ajax]').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = form.querySelector('[type="submit"]');
                const originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Memproses...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        App.toast(data.message || 'Berhasil', 'success');
                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 800);
                        } else if (data.reload) {
                            setTimeout(() => window.location.reload(), 800);
                        }
                    } else {
                        App.toast(data.message || 'Terjadi kesalahan', 'error');
                    }
                } catch (err) {
                    App.toast('Koneksi gagal', 'error');
                } finally {
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            });
        });
    },

    initConfirmDelete() {
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', (e) => {
                if (!confirm(el.dataset.confirm || 'Yakin ingin menghapus?')) {
                    e.preventDefault();
                }
            });
        });
    },

    toast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    },

    async fetchAPI(url, options = {}) {
        const response = await fetch(url, options);
        return response.json();
    },

    formatDate(dateStr) {
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const d = new Date(dateStr);
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());
