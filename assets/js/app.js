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

        // Buat panel dropdown notifikasi
        const panel = document.createElement('div');
        panel.id = 'notifPanel';
        panel.className = 'notif-panel';
        panel.innerHTML = '<div class="notif-header"><span>Notifikasi</span><button id="notifMarkAll" title="Tandai semua sudah dibaca">Tandai dibaca</button></div><div class="notif-list" id="notifList"><div class="notif-loading">Memuat...</div></div>';
        bell.style.position = 'relative';
        bell.appendChild(panel);

        // Muat jumlah notifikasi belum dibaca
        const loadCount = () => {
            fetch(`${window.APP_URL}/api/notifications.php?action=count`)
                .then(r => r.json())
                .then(data => {
                    const countEl = document.getElementById('notifCount');
                    if (data.count > 0) {
                        countEl.textContent = data.count > 99 ? '99+' : data.count;
                        countEl.style.display = 'flex';
                    } else {
                        countEl.style.display = 'none';
                    }
                })
                .catch(() => {});
        };
        loadCount();

        // Render daftar notifikasi ke panel
        const loadList = () => {
            const list = document.getElementById('notifList');
            list.innerHTML = '<div class="notif-loading">Memuat...</div>';
            fetch(`${window.APP_URL}/api/notifications.php?action=list`)
                .then(r => r.json())
                .then(data => {
                    const notifs = data.notifications || [];
                    if (!notifs.length) {
                        list.innerHTML = '<div class="notif-empty">Tidak ada notifikasi</div>';
                        return;
                    }
                    list.innerHTML = notifs.map(n => `
                        <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" data-link="${n.link || ''}">
                            <div class="notif-icon notif-icon-${n.tipe || 'info'}">
                                ${App.notifIcon(n.tipe)}
                            </div>
                            <div class="notif-body">
                                <div class="notif-title">${App.escHtml(n.judul)}</div>
                                <div class="notif-msg">${App.escHtml(n.pesan)}</div>
                                <div class="notif-time">${App.timeAgo(n.created_at)}</div>
                            </div>
                        </div>
                    `).join('');

                    // Klik item notifikasi
                    list.querySelectorAll('.notif-item').forEach(el => {
                        el.addEventListener('click', () => {
                            const id = el.dataset.id;
                            const link = el.dataset.link;
                            fetch(`${window.APP_URL}/api/notifications.php?action=read&id=${id}`);
                            el.classList.remove('unread');
                            if (link && link !== 'null') window.location.href = link;
                        });
                    });
                })
                .catch(() => { list.innerHTML = '<div class="notif-empty">Gagal memuat notifikasi</div>'; });
        };

        // Toggle panel saat klik lonceng
        bell.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = panel.classList.toggle('open');
            if (isOpen) loadList();
        });

        // Tutup panel saat klik luar
        document.addEventListener('click', (e) => {
            if (!bell.contains(e.target)) panel.classList.remove('open');
        });

        // Tandai semua dibaca
        document.getElementById('notifMarkAll')?.addEventListener('click', (e) => {
            e.stopPropagation();
            fetch(`${window.APP_URL}/api/notifications.php?action=read`)
                .then(() => {
                    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
                    const countEl = document.getElementById('notifCount');
                    countEl.style.display = 'none';
                });
        });
    },

    notifIcon(tipe) {
        const icons = {
            laporan: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>',
            sesi: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
            sharing: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
            sistem: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
            info: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
        };
        return icons[tipe] || icons.info;
    },

    escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    },

    timeAgo(dateStr) {
        const now = new Date();
        const d = new Date(dateStr);
        const diff = Math.floor((now - d) / 1000);
        if (diff < 60) return 'Baru saja';
        if (diff < 3600) return Math.floor(diff/60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff/3600) + ' jam lalu';
        if (diff < 604800) return Math.floor(diff/86400) + ' hari lalu';
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
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
