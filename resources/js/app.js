import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const escapeHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    // 1. Preloader on internal link navigation
    const preloader = document.getElementById('kotb-preloader');
    document.querySelectorAll('a[data-nav]').forEach((link) => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) return;
            if (preloader) {
                preloader.classList.remove('hidden');
                preloader.classList.add('flex');
            }
        });
    });

    // 2. Scroll-triggered reveal observer
    const revealEls = document.querySelectorAll('.reveal-on-scroll');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const delay = entry.target.getAttribute('data-delay') || '0';
                        entry.target.style.transitionDelay = `${delay}ms`;
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.08, rootMargin: '0px 0px -24px 0px' }
        );
        revealEls.forEach((el) => observer.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add('is-visible'));
    }

    // 3. Sticky Header shrink on scroll
    const headerBar = document.getElementById('kotb-header-bar');
    const headerLogo = document.getElementById('kotb-header-logo');
    if (headerBar && headerLogo) {
        window.addEventListener(
            'scroll',
            () => {
                if (window.scrollY > 30) {
                    headerBar.classList.remove('h-[76px]');
                    headerBar.classList.add('h-[62px]');
                    headerLogo.classList.add('scale-90');
                } else {
                    headerBar.classList.add('h-[76px]');
                    headerBar.classList.remove('h-[62px]');
                    headerLogo.classList.remove('scale-90');
                }
            },
            { passive: true }
        );
    }

    // 4. Menu Drawer (Two-level navigation)
    const menuDrawer = document.getElementById('kotb-menu-drawer');
    const menuPanel = document.getElementById('kotb-menu-panel');
    const openMenuBtn = document.getElementById('open-menu-btn');
    const closeMenuBtn = document.getElementById('close-menu-btn');
    const menuBackdrop = document.getElementById('menu-backdrop');
    const menuViews = {
        root: document.getElementById('menu-view-root'),
        story: document.getElementById('menu-view-story'),
        shop: document.getElementById('menu-view-shop'),
    };
    const menuBackBtn = document.getElementById('menu-back-btn');
    const menuHeaderLabel = document.getElementById('menu-header-label');

    const switchMenuView = (view) => {
        Object.entries(menuViews).forEach(([k, el]) => {
            if (!el) return;
            el.classList.toggle('hidden', k !== view);
        });
        if (menuPanel) {
            menuPanel.style.backgroundColor = view === 'root' ? '#ffffff' : '#ededed';
        }
        if (menuBackBtn && menuHeaderLabel) {
            if (view === 'root') {
                menuBackBtn.classList.add('hidden');
                menuHeaderLabel.classList.remove('hidden');
            } else {
                menuBackBtn.classList.remove('hidden');
                menuBackBtn.querySelector('span').textContent = view === 'story' ? 'BLOCK STORY' : 'SHOP';
                menuHeaderLabel.classList.add('hidden');
            }
        }
    };

    const toggleMenu = (open) => {
        if (!menuDrawer) return;
        menuDrawer.classList.toggle('hidden', !open);
        menuDrawer.classList.toggle('flex', open);
        if (!open) switchMenuView('root');
    };

    openMenuBtn?.addEventListener('click', () => toggleMenu(true));
    closeMenuBtn?.addEventListener('click', () => toggleMenu(false));
    menuBackdrop?.addEventListener('click', () => toggleMenu(false));
    menuBackBtn?.addEventListener('click', () => switchMenuView('root'));
    document.querySelectorAll('[data-submenu]').forEach((btn) => {
        btn.addEventListener('click', () => switchMenuView(btn.getAttribute('data-submenu')));
    });

    // 5. Predictive Search Overlay
    const searchOverlay = document.getElementById('kotb-search-overlay');
    const searchInput = document.getElementById('kotb-search-input');
    const searchResults = document.getElementById('kotb-search-results');
    const searchCountLabel = document.getElementById('kotb-search-count');

    const runSearch = async (query) => {
        if (!searchResults) return;
        try {
            const res = await fetch(`/search?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            const items = data.results || [];
            if (searchCountLabel) {
                searchCountLabel.textContent = query.trim()
                    ? `RESULTS (${items.length})`
                    : 'POPULAR SEARCHES';
            }
            if (items.length === 0) {
                searchResults.innerHTML = `<p class="text-sm text-[#666] py-3">No garments found for "${escapeHtml(query)}".</p>`;
                return;
            }
            searchResults.innerHTML = items
                .map(
                    (p) => `
                <a href="${p.url}" data-nav class="border border-black/10 p-2.5 flex flex-col gap-2 bg-[#fafafa] hover:border-black transition-colors">
                    <div class="aspect-[4/5] w-full">${p.svg}</div>
                    <div class="font-display text-[11px] leading-tight">${escapeHtml(p.title)}</div>
                    <div class="font-mono-meta text-[11px]">₱${escapeHtml(p.price)} PHP</div>
                </a>
            `
                )
                .join('');
        } catch (err) {
            console.error(err);
        }
    };

    document.getElementById('open-search-btn')?.addEventListener('click', () => {
        searchOverlay?.classList.remove('hidden');
        searchInput?.focus();
        runSearch('');
    });
    document.getElementById('close-search-btn')?.addEventListener('click', () => {
        searchOverlay?.classList.add('hidden');
    });
    document.getElementById('search-backdrop')?.addEventListener('click', () => {
        searchOverlay?.classList.add('hidden');
    });

    let searchTimer;
    searchInput?.addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => runSearch(e.target.value), 180);
    });

    // 6. Cart Drawer (AJAX Add / Update / Remove / Checkout)
    const cartDrawer = document.getElementById('kotb-cart-drawer');
    const cartItemsEl = document.getElementById('kotb-cart-items');
    const cartEmptyEl = document.getElementById('kotb-cart-empty');
    const cartFooterEl = document.getElementById('kotb-cart-footer');
    const cartConfirmedEl = document.getElementById('kotb-cart-confirmed');
    const cartSubtotalEl = document.getElementById('kotb-cart-subtotal');
    const cartBadgeEl = document.getElementById('kotb-cart-badge');
    const shipBarEl = document.getElementById('kotb-shipping-bar');
    const shipTextEl = document.getElementById('kotb-shipping-text');
    const shipFillEl = document.getElementById('kotb-shipping-fill');

    const toggleCart = (open) => {
        if (!cartDrawer) return;
        cartDrawer.classList.toggle('hidden', !open);
        cartDrawer.classList.toggle('flex', open);
    };

    const renderCart = (data) => {
        const items = data.items || [];
        if (cartBadgeEl) {
            cartBadgeEl.textContent = data.count;
            cartBadgeEl.classList.toggle('hidden', data.count <= 0);
            cartBadgeEl.classList.toggle('flex', data.count > 0);
            cartBadgeEl.classList.remove('anim-cart-bounce');
            void cartBadgeEl.offsetWidth;
            cartBadgeEl.classList.add('anim-cart-bounce');
        }
        if (cartConfirmedEl) cartConfirmedEl.classList.add('hidden');

        if (items.length === 0) {
            cartEmptyEl?.classList.remove('hidden');
            cartEmptyEl?.classList.add('flex');
            cartItemsEl?.classList.add('hidden');
            cartFooterEl?.classList.add('hidden');
            shipBarEl?.classList.add('hidden');
            return;
        }

        const subNum = parseFloat(String(data.subtotal).replace(/,/g, '')) || 0;
        const pct = Math.min(100, Math.round((subNum / 3500) * 100));
        const rem = Math.max(0, 3500 - subNum);
        shipBarEl?.classList.remove('hidden');
        if (shipFillEl) shipFillEl.style.width = `${pct}%`;
        if (shipTextEl) {
            shipTextEl.textContent = rem <= 0
                ? '✓ FREE EXPRESS SHIPPING UNLOCKED'
                : `ADD ₱${rem.toFixed(2)} MORE FOR FREE SHIPPING`;
        }

        cartEmptyEl?.classList.add('hidden');
        cartEmptyEl?.classList.remove('flex');
        cartItemsEl?.classList.remove('hidden');
        cartFooterEl?.classList.remove('hidden');
        if (cartSubtotalEl) cartSubtotalEl.textContent = `₱${data.subtotal} PHP`;

        if (cartItemsEl) {
            cartItemsEl.innerHTML = items
                .map(
                    (item) => `
                <div class="grid grid-cols-[76px_1fr] gap-3.5 pb-4 border-b border-black/10">
                    <div class="aspect-[4/5] bg-[#f7f7f7] overflow-hidden">
                        ${item.thumb_html || ''}
                    </div>
                    <div class="flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-2">
                                <a href="/product/${item.slug}" class="font-display text-xs leading-snug text-left">${item.title}</a>
                                <button type="button" data-cart-remove="${item.key}" class="text-[11px] text-[#666] hover:text-black">Remove</button>
                            </div>
                            <div class="font-mono-meta text-[11px] text-[#666] mt-1">SIZE: ${item.size}</div>
                        </div>
                        <div class="flex items-center justify-between mt-2.5">
                            <div class="inline-flex items-center border border-black rounded-[8px] h-8">
                                <button type="button" data-cart-delta="-1" data-cart-key="${item.key}" class="w-7 h-full flex items-center justify-center text-xs">-</button>
                                <span class="font-mono-meta w-6 text-center text-xs">${item.quantity}</span>
                                <button type="button" data-cart-delta="1" data-cart-key="${item.key}" class="w-7 h-full flex items-center justify-center text-xs">+</button>
                            </div>
                            <div class="font-mono-meta text-xs">₱${(item.price * item.quantity).toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            `
                )
                .join('');
        }
    };

    document.getElementById('open-cart-btn')?.addEventListener('click', () => toggleCart(true));
    document.getElementById('close-cart-btn')?.addEventListener('click', () => toggleCart(false));
    document.getElementById('cart-backdrop')?.addEventListener('click', () => toggleCart(false));
    document.getElementById('continue-shopping-btn')?.addEventListener('click', () => toggleCart(false));

    cartItemsEl?.addEventListener('click', async (e) => {
        const removeBtn = e.target.closest('[data-cart-remove]');
        const deltaBtn = e.target.closest('[data-cart-delta]');
        if (removeBtn) {
            const res = await fetch('/cart/remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ key: removeBtn.getAttribute('data-cart-remove') }),
            });
            renderCart(await res.json());
        } else if (deltaBtn) {
            const res = await fetch('/cart/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    key: deltaBtn.getAttribute('data-cart-key'),
                    delta: parseInt(deltaBtn.getAttribute('data-cart-delta'), 10),
                }),
            });
            renderCart(await res.json());
        }
    });

    document.getElementById('kotb-checkout-btn')?.addEventListener('click', async () => {
        const res = await fetch('/cart/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ payment_method: 'card' }),
        });
        const data = await res.json();
        renderCart({ items: [], count: 0, subtotal: '0.00' });
        cartEmptyEl?.classList.add('hidden');
        cartEmptyEl?.classList.remove('flex');
        if (cartConfirmedEl) {
            cartConfirmedEl.classList.remove('hidden');
            cartConfirmedEl.classList.add('flex');
            const orderNumEl = document.getElementById('confirmed-order-number');
            if (orderNumEl) orderNumEl.textContent = data.order_number;
        }
    });

    // Global Add to Cart listener (used on PDP with stock validation)
    const addToCartForm = document.getElementById('pdp-add-to-cart-form');
    addToCartForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errBox = document.getElementById('pdp-stock-error');
        errBox?.classList.add('hidden');
        const formData = new FormData(addToCartForm);
        const res = await fetch('/cart/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                slug: formData.get('slug'),
                size: formData.get('size'),
                quantity: parseInt(formData.get('quantity') || '1', 10),
            }),
        });
        const data = await res.json();
        if (!res.ok) {
            if (errBox) {
                errBox.textContent = data.error || 'Unable to add item.';
                errBox.classList.remove('hidden');
            }
            return;
        }
        renderCart(data);
        toggleCart(true);
    });

    // 7. Horizontal Slider Arrows on Shop Page
    document.querySelectorAll('[data-slider-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-slider-target');
            const dir = parseInt(btn.getAttribute('data-slider-btn'), 10);
            const track = document.getElementById(targetId);
            track?.scrollBy({ left: dir * 340, behavior: 'smooth' });
        });
    });

    // 8. Account Modal (Gmail 6-Digit OTP, Password Login, Register) & Escape Key
    const accountModal = document.getElementById('kotb-account-modal');
    const otpPanel = document.getElementById('kotb-otp-panel');
    const otpSendForm = document.getElementById('kotb-otp-send-form');
    const otpVerifyForm = document.getElementById('kotb-otp-verify-form');
    const loginForm = document.getElementById('kotb-login-form');
    const registerForm = document.getElementById('kotb-register-form');
    const authErr = document.getElementById('auth-error-msg');
    const authInfo = document.getElementById('auth-info-msg');

    document.getElementById('open-account-btn')?.addEventListener('click', () => {
        accountModal?.classList.remove('hidden');
        accountModal?.classList.add('flex');
    });
    document.getElementById('drawer-login-btn')?.addEventListener('click', () => {
        toggleMenu(false);
        accountModal?.classList.remove('hidden');
        accountModal?.classList.add('flex');
    });
    document.getElementById('close-account-btn')?.addEventListener('click', () => {
        accountModal?.classList.add('hidden');
        accountModal?.classList.remove('flex');
    });

    document.querySelectorAll('.auth-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            const mode = tab.getAttribute('data-auth-tab');
            authErr?.classList.add('hidden');
            authInfo?.classList.add('hidden');
            document.querySelectorAll('.auth-tab').forEach((t) => {
                t.classList.toggle('bg-black', t === tab);
                t.classList.toggle('text-white', t === tab);
                t.classList.toggle('bg-white', t !== tab);
                t.classList.toggle('text-black', t !== tab);
            });
            otpPanel?.classList.toggle('hidden', mode !== 'otp');
            otpPanel?.classList.toggle('flex', mode === 'otp');
            loginForm?.classList.toggle('hidden', mode !== 'login');
            loginForm?.classList.toggle('flex', mode === 'login');
            registerForm?.classList.toggle('hidden', mode !== 'register');
            registerForm?.classList.toggle('flex', mode === 'register');
        });
    });

    otpSendForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        authErr?.classList.add('hidden');
        authInfo?.classList.add('hidden');
        const email = document.getElementById('otp-email-input')?.value;
        const res = await fetch('/auth/otp/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ email }),
        });
        const data = await res.json();
        if (!res.ok) {
            if (authErr) {
                authErr.textContent = data.message || 'Unable to send code.';
                authErr.classList.remove('hidden');
            }
            return;
        }
        otpVerifyForm?.classList.remove('hidden');
        otpVerifyForm?.classList.add('flex');
        if (authInfo) {
            authInfo.textContent = data.dev_code
                ? `Code sent! (Local Dev Preview Code: ${data.dev_code})`
                : `✓ 6-digit code sent to ${email}. Check your inbox.`;
            authInfo.classList.remove('hidden');
        }
    });

    otpVerifyForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        authErr?.classList.add('hidden');
        const email = document.getElementById('otp-email-input')?.value;
        const code = document.getElementById('otp-code-input')?.value;
        const res = await fetch('/auth/otp/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ email, code }),
        });
        const data = await res.json();
        if (!res.ok) {
            if (authErr) {
                authErr.textContent = data.error || 'Invalid code.';
                authErr.classList.remove('hidden');
            }
            return;
        }
        window.location.href = data.redirect || '/account';
    });

    const handleAuthSubmit = async (form, url) => {
        authErr?.classList.add('hidden');
        const fd = new FormData(form);
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(Object.fromEntries(fd.entries())),
        });
        const data = await res.json();
        if (!res.ok) {
            if (authErr) {
                authErr.textContent = data.error || data.message || 'Authentication failed.';
                authErr.classList.remove('hidden');
            }
            return;
        }
        window.location.href = data.redirect || '/account';
    };

    loginForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        handleAuthSubmit(loginForm, '/login');
    });
    registerForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        handleAuthSubmit(registerForm, '/register');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            toggleMenu(false);
            toggleCart(false);
            searchOverlay?.classList.add('hidden');
            accountModal?.classList.add('hidden');
            accountModal?.classList.remove('flex');
        }
    });

    const chatPanel = document.getElementById('kotb-chat-panel');
    const chatLog = document.getElementById('kotb-chat-log');
    let chatPollTimer = null;

    const renderChatMessages = (messages) => {
        if (!chatLog) return;
        const welcome = `<div class="self-start bg-[#ededed] text-black px-3 py-2 text-xs max-w-[85%]">Welcome to KIDS OWN THE BLOCK. Tap a topic below or type your question.</div>`;
        const rows = (messages || []).map((m) => `
            <div class="${m.sender === 'visitor' ? 'self-end bg-black text-white' : 'self-start bg-[#ededed] text-black border border-black/15'} px-3 py-2 text-xs max-w-[85%]">
                ${m.sender === 'admin' ? '<div class="font-mono-meta text-[9px] text-[#666] mb-0.5">KOTB ASSISTANT</div>' : ''}
                <div>${escapeHtml(m.message)}</div>
            </div>
        `).join('');
        chatLog.innerHTML = welcome + rows;
        chatLog.scrollTop = chatLog.scrollHeight;
    };

    const sendChatMessage = async (val) => {
        if (!val) return;
        const res = await fetch('/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ message: val }),
        });
        if (res.ok) {
            const data = await res.json();
            renderChatMessages(data.messages);
        }
    };

    document.querySelectorAll('[data-chat-chip]').forEach((chip) => {
        chip.addEventListener('click', () => sendChatMessage(chip.getAttribute('data-chat-chip')));
    });

    const pollChat = async () => {
        try {
            const res = await fetch('/chat/poll');
            if (res.ok) {
                const data = await res.json();
                renderChatMessages(data.messages);
            }
        } catch (e) {}
    };

    document.getElementById('kotb-chat-toggle')?.addEventListener('click', () => {
        const isHidden = chatPanel?.classList.toggle('hidden');
        if (!isHidden) {
            pollChat();
            chatPollTimer = setInterval(pollChat, 4000);
        } else if (chatPollTimer) {
            clearInterval(chatPollTimer);
            chatPollTimer = null;
        }
    });
    document.getElementById('kotb-chat-close')?.addEventListener('click', () => {
        chatPanel?.classList.add('hidden');
        if (chatPollTimer) {
            clearInterval(chatPollTimer);
            chatPollTimer = null;
        }
    });
    document.getElementById('kotb-chat-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('kotb-chat-input');
        const val = input?.value.trim();
        if (!val) return;
        input.value = '';
        await sendChatMessage(val);
    });

    // 9. Footer Newsletter AJAX
    const newsletterForm = document.getElementById('kotb-newsletter-form');
    newsletterForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = newsletterForm.querySelector('input[name="email"]')?.value;
        if (!email) return;
        await fetch('/newsletter', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ email }),
        });
        document.getElementById('kotb-newsletter-status')?.classList.remove('hidden');
        newsletterForm.reset();
    });

    // 10. Scroll-to-Top Floating Pill
    const scrollTopBtn = document.getElementById('kotb-scroll-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', () => {
            scrollTopBtn.classList.toggle('hidden', window.scrollY < 420);
        }, { passive: true });
        scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }
});
