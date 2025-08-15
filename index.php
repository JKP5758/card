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
        integrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .perspective {
            perspective: 1000px;
        }

        .card {
            width: 90px;
            height: 130px;
        }

        @media (min-width: 640px) {
            .card {
                width: 110px;
                height: 160px;
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

                <!-- visual Rp + input formatted -->
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

            <div class="sm:col-span-2 bg-slate-800/60 rounded-2xl p-4 shadow-glow">
                <div class="text-sm text-slate-300">Aksi</div>
                <div class="mt-2 flex gap-3">
                    <button id="hitBtn" class="rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Ambil</button>
                    <button id="standBtn" class="rounded-xl bg-amber-600 hover:bg-amber-500 active:bg-amber-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Sudahi</button>
                    <button id="newRoundBtn" class="rounded-xl bg-slate-700 hover:bg-slate-600 active:bg-slate-800 transition px-4 py-2 hidden">Ronde Baru</button>
                </div>
                <div id="status" class="mt-3 text-slate-300"></div>
            </div>
        </section>

        <!-- Table Area -->
        <section class="mt-6 bg-gradient-to-b from-slate-800/70 to-slate-900 rounded-3xl p-4 sm:p-6 shadow-glow relative overflow-hidden">
            <!-- Deck visual (sumber kartu terbang) -->
            <div id="deckSpot" class="absolute right-4 top-4 card perspective">
                <div class="card-inner relative w-full h-full rounded-xl">
                    <div class="card-face card-back-design absolute inset-0 grid place-items-center text-slate-300 text-sm">DECK</div>
                </div>
            </div>

            <!-- Bot Area -->
            <div class="mb-8 max-sm:mt-24">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-lg font-semibold">Bot</h2>
                    <div class="text-slate-400">Total: <span id="botTotal">0</span></div>
                </div>
                <div id="botHand" class="min-h-[140px] sm:min-h-[170px] flex flex-wrap items-center gap-3 mt-2"></div>
            </div>

            <hr class="border-slate-700/60 my-4" />

            <!-- Player Area -->
            <div>
                <div class="flex items-baseline justify-between">
                    <h2 class="text-lg font-semibold">Kamu</h2>
                    <div class="text-slate-400">Total: <span id="playerTotal">0</span></div>
                </div>
                <div id="playerHand" class="min-h-[140px] sm:min-h-[170px] flex flex-wrap items-center gap-3 mt-2"></div>
            </div>
        </section>

        <footer class="mt-8 text-center text-xs text-slate-500">&copy; <span id="year"></span> JKP Project – Mini Blackjack (untuk edukasi, bukan ajakan bermain judi).</footer>
    </div>

    <!-- Template kartu (depan & belakang) -->
    <template id="cardTemplate">
        <div class="card perspective select-none">
            <div class="card-inner relative w-full h-full rounded-xl transition-transform duration-500 ease-out">
                <div class="card-face absolute inset-0 card-front grid place-items-center card-back-design">
                    <!-- back (tertutup) -->
                    <div class="w-[86%] h-[90%] rounded-lg border border-white/10"></div>
                </div>
                <div class="card-face card-back-face absolute inset-0 grid place-items-center overflow-hidden">
                    <!-- front (terbuka) -->
                    <img class="w-full h-full object-contain" alt="" />
                </div>
            </div>
        </div>
    </template>

    <!-- Toast notifikasi (atas, mirip notifikasi HP) -->
    <div id="toast" class="hidden fixed left-1/2 transform -translate-x-1/2 top-4 px-4 py-2 rounded-full text-white shadow-lg">
        <span id="toastMsg" class="text-sm font-bold"></span>
    </div>

    <script>
        // ====== GAME STATE ======
        const ASSET_PATH = './assets/img';
        const SUITS = ['H', 'W', 'K', 'S'];
        const RANKS = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        let deck = [];
        let playerHand = [];
        let botHand = [];
        let balance = 100000;
        let currentBet = 10000;
        let roundActive = false;
        let botHiddenCardEl = null;

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
        };

        els.year.textContent = new Date().getFullYear();

        const fmtRupiah = n => `Rp ${n.toLocaleString('id-ID')}`;

        function updateBalanceUI() {
            els.balance.textContent = fmtRupiah(balance);
        }

        // ---- BET formatting & validation helpers ----
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

        // bind bet input behavior
        els.bet.addEventListener('input', (e) => {
            const digits = (e.target.value || '').replace(/\D/g, '');
            renderBetFromRaw(digits);
            els.dealBtn.disabled = !isBetValid();
        });
        els.bet.addEventListener('blur', () => {
            let v = getBetNumeric();
            if (v === 0) v = 1000;
            if (v > balance) v = balance;
            renderBetFromRaw(String(v));
            els.dealBtn.disabled = !isBetValid();
        });
        // initial render
        renderBetFromRaw(String(currentBet));

        // ---- Toast helpers ----
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
            els.status.textContent = '';
            botHiddenCardEl = null;
            playerHand = [];
            botHand = [];
        }

        function setControls({
            inRound
        }) {
            els.hitBtn.disabled = !inRound;
            els.standBtn.disabled = !inRound;
            // Deal only active when not in round and bet valid
            els.dealBtn.disabled = inRound || !isBetValid();
            els.newRoundBtn.classList.toggle('hidden', inRound);
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
            }
            return cardEl;
        }

        function drawFromDeck() {
            if (deck.length === 0) {
                buildDeck();
                shuffle(deck);
            }
            return deck.pop();
        }

        async function startRound() {
            currentBet = Math.max(1000, getBetNumeric());
            if (currentBet > balance) {
                currentBet = balance;
                renderBetFromRaw(String(currentBet));
                els.status.textContent = 'Taruhan otomatis disesuaikan ke saldo Anda.';
            }

            resetTable();
            roundActive = true;
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

            const b1 = drawFromDeck();
            botHand.push(b1);
            const el = await dealCardAnimated(els.botHand, b1, {
                faceDown: true
            });
            botHiddenCardEl = el.querySelector('.card-inner');

            els.status.textContent = 'Giliran kamu: Ambil atau Sudahi.';
        }

        async function playerHit() {
            if (!roundActive) return;
            const c = drawFromDeck();
            playerHand.push(c);
            await dealCardAnimated(els.playerHand, c, {
                faceDown: false
            });
            const total = handTotal(playerHand);
            els.playerTotal.textContent = total;

            if (total > 21) {
                els.status.textContent = 'Kamu bust (>21). Kamu kalah.';
                balance -= currentBet;
                updateBalanceUI();
                await revealBotThenFinish();
            }
        }

        async function playerStand() {
            if (!roundActive) return;
            els.status.textContent = 'Bot berpikir…';
            await revealBotThenFinish(true);
        }

        async function revealBotThenFinish(runBotDraw = false) {
            if (botHiddenCardEl) {
                botHiddenCardEl.classList.add('[transform:rotateY(180deg)]');
                await new Promise(r => setTimeout(r, 400));
                botHiddenCardEl = null;
            }

            if (runBotDraw) {
                while (handTotal(botHand) < 17) {
                    await new Promise(r => setTimeout(r, 350));
                    const c = drawFromDeck();
                    botHand.push(c);
                    await dealCardAnimated(els.botHand, c, {
                        faceDown: false
                    });
                }
            }

            const p = handTotal(playerHand);
            const b = handTotal(botHand);
            els.playerTotal.textContent = p;
            els.botTotal.textContent = b;

            let msg = '';
            if (p > 21) {
                msg = 'Kamu kalah.';
            } else if (b > 21) {
                msg = 'Bot bust. Kamu menang!';
                balance += currentBet;
            } else if (p > b) {
                msg = 'Kamu menang!';
                balance += currentBet;
            } else if (p < b) {
                msg = 'Kamu kalah.';
                balance -= currentBet;
            } else {
                msg = 'Seri.';
            }

            updateBalanceUI();
            els.status.textContent = msg + ` (P:${p} vs B:${b})`;
            const toastType = msg.toLowerCase().includes('menang') ? 'win' : msg.toLowerCase().includes('kalah') ? 'lose' : 'info';
            showToast(msg, toastType);

            roundActive = false;
            setControls({
                inRound: false
            });
        }

        function resetGame() {
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

        // event bindings
        els.dealBtn.addEventListener('click', startRound);
        els.hitBtn.addEventListener('click', playerHit);
        els.standBtn.addEventListener('click', playerStand);
        els.resetBtn.addEventListener('click', resetGame);
        els.newRoundBtn.addEventListener('click', startRound);
    </script>
</body>

</html>