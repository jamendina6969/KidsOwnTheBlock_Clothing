document.addEventListener('DOMContentLoaded', () => {
  const KOTB = window.KOTB || {};
  const escapeHtml = (str) => String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const formatMoney = (cents) => {
    const amount = (Number(cents || 0) / 100).toLocaleString('en-PH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    const cur = KOTB.currency || 'PHP';
    return cur === 'PHP' ? `₱${amount} PHP` : `${amount} ${cur}`;
  };

  // 1. Preloader on internal link navigation
  const preloader = document.getElementById('kotb-preloader');
  window.addEventListener('pageshow', () => {
    if (preloader) {
      preloader.classList.add('hidden');
      preloader.classList.remove('flex');
    }
  });
  document.querySelectorAll('a[data-nav]').forEach((link) => {
    link.addEventListener('click', () => {
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

  // 4. Two-Level Menu Drawer
  const menuDrawer = document.getElementById('kotb-menu-drawer');
  const menuPanel = document.getElementById('kotb-menu-panel');
  const menuViews = {
    root: document.getElementById('menu-view-root'),
    story: document.getElementById('menu-view-story'),
    shop: document.getElementById('menu-view-shop'),
  };
  const menuBackBtn = document.getElementById('menu-back-btn');
  const menuHeaderLabel = document.getElementById('menu-header-label');

  const switchMenuView = (view) => {
    Object.entries(menuViews).forEach(([k, el]) => {
      if (el) el.classList.toggle('hidden', k !== view);
    });
    if (menuPanel) {
      menuPanel.style.backgroundColor = view === 'root' ? '#ffffff' : '#ededed';
    }
    if (menuBackBtn && menuHeaderLabel) {
      if (view === 'root') {
        menuBackBtn.classList.add('hidden');
        menuBackBtn.classList.remove('inline-flex');
        menuHeaderLabel.classList.remove('hidden');
      } else {
        menuBackBtn.classList.remove('hidden');
        menuBackBtn.classList.add('inline-flex');
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

  document.getElementById('open-menu-btn')?.addEventListener('click', () => toggleMenu(true));
  document.getElementById('close-menu-btn')?.addEventListener('click', () => toggleMenu(false));
  document.getElementById('menu-backdrop')?.addEventListener('click', () => toggleMenu(false));
  menuBackBtn?.addEventListener('click', () => switchMenuView('root'));
  document.querySelectorAll('[data-submenu]').forEach((btn) => {
    btn.addEventListener('click', () => switchMenuView(btn.getAttribute('data-submenu')));
  });

  // 5. Predictive Search Overlay (Shopify Predictive Search API)
  const searchOverlay = document.getElementById('kotb-search-overlay');
  const searchInput = document.getElementById('kotb-search-input');
  const searchResults = document.getElementById('kotb-search-results');
  const searchCountLabel = document.getElementById('kotb-search-count');

  const runSearch = async (query) => {
    if (!searchResults) return;
    const q = query.trim();
    if (!q) {
      if (searchCountLabel) searchCountLabel.textContent = 'TYPE TO SEARCH THE CATALOG';
      searchResults.innerHTML = '';
      return;
    }
    try {
      const base = KOTB.routes?.predictiveSearch || '/search/suggest';
      const res = await fetch(`${base}.json?q=${encodeURIComponent(q)}&resources[type]=product&resources[limit]=4`);
      const data = await res.json();
      const items = data?.resources?.results?.products || [];
      if (searchCountLabel) searchCountLabel.textContent = `RESULTS (${items.length})`;
      if (items.length === 0) {
        searchResults.innerHTML = `<p class="text-sm text-[#666] py-3">No garments found for "${escapeHtml(q)}".</p>`;
        return;
      }
      searchResults.innerHTML = items
        .map(
          (p) => `
          <a href="${p.url}" data-nav class="border border-black/10 p-2.5 flex flex-col gap-2 bg-[#fafafa] hover:border-black transition-colors">
            <div class="aspect-[4/5] w-full bg-white overflow-hidden">
              ${p.image ? `<img src="${p.image}" alt="${escapeHtml(p.title)}" class="w-full h-full object-contain" />` : ''}
            </div>
            <div class="font-display text-[11px] leading-tight">${escapeHtml(p.title)}</div>
            <div class="font-mono-meta text-[11px]">${escapeHtml(p.price)}</div>
          </a>`
        )
        .join('');
    } catch (err) {
      console.error(err);
    }
  };

  document.getElementById('open-search-btn')?.addEventListener('click', () => {
    searchOverlay?.classList.remove('hidden');
    searchInput?.focus();
  });
  document.getElementById('close-search-btn')?.addEventListener('click', () => searchOverlay?.classList.add('hidden'));
  document.getElementById('search-backdrop')?.addEventListener('click', () => searchOverlay?.classList.add('hidden'));

  let searchTimer;
  searchInput?.addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => runSearch(e.target.value), 180);
  });

  // 6. Cart Drawer (Shopify AJAX Cart API)
  const cartDrawer = document.getElementById('kotb-cart-drawer');
  const cartItemsEl = document.getElementById('kotb-cart-items');
  const cartEmptyEl = document.getElementById('kotb-cart-empty');
  const cartFooterEl = document.getElementById('kotb-cart-footer');
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

  const renderCart = (cart) => {
    const items = cart.items || [];
    const count = cart.item_count || 0;
    if (cartBadgeEl) {
      cartBadgeEl.textContent = count;
      cartBadgeEl.classList.toggle('hidden', count <= 0);
      cartBadgeEl.classList.toggle('flex', count > 0);
      cartBadgeEl.classList.remove('anim-cart-bounce');
      void cartBadgeEl.offsetWidth;
      cartBadgeEl.classList.add('anim-cart-bounce');
    }

    if (items.length === 0) {
      cartEmptyEl?.classList.remove('hidden');
      cartEmptyEl?.classList.add('flex');
      cartItemsEl?.classList.add('hidden');
      cartFooterEl?.classList.add('hidden');
      shipBarEl?.classList.add('hidden');
      return;
    }

    const thresholdCents = (KOTB.freeShippingThreshold || 3500) * 100;
    const pct = Math.min(100, Math.round((cart.total_price / thresholdCents) * 100));
    const rem = Math.max(0, thresholdCents - cart.total_price);
    shipBarEl?.classList.remove('hidden');
    if (shipFillEl) shipFillEl.style.width = `${pct}%`;
    if (shipTextEl) {
      shipTextEl.textContent =
        rem <= 0
          ? '✓ FREE EXPRESS SHIPPING UNLOCKED'
          : `ADD ${formatMoney(rem)} MORE FOR FREE SHIPPING`;
    }

    cartEmptyEl?.classList.add('hidden');
    cartEmptyEl?.classList.remove('flex');
    cartItemsEl?.classList.remove('hidden');
    cartFooterEl?.classList.remove('hidden');
    if (cartSubtotalEl) cartSubtotalEl.textContent = formatMoney(cart.total_price);

    if (cartItemsEl) {
      cartItemsEl.innerHTML = items
        .map(
          (item) => `
          <div class="grid grid-cols-[76px_1fr] gap-3.5 pb-4 border-b border-black/10">
            <div class="aspect-[4/5] bg-[#f7f7f7] overflow-hidden">
              ${item.image ? `<img src="${item.image}" alt="${escapeHtml(item.product_title)}" class="w-full h-full object-contain" />` : ''}
            </div>
            <div class="flex flex-col justify-between">
              <div>
                <div class="flex justify-between items-start gap-2">
                  <a href="${item.url}" class="font-display text-xs leading-snug text-left">${escapeHtml(item.product_title)}</a>
                  <button type="button" data-cart-remove="${item.key}" class="text-[11px] text-[#666] hover:text-black cursor-pointer">Remove</button>
                </div>
                ${item.variant_title ? `<div class="font-mono-meta text-[11px] text-[#666] mt-1">SIZE: ${escapeHtml(item.variant_title)}</div>` : ''}
              </div>
              <div class="flex items-center justify-between mt-2.5">
                <div class="inline-flex items-center border border-black rounded-[8px] h-8">
                  <button type="button" data-cart-qty="${item.quantity - 1}" data-cart-key="${item.key}" class="w-7 h-full flex items-center justify-center text-xs cursor-pointer">-</button>
                  <span class="font-mono-meta w-6 text-center text-xs">${item.quantity}</span>
                  <button type="button" data-cart-qty="${item.quantity + 1}" data-cart-key="${item.key}" class="w-7 h-full flex items-center justify-center text-xs cursor-pointer">+</button>
                </div>
                <div class="font-mono-meta text-xs">${formatMoney(item.final_line_price)}</div>
              </div>
            </div>
          </div>`
        )
        .join('');
    }
  };

  const refreshCart = async () => {
    const res = await fetch('/cart.js');
    if (res.ok) renderCart(await res.json());
  };

  document.getElementById('open-cart-btn')?.addEventListener('click', () => {
    toggleCart(true);
    refreshCart();
  });
  document.getElementById('close-cart-btn')?.addEventListener('click', () => toggleCart(false));
  document.getElementById('cart-backdrop')?.addEventListener('click', () => toggleCart(false));
  document.getElementById('continue-shopping-btn')?.addEventListener('click', () => toggleCart(false));

  cartItemsEl?.addEventListener('click', async (e) => {
    const removeBtn = e.target.closest('[data-cart-remove]');
    const qtyBtn = e.target.closest('[data-cart-qty]');
    if (removeBtn || qtyBtn) {
      const key = removeBtn ? removeBtn.getAttribute('data-cart-remove') : qtyBtn.getAttribute('data-cart-key');
      const quantity = removeBtn ? 0 : Math.max(0, parseInt(qtyBtn.getAttribute('data-cart-qty'), 10));
      const res = await fetch('/cart/change.js', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: key, quantity }),
      });
      if (res.ok) renderCart(await res.json());
    }
  });

  // 7. Horizontal Slider Arrows & Collection Sort
  document.querySelectorAll('[data-slider-btn]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const track = document.getElementById(btn.getAttribute('data-slider-target'));
      const dir = parseInt(btn.getAttribute('data-slider-btn'), 10);
      track?.scrollBy({ left: dir * 340, behavior: 'smooth' });
    });
  });

  document.getElementById('kotb-sort-select')?.addEventListener('change', (e) => {
    const url = new URL(window.location.href);
    url.searchParams.set('sort_by', e.target.value);
    window.location.href = url.toString();
  });

  // 8. Drop 05 Live Countdown Bar
  const countdownEl = document.getElementById('kotb-drop-countdown');
  if (countdownEl) {
    const targetIso = countdownEl.getAttribute('data-target');
    const parsed = targetIso ? Date.parse(targetIso) : NaN;
    const targetMs = !Number.isNaN(parsed) && parsed > Date.now() ? parsed : Date.now() + 210000000;
    const tick = () => {
      const remaining = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
      const d = String(Math.floor(remaining / 86400)).padStart(2, '0');
      const h = String(Math.floor((remaining % 86400) / 3600)).padStart(2, '0');
      const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
      const s = String(remaining % 60).padStart(2, '0');
      countdownEl.textContent = `${d}D : ${h}H : ${m}M : ${s}S`;
    };
    tick();
    setInterval(tick, 1000);
  }

  // 9. Product Detail Page (PDP)
  const pdpForm = document.getElementById('pdp-add-to-cart-form');
  if (pdpForm) {
    const mainImg = document.getElementById('pdp-main-img');
    const thumbBtns = document.querySelectorAll('[data-pdp-thumb]');
    thumbBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        const val = btn.getAttribute('data-pdp-thumb') || '';
        const stageEl = document.getElementById(`pdp-stage-${val}`);
        if (stageEl) {
          document.querySelectorAll('.pdp-stage').forEach((s) => s.classList.add('hidden'));
          stageEl.classList.remove('hidden');
        } else if (mainImg && val) {
          mainImg.src = val;
        }
        thumbBtns.forEach((t) => {
          t.classList.remove('border-black');
          t.classList.add('border-transparent');
        });
        btn.classList.remove('border-transparent');
        btn.classList.add('border-black');
      });
    });

    const variantInput = document.getElementById('pdp-variant-id');
    const priceEl = document.getElementById('pdp-price');
    const variantBtns = document.querySelectorAll('[data-variant-btn]:not([disabled]), .size-pill:not([disabled])');
    variantBtns.forEach((pill) => {
      pill.addEventListener('click', () => {
        if (variantInput) variantInput.value = pill.getAttribute('data-variant-id');
        if (priceEl && pill.getAttribute('data-variant-price')) {
          priceEl.textContent = pill.getAttribute('data-variant-price');
        }
        variantBtns.forEach((p) => {
          p.classList.remove('bg-black', 'text-white', 'border-black');
          p.classList.add('bg-white', 'text-black', 'border-black/30');
        });
        pill.classList.remove('bg-white', 'text-black', 'border-black/30');
        pill.classList.add('bg-black', 'text-white', 'border-black');
      });
    });

    let qty = 1;
    const qtyInput = document.getElementById('pdp-qty-input');
    const qtyDisplay = document.getElementById('pdp-qty-display');
    const syncQty = (next) => {
      qty = Math.max(1, next);
      if (qtyInput) qtyInput.value = qty;
      if (qtyDisplay) qtyDisplay.textContent = qty;
    };
    document.getElementById('pdp-qty-minus')?.addEventListener('click', () => syncQty(qty - 1));
    document.getElementById('pdp-qty-plus')?.addEventListener('click', () => syncQty(qty + 1));
    document.querySelectorAll('[data-pdp-qty]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const delta = parseInt(btn.getAttribute('data-pdp-qty') || '0', 10);
        const cur = parseInt(qtyInput?.value || String(qty), 10) || 1;
        syncQty(cur + delta);
      });
    });

    const addCurrentVariant = async () => {
      const errBox = document.getElementById('pdp-stock-error');
      errBox?.classList.add('hidden');
      const liveQty = Math.max(1, parseInt(qtyInput?.value || String(qty), 10) || 1);
      const res = await fetch('/cart/add.js', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: parseInt(variantInput?.value || '0', 10),
          quantity: liveQty,
        }),
      });
      const data = await res.json();
      if (!res.ok) {
        if (errBox) {
          errBox.textContent = data.description || data.message || 'Unable to add item.';
          errBox.classList.remove('hidden');
        }
        return false;
      }
      await refreshCart();
      return true;
    };

    pdpForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (await addCurrentVariant()) toggleCart(true);
    });

    document.getElementById('pdp-buy-now-btn')?.addEventListener('click', async () => {
      if (await addCurrentVariant()) window.location.href = '/checkout';
    });

    // Zoom Loupe
    document.querySelectorAll('.pdp-zoom-container').forEach((box) => {
      box.addEventListener('mousemove', (e) => {
        const rect = box.getBoundingClientRect();
        box.style.setProperty('--zoom-x', `${((e.clientX - rect.left) / rect.width) * 100}%`);
        box.style.setProperty('--zoom-y', `${((e.clientY - rect.top) / rect.height) * 100}%`);
      });
    });

    // Fit Finder Modal
    const fitModal = document.getElementById('fit-finder-modal');
    document.getElementById('open-fit-finder')?.addEventListener('click', () => {
      fitModal?.classList.remove('hidden');
      fitModal?.classList.add('flex');
    });
    document.getElementById('close-fit-finder')?.addEventListener('click', () => {
      fitModal?.classList.add('hidden');
      fitModal?.classList.remove('flex');
    });
    document.getElementById('calc-fit-btn')?.addEventListener('click', () => {
      const h = parseInt(document.getElementById('fit-height')?.value || '175', 10);
      const w = parseInt(document.getElementById('fit-weight')?.value || '70', 10);
      let rec = 'L';
      if (h < 166 && w < 60) rec = 'S';
      else if (h < 174 && w < 68) rec = 'M';
      else if (h < 182 && w < 82) rec = 'L';
      else if (h < 190 && w < 95) rec = 'XL';
      else rec = 'XXL';
      const resEl = document.getElementById('fit-result');
      if (resEl) {
        resEl.textContent = `RECOMMENDED: SIZE ${rec} (Relaxed Boxy Fit)`;
        resEl.classList.remove('hidden');
      }
    });

    // Wishlist (localStorage)
    const wlBtn = document.getElementById('pdp-wishlist-btn');
    if (wlBtn) {
      const handle = wlBtn.getAttribute('data-product-handle');
      const getWl = () => JSON.parse(localStorage.getItem('kotb_wishlist') || '[]');
      const updateWlLabel = () => {
        wlBtn.textContent = getWl().includes(handle) ? '♥ SAVED IN WISHLIST' : '♡ SAVE TO WISHLIST';
      };
      updateWlLabel();
      wlBtn.addEventListener('click', () => {
        let list = getWl();
        list = list.includes(handle) ? list.filter((x) => x !== handle) : [...list, handle];
        localStorage.setItem('kotb_wishlist', JSON.stringify(list));
        updateWlLabel();
      });
    }

    // Recently Viewed (localStorage)
    const mainEl = document.querySelector('[data-pdp-handle]');
    if (mainEl) {
      try {
        const current = {
          handle: mainEl.getAttribute('data-pdp-handle'),
          title: mainEl.getAttribute('data-pdp-title'),
          price: mainEl.getAttribute('data-pdp-price'),
          url: mainEl.getAttribute('data-pdp-url'),
        };
        let list = JSON.parse(localStorage.getItem('kotb_recent') || '[]');
        list = [current, ...list.filter((x) => x.handle !== current.handle)].slice(0, 5);
        localStorage.setItem('kotb_recent', JSON.stringify(list));
        const others = list.filter((x) => x.handle !== current.handle).slice(0, 4);
        if (others.length > 0) {
          const sec = document.getElementById('recently-viewed-section');
          const grid = document.getElementById('recently-viewed-grid');
          sec?.classList.remove('hidden');
          if (grid) {
            grid.innerHTML = others
              .map(
                (item) => `
                <a href="${item.url}" data-nav class="border border-black/15 hover:border-black p-3.5 flex flex-col justify-between gap-2 bg-[#fafafa] transition-colors">
                  <div class="font-display text-xs leading-snug">${escapeHtml(item.title)}</div>
                  <div class="font-mono-meta text-[11px] text-[#666]">${escapeHtml(item.price)} →</div>
                </a>`
              )
              .join('');
          }
        }
      } catch (e) {}
    }
  }

  // 10. Lookbooks Interactive Viewer
  if (document.getElementById('lb-main-svg')) {
    const collections = [
      {
        title: 'BLOCK PARTY COLLECTION 2026',
        slides: [
          { look: 1, title: 'KOTB Block Heavyweight Tee + Midnight Utility Shorts', garments: '240 GSM Washed Onyx Tee / Ripstop Cargo', bg: '#6e0e0e', drape: '#3d0404', sil: '#0d0d0d', acc: '#ffffff' },
          { look: 2, title: 'Concrete Youth Zip Hoodie + Stone Wash Baggy Denim', garments: '460 GSM Charcoal Fleece / 14oz Skate Denim', bg: '#161b26', drape: '#090c12', sil: '#222428', acc: '#d9ff00' },
          { look: 3, title: 'Two-Block Cropped Baby Tee + Cobalt Coach Jacket', garments: 'Bone Rib Knit / Water-Resistant Taslan', bg: '#751010', drape: '#2b0505', sil: '#162742', acc: '#f4f2ed' },
          { look: 4, title: 'Night Shift Work Jacket + Archive 6-Panel Cap', garments: '14oz Bull Denim Canvas / Brushed Twill', bg: '#181818', drape: '#070707', sil: '#262626', acc: '#c9a96e' },
          { look: 5, title: 'Block Run Club Mesh Jersey + Subterranean Track Pants', garments: '165 GSM Micro-Mesh / Crinkle Nylon', bg: '#111927', drape: '#05080f', sil: '#0e0e0e', acc: '#ffffff' },
        ],
      },
      {
        title: 'CONCRETE YOUTH ARCHIVE 2025',
        slides: [
          { look: 1, title: 'Heritage 001 Box Hoodie + Utility Shorts', garments: '480 GSM Heather Stone / 310 GSM Ripstop', bg: '#2c2c2c', drape: '#111111', sil: '#cfccc4', acc: '#111111' },
          { look: 2, title: 'Crimson Curtain Graphic Tee + Baggy Denim', garments: '230 GSM Off-White Jersey / 14oz Denim', bg: '#5a0c0c', drape: '#240303', sil: '#eceae4', acc: '#a81818' },
          { look: 3, title: 'Full Monochrome Block Uniform', garments: 'Onyx Heavyweight Tee / Archive 6-Panel', bg: '#0a0a0a', drape: '#000000', sil: '#1a1a1a', acc: '#ffffff' },
        ],
      },
    ];
    let colIdx = 0;
    let slideIdx = 0;

    const renderThumbs = () => {
      const thumbs = document.getElementById('lb-thumbs');
      if (!thumbs) return;
      thumbs.innerHTML = collections[colIdx].slides
        .map(
          (s, idx) => `
          <button type="button" data-lb-idx="${idx}" class="lb-thumb aspect-[4/5] overflow-hidden border ${idx === slideIdx ? 'border-black p-0.5' : 'border-transparent'} cursor-pointer">
            <svg viewBox="0 0 400 520" class="w-full h-full block" style="background:${s.bg}">
              <rect width="400" height="520" fill="${s.bg}" />
              <circle cx="200" cy="112" r="26" fill="${s.sil}" />
              <path d="M148 164 Q200 144 252 164 L276 312 L242 318 L234 475 L204 475 L200 340 L196 475 L166 475 L158 318 L124 312 Z" fill="${s.sil}" />
            </svg>
          </button>`
        )
        .join('');
    };

    const renderSlide = () => {
      const col = collections[colIdx];
      const slide = col.slides[slideIdx] || col.slides[0];
      const mainSvg = document.getElementById('lb-main-svg');
      mainSvg.classList.remove('anim-shutter');
      void mainSvg.offsetWidth;
      mainSvg.classList.add('anim-shutter');
      document.getElementById('lb-bg').setAttribute('fill', slide.bg);
      ['lb-d1', 'lb-d2', 'lb-d3', 'lb-d4'].forEach((id) => document.getElementById(id).setAttribute('fill', slide.drape));
      document.getElementById('lb-head').setAttribute('fill', slide.sil);
      document.getElementById('lb-body').setAttribute('fill', slide.sil);
      document.getElementById('lb-box').setAttribute('stroke', slide.acc);
      document.getElementById('lb-text').setAttribute('fill', slide.acc);
      document.getElementById('lb-look-tag').textContent = `LOOK 0${slide.look}`;
      document.getElementById('lb-col-title').textContent = col.title;
      document.getElementById('lb-look-label').textContent = `Look ${slide.look}`;
      document.getElementById('lb-look-caption').textContent = `${slide.title} — ${slide.garments}`;
      document.getElementById('lb-counter').textContent = `${slideIdx + 1} of ${col.slides.length}`;
      renderThumbs();
    };

    renderThumbs();
    document.getElementById('lb-thumbs')?.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-lb-idx]');
      if (!btn) return;
      slideIdx = parseInt(btn.getAttribute('data-lb-idx'), 10);
      renderSlide();
    });
    document.getElementById('lb-prev')?.addEventListener('click', () => {
      const len = collections[colIdx].slides.length;
      slideIdx = (slideIdx - 1 + len) % len;
      renderSlide();
    });
    document.getElementById('lb-next')?.addEventListener('click', () => {
      const len = collections[colIdx].slides.length;
      slideIdx = (slideIdx + 1) % len;
      renderSlide();
    });
    document.getElementById('lb-archive-toggle')?.addEventListener('click', () => {
      const list = document.getElementById('lb-archive-list');
      list?.classList.toggle('hidden');
      list?.classList.toggle('flex');
    });
    document.querySelectorAll('.lb-col-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        colIdx = parseInt(btn.getAttribute('data-col-idx'), 10);
        slideIdx = 0;
        renderSlide();
      });
    });
  }

  // 11. Run Club Radar Map
  if (document.getElementById('rc-city')) {
    const locations = [
      { city: 'DOWNTOWN CORE', district: 'DISTRICT 01', distance: '5.2 KM', pace: '5:30 / KM', nextDate: 'THU · NOV 26 · 7:30 PM', meetupSpot: 'KOTB Flagship Steps, 4th & Mercer' },
      { city: 'WESTSIDE PIER', district: 'HARBOR LOOP', distance: '7.0 KM', pace: '5:15 / KM', nextDate: 'SUN · NOV 29 · 7:00 AM', meetupSpot: 'Pier 14 Concrete Ramp' },
      { city: 'EAST BLOCK', district: 'INDUSTRIAL', distance: '6.0 KM', pace: '5:45 / KM', nextDate: 'TUE · DEC 01 · 8:00 PM', meetupSpot: 'Old Rail Yard Mural Wall' },
      { city: 'NORTH TRACK', district: 'STADIUM', distance: '400M INTERVALS', pace: 'OPEN PACE', nextDate: 'THU · DEC 03 · 7:30 PM', meetupSpot: 'North Oval Gate B' },
    ];
    document.querySelectorAll('[data-rc-idx]').forEach((el) => {
      el.addEventListener('click', () => {
        const idx = parseInt(el.getAttribute('data-rc-idx'), 10);
        const loc = locations[idx];
        document.getElementById('rc-district').textContent = `NEXT RUN · ${loc.district}`;
        document.getElementById('rc-city').textContent = loc.city;
        document.getElementById('rc-date').textContent = loc.nextDate;
        document.getElementById('rc-pace').textContent = `${loc.distance} · ${loc.pace}`;
        document.getElementById('rc-spot').textContent = `📍 ${loc.meetupSpot}`;
        const chInput = document.getElementById('rc-chapter-input');
        if (chInput) chInput.value = `RSVP Chapter: ${loc.city} (${loc.nextDate})`;
        document.querySelectorAll('.rc-pin').forEach((pin, i) => {
          pin.querySelector('.rc-ring')?.classList.toggle('hidden', i !== idx);
          pin.querySelector('.rc-dot')?.setAttribute('r', i === idx ? '2.2' : '1.5');
          pin.querySelector('.rc-dot')?.setAttribute('fill', i === idx ? '#d9ff00' : '#ffffff');
          pin.querySelector('.rc-label')?.setAttribute('fill', i === idx ? '#d9ff00' : '#aaaaaa');
        });
        document.querySelectorAll('.rc-pill').forEach((pill, i) => {
          pill.classList.toggle('bg-white', i === idx);
          pill.classList.toggle('text-black', i === idx);
          pill.classList.toggle('border-white', i === idx);
          pill.classList.toggle('text-white', i !== idx);
        });
      });
    });
  }

  // 12. Help FAQ Filter & Accordion
  if (document.getElementById('help-faq-grid')) {
    let activeTopic = 'ALL';
    const filterFaqs = () => {
      const q = (document.getElementById('help-search')?.value || '').toLowerCase();
      document.querySelectorAll('.help-faq-item').forEach((item) => {
        const topic = item.getAttribute('data-topic');
        const text = item.textContent.toLowerCase();
        item.classList.toggle('hidden', !((activeTopic === 'ALL' || topic === activeTopic) && (!q || text.includes(q))));
      });
    };
    document.getElementById('help-search')?.addEventListener('input', filterFaqs);
    document.querySelectorAll('.help-topic-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        activeTopic = btn.getAttribute('data-help-topic');
        document.querySelectorAll('.help-topic-btn').forEach((b) => {
          b.classList.toggle('bg-black', b === btn);
          b.classList.toggle('text-white', b === btn);
          b.classList.toggle('bg-white', b !== btn);
          b.classList.toggle('text-black', b !== btn);
        });
        filterFaqs();
      });
    });
    document.querySelectorAll('.help-faq-toggle').forEach((btn) => {
      btn.addEventListener('click', () => btn.nextElementSibling?.classList.toggle('hidden'));
    });
    document.querySelectorAll('.ticket-tab').forEach((tab) => {
      tab.addEventListener('click', () => {
        const t = tab.getAttribute('data-ticket-tab');
        const topicIn = document.getElementById('ticket-topic-input');
        if (topicIn) topicIn.value = t === 'order' ? 'Order Support' : 'Sizing & Restocks';
        document.getElementById('ticket-order-input')?.classList.toggle('hidden', t !== 'order');
        document.querySelectorAll('.ticket-tab').forEach((b) => {
          b.classList.toggle('bg-black', b === tab);
          b.classList.toggle('text-white', b === tab);
          b.classList.toggle('bg-white', b !== tab);
          b.classList.toggle('text-black', b !== tab);
        });
      });
    });
  }

  // 13. Floating KOTB Concierge Chat (Instant FAQ auto-responder)
  const chatPanel = document.getElementById('kotb-chat-panel');
  const chatLog = document.getElementById('kotb-chat-log');
  const chatReplies = {
    sizing: 'Our 240 GSM tees & 460 GSM hoodies have a boxy drop-shoulder street fit. Take your true size for a relaxed drape or use the Size Finder on any product page.',
    shipping: 'Domestic PH orders ship in 1–2 business days (2–4 days delivery). Free express shipping unlocks automatically at ₱3,500!',
    gcash: 'We accept GCash, Cards, and Cash on Delivery (COD) directly at Shopify Checkout.',
    cod: 'We accept GCash, Cards, and Cash on Delivery (COD) directly at Shopify Checkout.',
    promo: 'Subscribe to our footer newsletter for early Drop 05 access and exclusive Block Member promo codes.',
    track: 'You can track your order anytime at /pages/track-order or inside your Customer Account.',
  };

  const appendChat = (msg, from) => {
    if (!chatLog) return;
    const div = document.createElement('div');
    div.className =
      from === 'visitor'
        ? 'self-end bg-black text-white px-3 py-2 text-xs max-w-[85%]'
        : 'self-start bg-[#ededed] text-black border border-black/15 px-3 py-2 text-xs max-w-[85%]';
    div.innerHTML =
      from === 'visitor'
        ? escapeHtml(msg)
        : `<div class="font-mono-meta text-[9px] text-[#666] mb-0.5">KOTB ASSISTANT</div><div>${escapeHtml(msg)}</div>`;
    chatLog.appendChild(div);
    chatLog.scrollTop = chatLog.scrollHeight;
  };

  const handleChat = (val) => {
    if (!val) return;
    appendChat(val, 'visitor');
    const lower = val.toLowerCase();
    const matchKey = Object.keys(chatReplies).find((k) => lower.includes(k));
    const reply =
      chatReplies[matchKey] ||
      'Thanks for reaching out! For order-specific help, submit a quick ticket on our Help page (/pages/help) and our team will reply within 24h.';
    setTimeout(() => appendChat(reply, 'admin'), 260);
  };

  document.getElementById('kotb-chat-toggle')?.addEventListener('click', () => chatPanel?.classList.toggle('hidden'));
  document.getElementById('kotb-chat-close')?.addEventListener('click', () => chatPanel?.classList.add('hidden'));
  document.querySelectorAll('[data-chat-chip]').forEach((chip) => {
    chip.addEventListener('click', () => handleChat(chip.getAttribute('data-chat-chip')));
  });
  document.getElementById('kotb-chat-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const input = document.getElementById('kotb-chat-input');
    const val = input?.value.trim();
    if (!val) return;
    input.value = '';
    handleChat(val);
  });

  // 14. Escape key & Scroll-to-Top pill
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      toggleMenu(false);
      toggleCart(false);
      searchOverlay?.classList.add('hidden');
      document.getElementById('fit-finder-modal')?.classList.add('hidden');
    }
  });

  const scrollTopBtn = document.getElementById('kotb-scroll-top');
  if (scrollTopBtn) {
    window.addEventListener(
      'scroll',
      () => scrollTopBtn.classList.toggle('hidden', window.scrollY < 420),
      { passive: true }
    );
    scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }
});
