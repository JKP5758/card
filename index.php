<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Mini Blackjack 21 – Edukasi</title>
    <link rel="icon" href="https://jkp.my.id/assets/img/icons/favico.ico" type="image/x-icon" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    boxShadow: {
                        glow: "0 0 0 1px rgba(255,255,255,0.08), 0 8px 30px rgba(0,0,0,0.35)",
                    },
                },
            },
        };
    </script>
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"
        xintegrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        :root {
            --card-w: 90px;
            --card-h: 130px;
        }

        @media (min-width: 640px) {
            :root {
                --card-w: 110px;
                --card-h: 160px;
            }
        }

        .perspective {
            perspective: 1000px;
        }

        .card {
            width: var(--card-w);
            height: var(--card-h);
            transition: filter 0.3s ease-in-out;
        }

        @media (min-width: 640px) {
            .card {
                width: var(--card-w);
                height: var(--card-h);
            }
        }

        .card-inner {
            transform-style: preserve-3d;
            transition: transform .45s;
        }

        .card-face {
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .card-back-face {
            transform: rotateY(180deg);
        }

        .card-back-design {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.12);
        }

        /* glow & shake (glow = neutral white-blue) */
        .card.glow {
            /* Efek glow yang lebih halus */
            filter: drop-shadow(0 0 8px rgba(190, 230, 255, 0.6)) drop-shadow(0 0 16px rgba(160, 200, 255, 0.35)) drop-shadow(0 0 24px rgba(160, 200, 255, 0.12));
        }

        @keyframes shakeX {
            0% {
                transform: translateX(0)
            }

            25% {
                transform: translateX(-6px)
            }

            50% {
                transform: translateX(6px)
            }

            75% {
                transform: translateX(-4px)
            }

            100% {
                transform: translateX(0)
            }
        }

        .card.shake {
            animation: shakeX .5s ease;
        }

        /* status box to avoid CLS: reserve height and animate opacity only */
        .status-box {
            min-height: 1.25rem;
            display: block;
        }

        .status-text {
            display: inline-block;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity .18s ease, transform .18s ease;
        }

        .status-text.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Deck visual - stacked cards */
        .deck-spot {
            width: calc(var(--card-w));
            height: calc(var(--card-h));
            pointer-events: none;
        }

        .deck-stack {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .deck-stack .deck-card {
            position: absolute;
            inset: 0;
            border-radius: 10px;
            background: linear-gradient(180deg, #0b1220 0%, #12202b 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: grid;
            place-items: center;
            color: #cbd5e1;
            font-weight: 700;
            font-size: 12px;
            transform-origin: center;
            box-shadow: 0 6px 18px rgba(2, 6, 23, 0.6);
        }

        .deck-stack .deck-card.layer-1 {
            transform: translate(8px, -6px) rotate(-6deg);
            z-index: 1;
            opacity: 0.9;
        }

        .deck-stack .deck-card.layer-2 {
            transform: translate(4px, -3px) rotate(-3deg);
            z-index: 2;
            opacity: 0.95;
        }

        .deck-stack .deck-card.front {
            transform: translate(0px, 0px) rotate(0deg);
            z-index: 3;
            background: linear-gradient(180deg, #0f172a, #172033);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #e6f0ff;
            font-size: 13px;
            letter-spacing: .4px;
        }

        /* hands container tweaks - preserve original layout behavior */
        #botHand,
        #playerHand {
            min-height: 140px;
        }

        /* toast tweaks */
        #toast {
            min-width: 180px;
        }
    </style>
</head>

<body class="bg-slate-900 text-slate-100 min-h-screen font-sans">
    <div class="max-w-5xl mx-auto p-4 sm:p-6">
        <header class="flex items-center max-sm:flex-col justify-between gap-4">
            <h1 class="text-xl sm:text-2xl font-bold">Mini Blackjack 21 <span class="text-slate-400 text-base">(beta test)</span></h1>
            <div class="text-right max-sm:w-full">
                <div class="text-sm text-slate-400">Saldo</div>
                <div id="balance" class="text-2xl font-bold">Rp 100.000</div>
            </div>
        </header>

        <!-- Controls -->
        <section class="mt-4 grid sm:grid-cols-3 gap-3">
            <div class="sm:col-span-1 bg-slate-800/60 rounded-2xl p-4 shadow-glow">
                <label class="block text-sm text-slate-300 mb-1" for="bet">Taruhan</label>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-2 rounded-xl bg-slate-900/40 text-slate-300">Rp</span>
                    <input id="bet" type="text" inputmode="numeric" pattern="[0-9.]*"
                        class="w-full rounded-xl bg-slate-900 border border-slate-700 p-2 outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="10.000" value="10.000" />
                </div>

                <div class="mt-3 flex gap-2">
                    <button id="dealBtn" class="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition px-4 py-2 font-semibold">Deal</button>
                    <button id="resetBtn" class="rounded-xl bg-slate-700 hover:bg-slate-600 active:bg-slate-800 transition px-4 py-2">Reset</button>
                </div>
                <p class="text-xs text-slate-400 mt-2">Aturan payout: Menang = +taruhan, Kalah = -taruhan, Seri = 0.</p>
            </div>

            <div class="max-sm:hidden sm:col-span-2 bg-slate-800/60 rounded-2xl p-4 shadow-glow">
                <div class="text-sm text-slate-300">Aksi</div>
                <div class="mt-2 flex gap-3">
                    <button id="hitBtn" class="rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Ambil</button>
                    <button id="standBtn" class="rounded-xl bg-amber-600 hover:bg-amber-500 active:bg-amber-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Sudahi</button>
                    <button id="newRoundBtn" class="rounded-xl bg-slate-700 hover:bg-slate-600 active:bg-slate-800 transition px-4 py-2 hidden">Ronde Baru</button>
                </div>
                <div class="status-box mt-3 text-slate-300"><span id="status" class="status-text"></span></div>
            </div>
        </section>

        <!-- Table Area -->
        <section class="mt-6 bg-gradient-to-b from-slate-800/70 to-slate-900 rounded-3xl p-4 sm:p-6 shadow-glow relative overflow-hidden">
            <!-- Deck (styled as a small stacked deck) -->
            <div id="deckSpot" class="absolute right-4 top-4 perspective deck-spot" aria-hidden="true">
                <div class="deck-stack">
                    <div class="deck-card layer-1"></div>
                    <div class="deck-card layer-2"></div>
                    <div class="deck-card front">DECK</div>
                </div>
            </div>

            <div class="mb-8 max-sm:mt-32 sm:mr-32">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-lg font-semibold">Bot</h2>
                    <div class="text-slate-400">Total: <span id="botTotal">0</span></div>
                </div>
                <div id="botHand" class="min-h-[140px] sm:min-h-[170px] flex flex-wrap items-center gap-3 mt-2"></div>
            </div>

            <hr class="border-slate-700/60 my-4" />

            <div>
                <div class="flex items-baseline justify-between">
                    <h2 class="text-lg font-semibold">Kamu</h2>
                    <div class="text-slate-400">Total: <span id="playerTotal">0</span></div>
                </div>
                <div id="playerHand" class="min-h-[140px] sm:min-h-[170px] flex flex-wrap items-center gap-3 mt-2"></div>
            </div>
        </section>

        <!-- Controls Mobile -->
        <section class="mt-4 grid sm:grid-cols-3 gap-3">
            <div class="sm:hidden sm:col-span-2 bg-slate-800/60 rounded-2xl p-4 shadow-glow">
                <div class="text-sm text-slate-300">Aksi</div>
                <div class="mt-2 flex gap-3 flex-row-reverse">
                    <button id="standBtnMobile" class="rounded-xl bg-amber-600 hover:bg-amber-500 active:bg-amber-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Sudahi</button>
                    <button id="hitBtnMobile" class="rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Ambil</button>
                    <button id="newRoundBtnMobile" class="rounded-xl bg-slate-700 hover:bg-slate-600 active:bg-slate-800 transition px-4 py-2 hidden">Ronde Baru</button>
                </div>
                <div class="status-box mt-3 text-slate-300"><span id="statusMobile" class="status-text"></span></div>
            </div>
        </section>

        <footer class="mt-8 text-center text-xs text-slate-500">&copy; <span id="year"></span> JKP Project – Mini Blackjack (untuk edukasi, bukan ajakan bermain judi).</footer>
    </div>

    <!-- Template kartu (depan & belakang) -->
    <template id="cardTemplate">
        <div class="card perspective select-none">
            <div class="card-inner relative w-full h-full rounded-xl transition-transform duration-500 ease-out">
                <div class="card-face absolute inset-0 card-front grid place-items-center card-back-design">
                    <div class="w-[86%] h-[90%] rounded-lg border border-white/10"></div>
                </div>
                <div class="card-face card-back-face absolute inset-0 grid place-items-center overflow-hidden">
                    <img class="w-full h-full object-contain" alt="" />
                </div>
            </div>
        </div>
    </template>

    <!-- Toast notifikasi -->
    <div id="toast" class="hidden fixed left-1/2 transform -translate-x-1/2 top-4 px-4 py-2 rounded-full text-white shadow-lg">
        <span id="toastMsg" class="text-sm font-bold"></span>
    </div>

    <script>
        // ====== GAME STATE ======
        const ASSET_PATH = './assets/img';
        const SUITS = ['H', 'W', 'K', 'S'];
        const RANKS = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        // =======================================================================
        // ====== PANEL KONTROL BANDAR (PARAMETER MANIPULASI) ======
        // =======================================================================
        const MANIPULATION_CONFIG = {
            // Jika saldo pemain MELEBIHI ambang ini, manipulasi bisa diaktifkan.
            balance_threshold: 200000,
            // Probabilitas (0-1) bandar akan curang jika saldo pemain di atas ambang.
            // 0.9 = 90% kemungkinan bandar akan curang.
            cheat_chance_on_threshold: 0.9,
            // Saldo MAKSIMUM yang mustahil dicapai pemain. Bandar akan SELALU curang
            // untuk mencegah pemain mencapai atau melebihi saldo ini.
            unreachable_balance_cap: 300000,
        };
        // =======================================================================

        let deck = [];
        let playerHand = [];
        let botHand = [];
        let balance = 100000;
        let currentBet = 10000;
        let roundActive = false;
        let botHiddenCardEl = null;
        let isProcessingAction = false; // Flag baru untuk mencegah spam tombol

        const els = {
            balance: document.getElementById('balance'),
            bet: document.getElementById('bet'),
            dealBtn: document.getElementById('dealBtn'),
            resetBtn: document.getElementById('resetBtn'),
            hitBtn: document.getElementById('hitBtn'),
            standBtn: document.getElementById('standBtn'),
            newRoundBtn: document.getElementById('newRoundBtn'),
            status: document.getElementById('status'),
            deckSpot: document.getElementById('deckSpot'),
            botHand: document.getElementById('botHand'),
            playerHand: document.getElementById('playerHand'),
            botTotal: document.getElementById('botTotal'),
            playerTotal: document.getElementById('playerTotal'),
            year: document.getElementById('year'),
            cardTemplate: document.getElementById('cardTemplate'),
            // Tambahkan elemen-elemen mobile
            hitBtnMobile: document.getElementById('hitBtnMobile'),
            standBtnMobile: document.getElementById('standBtnMobile'),
            newRoundBtnMobile: document.getElementById('newRoundBtnMobile'),
            statusMobile: document.getElementById('statusMobile'),
        };

        els.year.textContent = new Date().getFullYear();

        // ===== helpers for syncing desktop + mobile controls/status =====
        function statusNodes() {
            return Array.from(document.querySelectorAll('#status, #statusMobile'));
        }

        function setStatus(text, autoHide = false) {
            statusNodes().forEach(n => {
                n.textContent = text;
                requestAnimationFrame(() => n.classList.add('visible'));
            });
            if (autoHide) {
                clearTimeout(setStatus._t);
                setStatus._t = setTimeout(() => statusNodes().forEach(n => n.classList.remove('visible')), 1800);
            }
        }

        function controlCollections() {
            return {
                hit: [els.hitBtn, els.hitBtnMobile].filter(b => b),
                stand: [els.standBtn, els.standBtnMobile].filter(b => b),
                deal: [els.dealBtn].filter(b => b),
                reset: [els.resetBtn].filter(b => b),
                newRound: [els.newRoundBtn, els.newRoundBtnMobile].filter(b => b)
            };
        }

        const fmtRupiah = n => `Rp ${n.toLocaleString('id-ID')}`;

        function updateBalanceUI() {
            els.balance.textContent = fmtRupiah(balance);
        }

        function numberWithDots(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function getBetNumeric() {
            const raw = (els.bet.dataset.raw || els.bet.value || '').toString().replace(/\D/g, '');
            const num = raw === '' ? 0 : parseInt(raw, 10);
            return Number.isNaN(num) ? 0 : num;
        }

        function renderBetFromRaw(rawDigits) {
            els.bet.dataset.raw = rawDigits;
            const formatted = rawDigits === '' ? '' : numberWithDots(rawDigits);
            els.bet.value = formatted;
        }

        function isBetValid() {
            const n = getBetNumeric();
            return n >= 1000 && n <= balance;
        }

        els.bet.addEventListener('input', (e) => {
            const digits = (e.target.value || '').replace(/\D/g, '');
            renderBetFromRaw(digits);
            Array.from(document.querySelectorAll('#dealBtn')).forEach(b => b.disabled = !isBetValid());
        });
        els.bet.addEventListener('blur', () => {
            let v = getBetNumeric();
            if (v === 0) v = 1000;
            if (v > balance) v = balance;
            renderBetFromRaw(String(v));
            Array.from(document.querySelectorAll('#dealBtn')).forEach(b => b.disabled = !isBetValid());
        });
        renderBetFromRaw(String(currentBet));

        function showToast(message, type = 'info', timeout = 2500) {
            const toast = document.getElementById('toast');
            const msg = document.getElementById('toastMsg');
            if (!toast || !msg) return;
            msg.textContent = message;
            toast.classList.remove('bg-green-600', 'bg-red-600', 'bg-slate-700', 'hidden');
            if (type === 'win') toast.classList.add('bg-green-600');
            else if (type === 'lose') toast.classList.add('bg-red-600');
            else toast.classList.add('bg-slate-700');
            gsap.fromTo(toast, {
                y: -60,
                autoAlpha: 0
            }, {
                y: 0,
                autoAlpha: 1,
                duration: 0.35,
                ease: 'power2.out'
            });
            if (toast._timeout) clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => hideToast(), timeout);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            if (!toast) return;
            gsap.to(toast, {
                y: -60,
                autoAlpha: 0,
                duration: 0.3,
                ease: 'power2.in',
                onComplete: () => toast.classList.add('hidden')
            });
        }
        document.getElementById('toast')?.addEventListener('click', hideToast);

        function buildDeck() {
            deck = [];
            for (const s of SUITS)
                for (const r of RANKS) deck.push({
                    suit: s,
                    rank: r,
                    img: `${ASSET_PATH}/${s}${r}.svg`
                });
        }

        function shuffle(arr) {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
            return arr;
        }

        function valueOfCard(rank) {
            if (rank === 'A') return 11;
            if (['K', 'Q', 'J'].includes(rank)) return 10;
            return parseInt(rank, 10);
        }

        function handTotal(hand) {
            let total = 0,
                aces = 0;
            for (const c of hand) {
                total += valueOfCard(c.rank);
                if (c.rank === 'A') aces++;
            }
            while (total > 21 && aces > 0) {
                total -= 10;
                aces--;
            }
            return total;
        }

        function resetTable() {
            els.botHand.innerHTML = '';
            els.playerHand.innerHTML = '';
            els.botTotal.textContent = '0';
            els.playerTotal.textContent = '0';
            setStatus('');
            botHiddenCardEl = null;
            playerHand = [];
            botHand = [];
        }

        function setControls({
            inRound
        }) {
            const cols = controlCollections();
            cols.hit.forEach(b => b.disabled = !inRound);
            cols.stand.forEach(b => b.disabled = !inRound);
            cols.deal.forEach(b => b.disabled = inRound || !isBetValid());
            cols.reset.forEach(b => b.disabled = false);
            cols.newRound.forEach(b => b.classList.toggle('hidden', inRound));
        }

        function createCardElement(card) {
            const tpl = els.cardTemplate.content.firstElementChild.cloneNode(true);
            const img = tpl.querySelector('img');
            img.src = card.img;
            img.alt = `${card.suit}${card.rank}`;
            return tpl;
        }

        function centerOf(el) {
            const r = el.getBoundingClientRect();
            return {
                x: r.left + r.width / 2 + window.scrollX,
                y: r.top + r.height / 2 + window.scrollY,
                w: r.width,
                h: r.height
            };
        }

        async function dealCardAnimated(targetContainer, card, {
            faceDown = false
        } = {}) {
            const cardEl = createCardElement(card);
            document.body.appendChild(cardEl);
            cardEl.style.position = 'absolute';
            cardEl.style.zIndex = 1000;
            cardEl.style.pointerEvents = 'none';

            const deckC = centerOf(els.deckSpot);
            const cardRect = cardEl.getBoundingClientRect();
            const startLeft = deckC.x - cardRect.width / 2;
            const startTop = deckC.y - cardRect.height / 2;
            cardEl.style.left = startLeft + 'px';
            cardEl.style.top = startTop + 'px';

            const targetDummy = document.createElement('div');
            targetDummy.className = 'card';
            targetContainer.appendChild(targetDummy);
            const targetC = centerOf(targetDummy);
            const endLeft = targetC.x - cardRect.width / 2;
            const endTop = targetC.y - cardRect.height / 2;

            await gsap.to(cardEl, {
                duration: .45,
                left: endLeft,
                top: endTop,
                rotation: (Math.random() * 10 - 5),
                ease: 'power2.out'
            });

            targetContainer.replaceChild(cardEl, targetDummy);
            cardEl.style.position = 'relative';
            cardEl.style.left = '0px';
            cardEl.style.top = '0px';
            cardEl.style.transform = '';
            cardEl.style.zIndex = 'auto';
            cardEl.style.pointerEvents = 'auto';

            if (!faceDown) {
                const inner = cardEl.querySelector('.card-inner');
                requestAnimationFrame(() => inner.classList.add('[transform:rotateY(180deg)]'));
                await new Promise(r => setTimeout(r, 480));
                cardEl.classList.add('glow');
                setTimeout(() => cardEl.classList.remove('glow'), 600);
            }
            return cardEl;
        }

        async function collectCardsBack() {
            const allCards = Array.from(document.querySelectorAll('#playerHand .card, #botHand .card'));
            if (allCards.length === 0) return;

            const firstCard = document.querySelector('#botHand .card') || document.querySelector('#playerHand .card');
            const firstRect = firstCard.getBoundingClientRect();
            const collectX = firstRect.left + firstRect.width / 2 + window.scrollX;
            const collectY = firstRect.top + firstRect.height / 2 + window.scrollY;

            allCards.forEach(c => {
                const inner = c.querySelector('.card-inner');
                if (inner) inner.classList.remove('[transform:rotateY(180deg)]');
            });
            await new Promise(r => setTimeout(r, 360));

            allCards.forEach((c, i) => {
                const r = c.getBoundingClientRect();
                document.body.appendChild(c);
                c.style.position = 'absolute';
                c.style.left = (r.left + window.scrollX) + 'px';
                c.style.top = (r.top + window.scrollY) + 'px';
                c.style.zIndex = 2000 + i;
            });

            const cardW = allCards[0].getBoundingClientRect().width;
            const cardH = allCards[0].getBoundingClientRect().height;
            await gsap.to(allCards, {
                duration: 0.45,
                left: (i) => (collectX - cardW / 2 + (i % 6) * 2),
                top: (i) => (collectY - cardH / 2 + Math.floor(i / 6) * 2),
                rotation: () => (Math.random() * 20 - 10),
                scale: 0.92,
                stagger: 0.05,
                ease: 'power2.out'
            });

            await new Promise(r => setTimeout(r, 180));

            const deckC = centerOf(els.deckSpot);
            await gsap.to(allCards, {
                duration: 0.5,
                left: deckC.x - cardW / 2,
                top: deckC.y - cardH / 2,
                rotation: 20,
                scale: 0.6,
                stagger: 0.03,
                ease: 'power2.in'
            });

            allCards.forEach(c => c.remove());
        }

        // FUNGSI PENGAMBILAN KARTU YANG DIMANIPULASI
        function drawFromDeck({
            neededValue = 0
        } = {}) {
            if (deck.length === 0) {
                buildDeck();
                shuffle(deck);
            }

            // Jika butuh kartu spesifik untuk curang
            if (neededValue > 0) {
                // Cari kartu dengan nilai pas di dalam dek
                const cardIndex = deck.findIndex(card => valueOfCard(card.rank) === neededValue);
                if (cardIndex > -1) {
                    // Jika ketemu, ambil kartu itu dari dek
                    const cheatCard = deck.splice(cardIndex, 1)[0];
                    return cheatCard;
                }
            }

            // Jika tidak curang atau kartu yang dicari tidak ada, ambil kartu paling atas
            return deck.pop();
        }

        async function startRound() {
            currentBet = Math.max(1000, getBetNumeric());
            if (currentBet > balance) {
                currentBet = balance;
                renderBetFromRaw(String(currentBet));
                setStatus('Taruhan otomatis disesuaikan ke saldo Anda.', true);
            }

            await collectCardsBack();
            resetTable();

            roundActive = true;
            isProcessingAction = true; // Nonaktifkan tombol saat animasi dimulai
            setControls({
                inRound: true
            });

            buildDeck();
            shuffle(deck);

            const p1 = drawFromDeck();
            playerHand.push(p1);
            await dealCardAnimated(els.playerHand, p1, {
                faceDown: false
            });
            els.playerTotal.textContent = handTotal(playerHand);

            // Kartu kedua pemain
            const p2 = drawFromDeck();
            playerHand.push(p2);
            await dealCardAnimated(els.playerHand, p2, {
                faceDown: false
            });
            els.playerTotal.textContent = handTotal(playerHand);

            // Kartu pertama bot (terbuka)
            const b1 = drawFromDeck();
            botHand.push(b1);
            await dealCardAnimated(els.botHand, b1, {
                faceDown: false
            });
            els.botTotal.textContent = handTotal(botHand);

            // Kartu kedua bot (tertutup)
            const b2 = drawFromDeck();
            botHand.push(b2);
            const el = await dealCardAnimated(els.botHand, b2, {
                faceDown: true
            });
            botHiddenCardEl = el.querySelector('.card-inner');

            isProcessingAction = false; // Aktifkan tombol saat giliran pemain dimulai
            setControls({
                inRound: true
            });

            setStatus('Giliran kamu: Ambil atau Sudahi.');
            if (handTotal(playerHand) === 21) {
                // Handle Blackjack
                await revealBotThenFinish();
            }
        }

        async function playerHit() {
            if (!roundActive || isProcessingAction) return;
            isProcessingAction = true; // Nonaktifkan tombol
            setStatus('Mengambil kartu...');

            const c = drawFromDeck();
            playerHand.push(c);
            await dealCardAnimated(els.playerHand, c, {
                faceDown: false
            });
            const total = handTotal(playerHand);
            els.playerTotal.textContent = total;

            if (total > 21) {
                setStatus('Kamu bust (>21). Kamu kalah.', true);
                await revealBotThenFinish();
            } else {
                setStatus('Giliran kamu: Ambil atau Sudahi.');
                isProcessingAction = false; // Aktifkan kembali tombol
            }
        }

        async function playerStand() {
            if (!roundActive || isProcessingAction) return;
            isProcessingAction = true; // Nonaktifkan tombol
            setStatus('Bot berpikir…');
            setControls({
                inRound: false
            }); // Nonaktifkan tombol pemain

            // Buka kartu bot yang tersembunyi
            if (botHiddenCardEl) {
                botHiddenCardEl.classList.add('[transform:rotateY(180deg)]');
                await new Promise(r => setTimeout(r, 400));
                try {
                    const botCardEl = botHiddenCardEl.closest('.card');
                    if (botCardEl) {
                        botCardEl.classList.add('glow');
                        setTimeout(() => botCardEl.classList.remove('glow'), 800);
                    }
                } catch (e) {}
                botHiddenCardEl = null;
                els.botTotal.textContent = handTotal(botHand);
            }

            // LOGIKA INTI MANIPULASI BANDAR
            const playerTotal = handTotal(playerHand);
            let botTotal = handTotal(botHand);

            // Kondisi 1: Cek apakah kemenangan akan melewati batas saldo (CAP)
            const willExceedCap = (balance + currentBet) >= MANIPULATION_CONFIG.unreachable_balance_cap;
            // Kondisi 2: Cek apakah saldo pemain sudah tinggi & bandar beruntung untuk curang
            const isBalanceHigh = balance >= MANIPULATION_CONFIG.balance_threshold;
            const isCheatingTriggered = Math.random() < MANIPULATION_CONFIG.cheat_chance_on_threshold;

            let shouldCheat = false;
            if (playerTotal <= 21 && willExceedCap) {
                shouldCheat = true; // PASTI CURANG jika akan melewati CAP
                setStatus('Bandar terlihat tersenyum licik...', true);
            } else if (playerTotal <= 21 && isBalanceHigh && isCheatingTriggered) {
                shouldCheat = true; // BERPELUANG CURANG jika saldo tinggi
                setStatus('Bandar sepertinya mendapat keberuntungan...', true);
            }

            // Jalankan giliran bot
            while (botTotal < 17 || (shouldCheat && botTotal <= playerTotal && botTotal <= 21)) {
                await new Promise(r => setTimeout(r, 600)); // Jeda agar terlihat "berpikir"

                let cardToDraw;
                if (shouldCheat && botTotal <= playerTotal) {
                    // Bandar curang: cari kartu yang sempurna untuk menang
                    const needed = (playerTotal - botTotal) + 1;
                    // Cari kartu antara nilai yg dibutuhkan s/d 11, agar tidak bust
                    let targetValue = 0;
                    for (let v = Math.min(needed, 11); v > 0; v--) {
                        if (botTotal + v <= 21) {
                            targetValue = v;
                            break;
                        }
                    }
                    cardToDraw = drawFromDeck({
                        neededValue: targetValue
                    });
                } else {
                    // Main normal
                    cardToDraw = drawFromDeck();
                }

                botHand.push(cardToDraw);
                await dealCardAnimated(els.botHand, cardToDraw, {
                    faceDown: false
                });
                botTotal = handTotal(botHand);
                els.botTotal.textContent = botTotal;

                // Jika sudah curang dan menang, hentikan
                if (shouldCheat && botTotal > playerTotal && botTotal <= 21) {
                    break;
                }
            }

            await revealBotThenFinish();
        }

        async function revealBotThenFinish() {
            roundActive = false;
            isProcessingAction = false;
            await new Promise(r => setTimeout(r, 800));

            const p = handTotal(playerHand);
            const b = handTotal(botHand);
            els.playerTotal.textContent = p;
            els.botTotal.textContent = b;

            let msg = '';
            let finalOutcome = 'tie';

            if (handTotal(playerHand) > 21) {
                msg = 'Kamu kalah (Bust).';
                finalOutcome = 'lose';
            } else if (handTotal(botHand) > 21) {
                msg = 'Bot bust. Kamu menang!';
                finalOutcome = 'win';
            } else if (handTotal(playerHand) > handTotal(botHand)) {
                msg = 'Skor lebih tinggi. Kamu menang!';
                finalOutcome = 'win';
            } else if (handTotal(playerHand) < handTotal(botHand)) {
                msg = 'Skor lebih rendah. Kamu kalah.';
                finalOutcome = 'lose';
            } else {
                msg = 'Seri.';
            }

            if (finalOutcome === 'win' && (balance + currentBet) >= MANIPULATION_CONFIG.unreachable_balance_cap) {
                msg = 'Hampir saja! Tapi bandar lebih beruntung. Kamu kalah.';
                finalOutcome = 'lose';
            }

            if (finalOutcome === 'win') {
                if (playerHand.length === 2 && handTotal(playerHand) === 21) {
                    balance += currentBet * 1.5;
                    msg = 'BLACKJACK! Kamu menang! ' + msg;
                } else {
                    balance += currentBet;
                }
            } else if (finalOutcome === 'lose') {
                balance -= currentBet;
            }

            updateBalanceUI();
            setStatus(msg + ` (P:${p} vs B:${b})`, true);

            const toastType = finalOutcome === 'win' ? 'win' : finalOutcome === 'lose' ? 'lose' : 'info';
            showToast(msg, toastType);

            setControls({
                inRound: false
            });
        }

        async function resetGame() {
            await collectCardsBack();
            balance = 100000;
            updateBalanceUI();
            renderBetFromRaw('10000');
            els.bet.dataset.raw = '10000';
            currentBet = 10000;
            roundActive = false;
            resetTable();
            setControls({
                inRound: false
            });
        }

        // INIT
        updateBalanceUI();
        setControls({
            inRound: false
        });

        // Event Listeners
        Array.from(document.querySelectorAll('#dealBtn')).forEach(b => b.addEventListener('click', startRound));
        Array.from(document.querySelectorAll('#resetBtn')).forEach(b => b.addEventListener('click', resetGame));
        Array.from(document.querySelectorAll('#hitBtn, #hitBtnMobile')).forEach(b => b.addEventListener('click', playerHit));
        Array.from(document.querySelectorAll('#standBtn, #standBtnMobile')).forEach(b => b.addEventListener('click', playerStand));
        Array.from(document.querySelectorAll('#newRoundBtn, #newRoundBtnMobile')).forEach(b => b.addEventListener('click', startRound));

        // fallback helpers (do not remove)
        function numberWithDots(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function renderBetFromRaw(rawDigits) {
            try {
                els.bet.dataset.raw = rawDigits;
                const formatted = rawDigits === '' ? '' : numberWithDots(rawDigits);
                els.bet.value = formatted;
            } catch (e) {}
        }

        function getBetNumeric() {
            try {
                const raw = (els.bet.dataset.raw || els.bet.value || '').toString().replace(/\D/g, '');
                const num = raw === '' ? 0 : parseInt(raw, 10);
                return Number.isNaN(num) ? 0 : num;
            } catch (e) {
                return 0;
            }
        }
    </script>
</body>

</html>