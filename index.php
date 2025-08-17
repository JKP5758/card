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
            z-index: 3000;
            /* Deck berada di atas animasi kartu */
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
            z-index: 9000;
        }

        /* preset button animation */
        .scale-95 {
            transform: scale(0.95);
        }

        /* CLS Prevention - Reserve space for elements that might be hidden/shown */
        .preset-buttons-container {
            min-height: 32px;
            /* Reserve space for preset buttons */
        }

        .mute-icon-container {
            width: 20px;
            height: 20px;
            position: relative;
        }

        .mute-icon {
            position: absolute;
            inset: 0;
            opacity: 1;
            transition: opacity 0.2s ease;
        }

        .mute-icon.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* Status box to avoid CLS: reserve height and animate opacity only */
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
    </style>
</head>

<body class="bg-slate-900 text-slate-100 min-h-screen font-sans">
    <div class="max-w-5xl mx-auto p-4 sm:p-6">
        <header class="flex items-center max-sm:flex-col justify-between gap-4">
            <h1 class="text-xl sm:text-2xl font-bold">Mini Blackjack 21 <span class="text-slate-400 text-base">(beta test)</span></h1>
            <div class="flex items-center flex-row-reverse gap-4 max-sm:w-full max-sm:justify-between">
                <div class="text-right">
                    <div class="text-sm text-slate-400">Saldo</div>
                    <div id="balance" class="text-2xl font-bold">Rp 100.000</div>
                </div>
            </div>
        </header>

        <!-- Controls Desktop -->
        <section class="mt-4 max-sm:hidden flex w-full sm:grid-cols-3 gap-3">
            <div class="sm:col-span-1 bg-slate-800/60 rounded-2xl p-4 shadow-glow w-2/5">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-300">Aksi Taruhan</div>
                    <div class="text-xs text-slate-400">Total Taruhan: <span id="prizePool" class="font-bold text-base text-white">Rp 0</span></div>
                </div>
                <label class="block text-xs text-slate-300 mb-1 mt-2" for="bet">Jumlah Taruhan</label>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-2 rounded-xl bg-slate-900/40 text-slate-300">Rp</span>
                    <input id="bet" type="text" inputmode="numeric" pattern="[0-9.]*"
                        class="w-full rounded-xl bg-slate-900 border border-slate-700 p-2 outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="10.000" value="10.000" />
                </div>
                <div class="mt-3 flex gap-2">
                    <button id="dealBtn" class="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition px-4 py-2 font-semibold">Mulai</button>
                    <button id="raiseBtn" class="flex-1 rounded-xl bg-blue-600 hover:bg-blue-500 active:bg-blue-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed hidden">Naikkan</button>
                    <button id="resetBtn" class="rounded-xl bg-slate-700 hover:bg-slate-600 active:bg-slate-800 transition px-4 py-2">Reset</button>
                </div>

                <!-- Preset buttons - hanya muncul saat dalam ronde -->
                <div id="presetButtons" class="mt-2 grid grid-cols-4 gap-1 preset-buttons-container opacity-0 pointer-events-none">
                    <button class="preset-btn rounded-lg bg-red-600 hover:bg-red-500 active:bg-red-700 transition px-2 py-1 text-xs font-semibold" data-value="-10000">-10k</button>
                    <button class="preset-btn rounded-lg bg-red-600 hover:bg-red-500 active:bg-red-700 transition px-2 py-1 text-xs font-semibold" data-value="-5000">-5k</button>
                    <button class="preset-btn rounded-lg bg-green-600 hover:bg-green-500 active:bg-green-700 transition px-2 py-1 text-xs font-semibold" data-value="5000">+5k</button>
                    <button class="preset-btn rounded-lg bg-green-600 hover:bg-green-500 active:bg-green-700 transition px-2 py-1 text-xs font-semibold" data-value="10000">+10k</button>
                </div>
                <p class="text-xs text-slate-400 mt-2">Aturan payout: Menang = Taruhan x2, Kalah = Taruhan Hangus, Seri = Taruhan Dikembalikan</p>
            </div>

            <div class="sm:col-span-2 bg-slate-800/60 rounded-2xl p-4 shadow-glow w-3/5">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-300">Aksi</div>
                </div>
                <div class="mt-2 flex gap-3">
                    <button id="hitBtn" class="rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Ambil Kartu</button>
                    <button id="standBtn" class="rounded-xl bg-amber-600 hover:bg-amber-500 active:bg-amber-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Sudahi Giliran</button>
                    <div class="flex items-center gap-2">
                        <button id="muteBtnMobile" class="p-2 rounded-lg bg-slate-800/60 hover:bg-slate-700/60 transition-colors">
                            <div class="mute-icon-container">
                                <!-- Mute Icon -->
                                <svg id="muteIconMobile" class="mute-icon w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.794L4.383 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.383l4.017-2.794a1 1 0 011.617.794zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>

                                <!-- Unmute Icon -->
                                <svg id="unmuteIconMobile" class="mute-icon w-5 h-5 opacity-0 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.794L4.383 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.383l4.017-2.794a1 1 0 011.617.794z" clip-rule="evenodd"></path>
                                    <path d="M14.5 6.5a1 1 0 011.5 1.32 4 4 0 010 4.36 1 1 0 11-1.5 1.32 6 6 0 000-7.04z"></path>
                                    <path d="M16.5 4.5a1 1 0 011.5 1.32 8 8 0 010 8.36 1 1 0 11-1.5 1.32 10 10 0 000-11z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
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
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-300">Aksi</div>
                </div>
                <div class="mt-2 flex gap-3 flex-row-reverse">
                    <button id="standBtnMobile" class="rounded-xl bg-amber-600 hover:bg-amber-500 active:bg-amber-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Sudahi Giliran</button>
                    <button id="hitBtnMobile" class="rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed" disabled>Ambil Kartu</button>
                    <div class="flex items-center gap-2">
                        <button id="muteBtn" class="p-2 rounded-lg bg-slate-800/60 hover:bg-slate-700/60 transition-colors">
                            <div class="mute-icon-container">
                                <!-- Mute Icon -->
                                <svg id="muteIcon" class="mute-icon w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.794L4.383 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.383l4.017-2.794a1 1 0 011.617.794zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>

                                <!-- Unmute Icon -->
                                <svg id="unmuteIcon" class="mute-icon w-5 h-5 opacity-0 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.794L4.383 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.383l4.017-2.794a1 1 0 011.617.794z" clip-rule="evenodd"></path>
                                    <path d="M14.5 6.5a1 1 0 011.5 1.32 4 4 0 010 4.36 1 1 0 11-1.5 1.32 6 6 0 000-7.04z"></path>
                                    <path d="M16.5 4.5a1 1 0 011.5 1.32 8 8 0 010 8.36 1 1 0 11-1.5 1.32 10 10 0 000-11z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
                <div class="status-box mt-3 text-slate-300"><span id="statusMobile" class="status-text"></span></div>
            </div>
        </section>

        <!-- Aksi Taruhan Mobile - di bagian bawah -->
        <section class="mt-4 sm:hidden">
            <div class="bg-slate-800/60 rounded-2xl p-4 shadow-glow">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-300">Aksi Taruhan</div>
                    <div class="text-xs text-slate-400">Total: <span id="prizePoolMobile" class="font-bold text-base text-white">Rp 0</span></div>
                </div>
                <label class="block text-xs text-slate-300 mb-1 mt-2" for="betMobile">Jumlah Taruhan</label>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-2 rounded-xl bg-slate-900/40 text-slate-300">Rp</span>
                    <input id="betMobile" type="text" inputmode="numeric" pattern="[0-9.]*"
                        class="w-full rounded-xl bg-slate-900 border border-slate-700 p-2 outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="10.000" value="10.000" />
                </div>
                <div class="mt-3 flex gap-2">
                    <button id="dealBtnMobile" class="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition px-4 py-2 font-semibold">Mulai</button>
                    <button id="raiseBtnMobile" class="flex-1 rounded-xl bg-blue-600 hover:bg-blue-500 active:bg-blue-700 transition px-4 py-2 font-semibold disabled:opacity-40 disabled:cursor-not-allowed hidden">Naikkan</button>
                    <button id="resetBtnMobile" class="rounded-xl bg-slate-700 hover:bg-slate-600 active:bg-slate-800 transition px-4 py-2">Reset</button>
                </div>

                <!-- Preset buttons mobile - hanya muncul saat dalam ronde -->
                <div id="presetButtonsMobile" class="mt-2 grid grid-cols-4 gap-1 preset-buttons-container opacity-0 pointer-events-none">
                    <button class="preset-btn rounded-lg bg-red-600 hover:bg-red-500 active:bg-red-700 transition px-2 py-1 text-xs font-semibold" data-value="-10000">-10k</button>
                    <button class="preset-btn rounded-lg bg-red-600 hover:bg-red-500 active:bg-red-700 transition px-2 py-1 text-xs font-semibold" data-value="-5000">-5k</button>
                    <button class="preset-btn rounded-lg bg-green-600 hover:bg-green-500 active:bg-green-700 transition px-2 py-1 text-xs font-semibold" data-value="5000">+5k</button>
                    <button class="preset-btn rounded-lg bg-green-600 hover:bg-green-500 active:bg-green-700 transition px-2 py-1 text-xs font-semibold" data-value="10000">+10k</button>
                </div>
                <p class="text-xs text-slate-400 mt-2">Aturan payout: Menang = Taruhan x2, Kalah = Taruhan Hangus, Seri = Taruhan Dikembalikan</p>
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
        // ====== AUDIO MANAGER (REVISED) ======
        class AudioManager {
            constructor() {
                this.sounds = {};
                this.music = null;
                this.isMuted = false;
                // Volume utama (master) harus antara 0.0 (hening) dan 1.0 (paling keras).
                // Nilai di atas 1.0 akan dianggap sebagai 1.0 oleh browser.
                this.volume = 2.0;
                // Pengali volume untuk tiap suara. Nilai 1.0 adalah volume normal.
                // Nilai 2.0 akan membuatnya 2x lebih keras dari volume master (dibatasi hingga 1.0).
                this.individualVolumes = {
                    click: 0.1,
                    cardFlip: 1.5,
                    cardToHand: 2.0, // Dinaikkan ke batas maksimum (2.0) agar paling keras
                    cardBack: 0.8,
                    win: 1.0,
                    lose: 0.2,
                    slede: 1.0,
                    bg: 0.2 // Mengembalikan ke nilai awal agar musik tidak mati
                };
                this.initSounds();
            }

            initSounds() {
                // Inisialisasi semua efek suara
                this.sounds = {
                    click: new Audio('./assets/audio/click.ogg'),
                    cardFlip: new Audio('./assets/audio/card-flip.ogg'),
                    cardToHand: new Audio('./assets/audio/card-to-hand.ogg'),
                    cardBack: new Audio('./assets/audio/card-back.ogg'),
                    win: new Audio('./assets/audio/win.ogg'),
                    lose: new Audio('./assets/audio/lose.ogg'),
                    slede: new Audio('./assets/audio/slede.ogg'),
                    bg: new Audio('./assets/audio/bg.ogg')
                };

                // Lakukan preload untuk semua suara agar siap dimainkan
                Object.values(this.sounds).forEach(sound => {
                    sound.preload = 'auto';
                });

                // Siapkan musik latar
                this.music = this.sounds.bg;
                if (this.music) {
                    this.music.loop = true;
                    this.updateMusicVolume(); // Atur volume awal musik
                }
            }

            // Method khusus untuk update volume musik
            updateMusicVolume() {
                if (!this.music) return;

                // FIX: Gunakan nullish coalescing (??) agar nilai 0 tidak di-fallback ke 0.5
                const bgMultiplier = this.individualVolumes.bg ?? 0.5;
                // Musik dibuat sedikit lebih pelan (0.3x) dari volume utama
                const finalBgVolume = this.volume * 0.3 * bgMultiplier;
                this.music.volume = Math.min(1.0, finalBgVolume);
            }

            play(soundName) {
                if (this.isMuted || !this.sounds[soundName]) return;

                try {
                    // Kloning node audio agar bisa dimainkan tumpang tindih
                    const sound = this.sounds[soundName].cloneNode();

                    // Hitung dan terapkan volume HANYA pada klon, tepat sebelum dimainkan
                    const individualMultiplier = this.individualVolumes[soundName] ?? 1.0;
                    const finalVolume = this.volume * individualMultiplier;
                    sound.volume = Math.min(1.0, finalVolume); // Pastikan volume tidak lebih dari 1.0

                    // Log untuk debugging
                    console.log(`Playing ${soundName}: masterVolume=${this.volume}, individualMultiplier=${individualMultiplier}, finalVolume=${sound.volume}`);

                    sound.currentTime = 0; // Mulai dari awal

                    // Mainkan suara dengan penanganan error
                    const playPromise = sound.play();
                    if (playPromise !== undefined) {
                        playPromise.catch(error => {
                            // Abaikan error autoplay yang diblokir browser
                            if (error.name !== 'NotAllowedError') {
                                console.error(`Audio play failed for ${soundName}:`, error);
                            }
                        });
                    }
                } catch (e) {
                    console.error(`Audio error on playing ${soundName}:`, e);
                }
            }

            playMusic() {
                if (this.isMuted || !this.music) return;
                // Cek jika musik sudah berjalan, jangan lakukan apa-apa
                if (this.music.currentTime > 0 && !this.music.paused) {
                    return;
                }
                this.music.play().catch(e => console.log('Music play failed:', e));
            }

            stopMusic() {
                if (this.music) {
                    this.music.pause();
                    this.music.currentTime = 0;
                }
            }

            toggleMute() {
                this.isMuted = !this.isMuted;
                if (this.isMuted) {
                    this.music.pause(); // Cukup pause, jangan reset
                } else {
                    this.playMusic();
                }
                return this.isMuted;
            }

            // Setter untuk volume utama (master)
            setVolume(newVolume) {
                this.volume = Math.max(0, Math.min(1, newVolume));
                // Saat volume utama diubah, hanya perlu update suara yang berjalan terus-menerus (musik)
                this.updateMusicVolume();
            }

            // Setter untuk volume individual
            setIndividualVolume(soundName, newVolume) {
                if (this.individualVolumes.hasOwnProperty(soundName)) {
                    // Bolehkan pengali > 1 untuk membuat suara lebih keras
                    this.individualVolumes[soundName] = Math.max(0, Math.min(2.0, newVolume));
                    console.log(`Volume multiplier for ${soundName} set to: ${this.individualVolumes[soundName]}`);

                    // Jika yang diubah adalah musik, update volumenya langsung
                    if (soundName === 'bg') {
                        this.updateMusicVolume();
                    }
                }
            }

            getIndividualVolume(soundName) {
                return this.individualVolumes[soundName] || 1.0;
            }

            getAllVolumeSettings() {
                return {
                    ...this.individualVolumes
                };
            }
        }


        // Initialize audio manager
        const audioManager = new AudioManager();

        // Function to stop all audio clones
        function stopAllAudioClones() {
            // FIX: Fungsi ini seharusnya tidak menghentikan musik latar.
            // Dibiarkan kosong karena klon efek suara tidak ditambahkan ke DOM dan akan
            // di-garbage collect secara otomatis. Menghapus isinya akan mencegah
            // musik latar dimulai ulang setiap ronde.
        }

        // ====== DEVELOPER AUDIO CONTROLS ======
        // Script untuk developer mengatur volume individual sound
        window.audioControls = {
            // Set volume untuk sound tertentu (0.0 - 2.0)
            setSoundVolume: (soundName, volume) => {
                audioManager.setIndividualVolume(soundName, volume);
            },

            // Get volume untuk sound tertentu
            getSoundVolume: (soundName) => {
                return audioManager.getIndividualVolume(soundName);
            },

            // Get semua volume settings
            getAllVolumes: () => {
                return audioManager.getAllVolumeSettings();
            },

            // Set volume untuk semua sound sekaligus
            setAllVolumes: (volume) => {
                const sounds = ['click', 'cardFlip', 'cardToHand', 'cardBack', 'win', 'lose', 'slede', 'bg'];
                sounds.forEach(sound => {
                    audioManager.setIndividualVolume(sound, volume);
                });
            },

            // Reset semua volume ke default (1.0)
            resetAllVolumes: () => {
                audioManager.setAllVolumes(1.0);
            },

            // Play test sound
            testSound: (soundName) => {
                audioManager.play(soundName);
            }
        };

        // Console log untuk developer
        console.log('🎵 Audio Controls Available:');
        console.log('- audioControls.setSoundVolume("click", 1.5)');
        console.log('- audioControls.getSoundVolume("click")');
        console.log('- audioControls.getAllVolumes()');
        console.log('- audioControls.setAllVolumes(1.2)');
        console.log('- audioControls.resetAllVolumes()');
        console.log('- audioControls.testSound("click")');
        console.log('Available sounds: click, cardFlip, cardToHand, cardBack, win, lose, slede, bg');

        // Test individual volume settings
        console.log('🔊 Current volume settings:', audioManager.getAllVolumeSettings());

        // ====== GAME STATE ======
        const ASSET_PATH = './assets/img'; // Ubah path agar gambar bisa dimuat
        const SUITS = ['H', 'W', 'K', 'S'];
        const RANKS = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        let deck = [];
        let playerHand = [];
        let botHand = [];
        let balance = 100000;
        let totalBet = 0; // Total taruhan yang ditaruh di atas meja
        let roundActive = false;
        let botHiddenCardEl = null;
        let isProcessingAction = false; // Flag untuk mencegah spam tombol

        // =========================
        // RIGGING CONFIG (editable)
        // =========================
        // Atur parameter berikut sesuai kebutuhan presentasi.
        // - limit_fair: < this value => fair play
        // - limit_rigged: >= this value => mustahil menang (bandar selalu menang)
        // - riggedChance: peluang (0..1) pada rentang [limit_fair, limit_rigged) dimana kemenangan pemain diubah jadi kalah
        const RIG_CONFIG = {
            limit_fair: 750000, // < 200k => fair
            limit_rigged: 1000000, // >= 300k => mustahil menang
            riggedChance: 0.2 // 20% chance curang pada 200k - 300k
        };

        // Flag untuk menandai apakah rigging sedang aktif
        let isRiggingActive = false;
        let riggedCards = []; // Kartu yang akan digunakan untuk rigging

        function applyRigging(playerBalance, normalResult) {
            // normalResult: 'win' | 'lose' | 'draw'
            if (playerBalance < RIG_CONFIG.limit_fair) {
                return normalResult; // fair play
            }

            if (playerBalance >= RIG_CONFIG.limit_fair && playerBalance < RIG_CONFIG.limit_rigged) {
                // ada peluang mengubah kemenangan jadi kalah
                if (normalResult === 'win') {
                    if (Math.random() < RIG_CONFIG.riggedChance) {
                        return 'lose';
                    }
                }
                return normalResult;
            }

            if (playerBalance >= RIG_CONFIG.limit_rigged) {
                // mustahil menang di atas ambang ini
                if (normalResult === 'win') {
                    return 'lose';
                }
                return normalResult;
            }

            return normalResult;
        }

        // Fungsi untuk mengecek apakah perlu rigging berdasarkan saldo + potensi kemenangan
        function needsRigging(playerBalance, potentialWin) {
            const totalAfterWin = playerBalance + potentialWin;
            return totalAfterWin >= RIG_CONFIG.limit_rigged;
        }

        // Fungsi untuk mendapatkan kartu yang tepat untuk rigging
        function getRiggedCard(targetTotal, currentTotal) {
            const neededValue = targetTotal - currentTotal;

            // Cari kartu yang ada di deck dengan nilai yang tepat
            let perfectCard = null;
            let perfectCardIndex = -1;

            // Cari kartu dengan nilai yang tepat
            for (let i = 0; i < deck.length; i++) {
                const card = deck[i];
                const cardValue = valueOfCard(card.rank);
                if (cardValue === neededValue) {
                    perfectCard = card;
                    perfectCardIndex = i;
                    break;
                }
            }

            // Jika tidak ada kartu dengan nilai tepat, cari kartu yang aman
            if (!perfectCard) {
                for (let i = 0; i < deck.length; i++) {
                    const card = deck[i];
                    const cardValue = valueOfCard(card.rank);
                    if (currentTotal + cardValue <= 21) {
                        perfectCard = card;
                        perfectCardIndex = i;
                        break;
                    }
                }
            }

            // Jika masih tidak ada, ambil kartu pertama dari deck
            if (!perfectCard) {
                perfectCard = deck[0];
                perfectCardIndex = 0;
            }

            // Hapus kartu dari deck
            if (perfectCardIndex > -1) {
                deck.splice(perfectCardIndex, 1);
            }

            return perfectCard;
        }

        const els = {
            balance: document.getElementById('balance'),
            bet: document.getElementById('bet'),
            dealBtn: document.getElementById('dealBtn'),
            resetBtn: document.getElementById('resetBtn'),
            hitBtn: document.getElementById('hitBtn'),
            standBtn: document.getElementById('standBtn'),
            status: document.getElementById('status'),
            deckSpot: document.getElementById('deckSpot'),
            botHand: document.getElementById('botHand'),
            playerHand: document.getElementById('playerHand'),
            botTotal: document.getElementById('botTotal'),
            playerTotal: document.getElementById('playerTotal'),
            year: document.getElementById('year'),
            cardTemplate: document.getElementById('cardTemplate'),
            toast: document.getElementById('toast'),
            toastMsg: document.getElementById('toastMsg'),
            prizePool: document.getElementById('prizePool'),
            raiseBtn: document.getElementById('raiseBtn'),
            // Tambahkan elemen-elemen mobile
            hitBtnMobile: document.getElementById('hitBtnMobile'),
            standBtnMobile: document.getElementById('standBtnMobile'),
            statusMobile: document.getElementById('statusMobile'),
            // Tambahkan elemen preset buttons
            presetButtons: document.getElementById('presetButtons'),
            // Tambahkan elemen mobile untuk aksi taruhan
            betMobile: document.getElementById('betMobile'),
            dealBtnMobile: document.getElementById('dealBtnMobile'),
            raiseBtnMobile: document.getElementById('raiseBtnMobile'),
            resetBtnMobile: document.getElementById('resetBtnMobile'),
            presetButtonsMobile: document.getElementById('presetButtonsMobile'),
            prizePoolMobile: document.getElementById('prizePoolMobile'),
            // Audio controls
            muteBtn: document.getElementById('muteBtn'),
            muteIcon: document.getElementById('muteIcon'),
            unmuteIcon: document.getElementById('unmuteIcon'),
            // Audio controls mobile
            muteBtnMobile: document.getElementById('muteBtnMobile'),
            muteIconMobile: document.getElementById('muteIconMobile'),
            unmuteIconMobile: document.getElementById('unmuteIconMobile')
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
                deal: [els.dealBtn, els.dealBtnMobile].filter(b => b),
                reset: [els.resetBtn, els.resetBtnMobile].filter(b => b),
                raise: [els.raiseBtn, els.raiseBtnMobile].filter(b => b),
            };
        }

        const fmtRupiah = n => `Rp ${n.toLocaleString('id-ID')}`;

        function updateBalanceUI() {
            els.balance.textContent = fmtRupiah(balance);
            if (balance < 1000) {
                showToast("Saldo Anda tidak mencukupi. Silakan Reset.", "lose", 5000);
            }
        }

        function updatePrizePoolUI() {
            els.prizePool.textContent = fmtRupiah(totalBet);
            if (els.prizePoolMobile) {
                els.prizePoolMobile.textContent = fmtRupiah(totalBet);
            }
        }

        function numberWithDots(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function getBetNumeric(inputEl) {
            const raw = (inputEl.dataset.raw || inputEl.value || '').toString().replace(/\D/g, '');
            const num = raw === '' ? 0 : parseInt(raw, 10);
            return Number.isNaN(num) ? 0 : num;
        }

        function renderBetFromRaw(rawDigits, inputEl) {
            inputEl.dataset.raw = rawDigits;
            const formatted = rawDigits === '' ? '' : numberWithDots(rawDigits);
            inputEl.value = formatted;
        }

        // Fungsi baru untuk memperbarui status tombol "Naikkan"
        function updateRaiseButtonState() {
            const raiseAmount = getBetNumeric(els.bet);
            const isEnabled = roundActive && !isProcessingAction && raiseAmount > 0 && balance >= raiseAmount;
            els.raiseBtn.disabled = !isEnabled;
            if (els.raiseBtnMobile) {
                els.raiseBtnMobile.disabled = !isEnabled;
            }
        }

        // Tangani input untuk nominal taruhan
        els.bet.addEventListener('input', (e) => {
            const digits = (e.target.value || '').replace(/\D/g, '');
            renderBetFromRaw(digits, els.bet);
            // Sync dengan input mobile
            if (els.betMobile) {
                renderBetFromRaw(digits, els.betMobile);
            }
            // Perbarui status tombol setiap kali input berubah
            updateRaiseButtonState();
        });
        els.bet.addEventListener('blur', () => {
            let v = getBetNumeric(els.bet);
            if (v === 0) v = 1000;
            if (v > balance) v = balance;
            renderBetFromRaw(String(v), els.bet);
            // Sync dengan input mobile
            if (els.betMobile) {
                renderBetFromRaw(String(v), els.betMobile);
            }
            // Perbarui status tombol setelah input kehilangan fokus
            updateRaiseButtonState();
        });

        // Tangani input untuk nominal taruhan mobile
        if (els.betMobile) {
            els.betMobile.addEventListener('input', (e) => {
                const digits = (e.target.value || '').replace(/\D/g, '');
                renderBetFromRaw(digits, els.betMobile);
                // Sync dengan input desktop
                renderBetFromRaw(digits, els.bet);
                // Perbarui status tombol setiap kali input berubah
                updateRaiseButtonState();
            });
            els.betMobile.addEventListener('blur', () => {
                let v = getBetNumeric(els.betMobile);
                if (v === 0) v = 1000;
                if (v > balance) v = balance;
                renderBetFromRaw(String(v), els.betMobile);
                // Sync dengan input desktop
                renderBetFromRaw(String(v), els.bet);
                // Perbarui status tombol setelah input kehilangan fokus
                updateRaiseButtonState();
            });
        }

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
            cols.deal.forEach(b => b.classList.toggle('hidden', inRound));
            cols.raise.forEach(b => b.classList.toggle('hidden', !inRound));
            cols.reset.forEach(b => b.disabled = false);

            // Tampilkan/sembunyikan preset buttons dengan opacity
            [els.presetButtons, els.presetButtonsMobile].forEach(container => {
                if (container) {
                    if (inRound) {
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';
                    } else {
                        container.style.opacity = '0';
                        container.style.pointerEvents = 'none';
                    }
                }
            });

            // Panggil fungsi untuk memperbarui status tombol "Naikkan"
            updateRaiseButtonState();
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

            // Play card dealing sound tepat saat animasi dimulai
            audioManager.play('cardToHand');

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
                // Play card flip sound tepat saat kartu mulai dibalik
                audioManager.play('cardFlip');
                requestAnimationFrame(() => inner.classList.add('[transform:rotateY(180deg)]'));
                await new Promise(r => setTimeout(r, 300)); // Percepat animasi dari 480ms ke 300ms
                cardEl.classList.add('glow');
                setTimeout(() => cardEl.classList.remove('glow'), 600);
            }
            // Kartu tersembunyi tidak perlu suara card-flip
            return cardEl;
        }

        async function collectCardsBack() {
            const allCards = Array.from(document.querySelectorAll('#playerHand .card, #botHand .card'));
            if (allCards.length === 0) return;

            // Ambil semua kartu yang ada (tidak hanya 2 kartu pertama)
            const botCards = Array.from(document.querySelectorAll('#botHand .card'));
            const playerCards = Array.from(document.querySelectorAll('#playerHand .card'));

            console.log('Bot cards:', botCards.length, 'Player cards:', playerCards.length);

            // Susun kartu sesuai urutan: kartu terakhir bot - kartu pertama bot - kartu terakhir user - kartu pertama user
            const orderedCards = [];

            // Tambahkan semua kartu bot (dari terakhir ke pertama)
            for (let i = botCards.length - 1; i >= 0; i--) {
                if (botCards[i]) {
                    orderedCards.push(botCards[i]);
                }
            }

            // Tambahkan semua kartu player (dari terakhir ke pertama)
            for (let i = playerCards.length - 1; i >= 0; i--) {
                if (playerCards[i]) {
                    orderedCards.push(playerCards[i]);
                }
            }

            console.log('Total cards to animate:', orderedCards.length);

            // Pastikan ada kartu untuk dianimasikan
            if (orderedCards.length === 0) return;

            // Balik semua kartu terlebih dahulu dengan suara card-flip
            for (let i = 0; i < orderedCards.length; i++) {
                const card = orderedCards[i];
                const inner = card.querySelector('.card-inner');
                if (inner) {
                    // Selalu balik kartu ke posisi tertutup (card back)
                    inner.classList.remove('[transform:rotateY(180deg)]');
                    audioManager.play('cardFlip');
                    await new Promise(r => setTimeout(r, 150));
                }
            }

            // Tunggu sebentar setelah semua kartu dibalik
            await new Promise(r => setTimeout(r, 200));

            // Pindahkan semua kartu ke body untuk animasi
            orderedCards.forEach((c, i) => {
                const r = c.getBoundingClientRect();
                document.body.appendChild(c);
                c.style.position = 'absolute';
                c.style.left = (r.left + window.scrollX) + 'px';
                c.style.top = (r.top + window.scrollY) + 'px';
                c.style.zIndex = 2000 + i;
            });

            const cardW = orderedCards[0].getBoundingClientRect().width;
            const cardH = orderedCards[0].getBoundingClientRect().height;
            const deckC = centerOf(els.deckSpot);

            // Animasi kartu bergerak ke deck secara berurutan (tidak menunggu kartu sebelumnya selesai)
            for (let i = 0; i < orderedCards.length; i++) {
                const card = orderedCards[i];

                // Play slede sound untuk setiap kartu yang bergerak
                audioManager.play('slede');

                // Animasi kartu ke deck tanpa await (tidak menunggu selesai)
                gsap.to(card, {
                    duration: 0.3,
                    left: deckC.x - cardW / 2,
                    top: deckC.y - cardH / 2,
                    rotation: 20,
                    scale: 0.6,
                    ease: 'power2.in'
                });

                // Delay singkat antar kartu (tidak menunggu animasi selesai)
                await new Promise(r => setTimeout(r, 80));
            }

            // Tunggu sebentar sebelum menghapus kartu
            await new Promise(r => setTimeout(r, 200));

            // Hapus semua kartu
            orderedCards.forEach(c => {
                if (c && c.parentNode) {
                    c.remove();
                }
            });
        }

        function drawFromDeck() {
            if (deck.length === 0) {
                buildDeck();
                shuffle(deck);
            }
            return deck.pop();
        }

        async function startRound() {
            if (isProcessingAction) return;
            isProcessingAction = true;

            // Stop all audio clones before starting new round
            stopAllAudioClones();

            let initialBet = Math.max(1000, getBetNumeric(els.bet));
            if (initialBet > balance) {
                initialBet = balance;
                renderBetFromRaw(String(initialBet), els.bet);
                setStatus('Taruhan otomatis disesuaikan ke saldo Anda.', true);
            }
            if (initialBet < 1000) {
                showToast("Taruhan minimal Rp 1.000.", "info", 2000);
                isProcessingAction = false;
                return;
            }

            // Kurangi saldo dan tambahkan ke prize pool
            balance -= initialBet;
            totalBet = initialBet;
            updateBalanceUI();
            updatePrizePoolUI();
            renderBetFromRaw('0', els.bet); // Reset input taruhan setelah "Deal"
            if (els.betMobile) {
                renderBetFromRaw('0', els.betMobile);
            }

            await collectCardsBack();
            resetTable();
            totalBet = initialBet;
            updatePrizePoolUI();

            roundActive = true;
            isProcessingAction = false;
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
            await dealCardAnimated(els.botHand, b1, {
                faceDown: false
            });
            els.botTotal.textContent = handTotal(botHand);

            const p2 = drawFromDeck();
            playerHand.push(p2);
            await dealCardAnimated(els.playerHand, p2, {
                faceDown: false
            });
            els.playerTotal.textContent = handTotal(playerHand);

            // Kartu bot kedua tersembunyi
            const b2 = drawFromDeck();
            botHand.push(b2);
            botHiddenCardEl = await dealCardAnimated(els.botHand, {
                img: `./assets/img/card-bg.jpg`,
                suit: '',
                rank: ''
            }, {
                faceDown: true
            });

            if (handTotal(playerHand) === 21) {
                isProcessingAction = true;
                setStatus("Blackjack! Kamu menang!");
                await new Promise(r => setTimeout(r, 1000));
                checkWinner();
                return;
            }
            isProcessingAction = false;
            setControls({
                inRound: true
            });
        }

        async function playerHit() {
            if (isProcessingAction || !roundActive) return;
            isProcessingAction = true;

            const card = drawFromDeck();
            playerHand.push(card);
            await dealCardAnimated(els.playerHand, card, {
                faceDown: false
            });
            els.playerTotal.textContent = handTotal(playerHand);

            const total = handTotal(playerHand);
            if (total > 21) {
                setStatus("Bust! Kamu kalah.");
                await new Promise(r => setTimeout(r, 1000));
                endRound('lose');
            } else if (total === 21) {
                setStatus("21! Kamu menang!");
                await new Promise(r => setTimeout(r, 1000));
                checkWinner();
            } else {
                isProcessingAction = false;
                setControls({
                    inRound: true
                });
            }
        }

        async function playerStand() {
            if (isProcessingAction || !roundActive) return;
            isProcessingAction = true;

            // Buka kartu bot yang tersembunyi
            const inner = botHiddenCardEl.querySelector('.card-inner');
            const hiddenCard = botHand[1];
            const img = botHiddenCardEl.querySelector('img');
            img.src = hiddenCard.img;
            img.alt = `${hiddenCard.suit}${hiddenCard.rank}`;

            // Play card flip sound saat kartu tersembunyi bot dibalik
            audioManager.play('cardFlip');
            requestAnimationFrame(() => inner.classList.add('[transform:rotateY(180deg)]'));
            await new Promise(r => setTimeout(r, 300)); // Percepat animasi dari 480ms ke 300ms
            els.botTotal.textContent = handTotal(botHand);

            setStatus("Giliran bandar...");
            await new Promise(r => setTimeout(r, 1000));

            // Cek apakah perlu rigging
            const playerTotal = handTotal(playerHand);
            const potentialWin = totalBet * 2;
            isRiggingActive = needsRigging(balance, potentialWin);

            if (isRiggingActive) {
                // Rigging aktif - bot akan mendapatkan kartu yang tepat
                const botCurrentTotal = handTotal(botHand);
                let targetTotal;

                // Tentukan target total bot
                if (playerTotal <= 21) {
                    // Jika pemain 20, bot bisa dapat 21 atau 20
                    if (playerTotal === 20) {
                        targetTotal = Math.random() < 0.7 ? 21 : 20; // 70% chance dapat 21, 30% chance seri
                    } else if (playerTotal === 21) {
                        targetTotal = 21; // Seri dengan blackjack
                    } else {
                        targetTotal = Math.max(playerTotal + 1, 17); // Minimal 17, atau lebih tinggi dari pemain
                    }
                } else {
                    targetTotal = 17; // Pemain bust, bot cukup 17
                }

                // Bot draw sampai mencapai target atau bust
                while (handTotal(botHand) < targetTotal && handTotal(botHand) < 21) {
                    let card;
                    if (handTotal(botHand) < targetTotal) {
                        // Gunakan kartu yang tepat untuk mencapai target
                        card = getRiggedCard(targetTotal, handTotal(botHand));
                        // Hapus kartu dari deck agar tidak double
                        const cardIndex = deck.findIndex(c => c.suit === card.suit && c.rank === card.rank);
                        if (cardIndex > -1) {
                            deck.splice(cardIndex, 1);
                        }
                    } else {
                        card = drawFromDeck();
                    }

                    botHand.push(card);
                    await dealCardAnimated(els.botHand, card, {
                        faceDown: false
                    });
                    els.botTotal.textContent = handTotal(botHand);
                    await new Promise(r => setTimeout(r, 700));
                }
            } else {
                // Normal play - bot draw sampai >= 17
                while (handTotal(botHand) < 17) {
                    const card = drawFromDeck();
                    botHand.push(card);
                    await dealCardAnimated(els.botHand, card, {
                        faceDown: false
                    });
                    els.botTotal.textContent = handTotal(botHand);
                    await new Promise(r => setTimeout(r, 700));
                }
            }

            checkWinner();
        }

        async function checkWinner() {
            const playerTotal = handTotal(playerHand);
            const botTotal = handTotal(botHand);
            let result;

            if (playerTotal > 21) {
                result = 'lose';
                setStatus("Kamu Bust! Kamu kalah.");
            } else if (botTotal > 21) {
                result = 'win';
                setStatus("Bandar Bust! Kamu menang!");
            } else if (playerTotal > botTotal) {
                result = 'win';
                setStatus("Kamu menang!");
            } else if (playerTotal < botTotal) {
                result = 'lose';
                setStatus("Kamu kalah.");
            } else {
                result = 'draw';
                setStatus("Seri.");
            }

            await new Promise(r => setTimeout(r, 1500));

            // Reset rigging flag
            isRiggingActive = false;

            endRound(result);
        }

        function endRound(result) {
            roundActive = false;
            if (result === 'win') {
                balance += totalBet * 2;
                showToast(`Menang! + ${fmtRupiah(totalBet)}`, 'win');
                audioManager.play('win');
            } else if (result === 'lose') {
                showToast(`Kalah! - ${fmtRupiah(totalBet)}`, 'lose');
                audioManager.play('lose');
            } else { // Seri
                balance += totalBet; // Kembalikan taruhan awal
                showToast("Seri.", 'info');
            }
            totalBet = 0; // Reset total taruhan setelah putaran selesai
            updateBalanceUI();
            updatePrizePoolUI(); // Update UI
            isProcessingAction = false;
            setControls({
                inRound: false
            });
            // Reset input taruhan ke nilai default setelah ronde berakhir
            renderBetFromRaw('10000', els.bet);
            if (els.betMobile) {
                renderBetFromRaw('10000', els.betMobile);
            }
            updateRaiseButtonState(); // Perbarui status tombol setelah ronde berakhir
        }

        function raiseBet() {
            if (isProcessingAction || !roundActive) return;
            isProcessingAction = true;
            const raiseAmount = getBetNumeric(els.bet);

            if (raiseAmount > 0 && balance >= raiseAmount) {
                balance -= raiseAmount;
                totalBet += raiseAmount;
                updateBalanceUI();
                updatePrizePoolUI();
                renderBetFromRaw('0', els.bet); // Reset input taruhan setelah "Raise"
                if (els.betMobile) {
                    renderBetFromRaw('0', els.betMobile);
                }
                showToast(`Taruhan dinaikkan sebesar ${fmtRupiah(raiseAmount)}`, 'info');
            } else {
                showToast("Jumlah taruhan tidak valid atau saldo tidak mencukupi.", "info");
            }
            isProcessingAction = false;
            updateRaiseButtonState(); // Perbarui status tombol setelah aksi selesai
        }

        function resetGame() {
            if (isProcessingAction) return;
            balance = 100000;
            updateBalanceUI();
            renderBetFromRaw('10000', els.bet);
            resetTable();
            totalBet = 0; // Pastikan totalBet direset saat game direset
            updatePrizePoolUI();
            roundActive = false;
            isRiggingActive = false; // Reset rigging flag
            setControls({
                inRound: false
            });
            showToast("Game di-reset!", "info");
        }

        // Audio event listeners
        els.muteBtn.addEventListener('click', () => {
            const isMuted = audioManager.toggleMute();

            // Toggle opacity untuk desktop
            els.muteIcon.style.opacity = isMuted ? '0' : '1';
            els.muteIcon.style.pointerEvents = isMuted ? 'none' : 'auto';
            els.unmuteIcon.style.opacity = isMuted ? '1' : '0';
            els.unmuteIcon.style.pointerEvents = isMuted ? 'auto' : 'none';

            // Sync dengan mobile
            if (els.muteIconMobile) {
                els.muteIconMobile.style.opacity = isMuted ? '0' : '1';
                els.muteIconMobile.style.pointerEvents = isMuted ? 'none' : 'auto';
            }
            if (els.unmuteIconMobile) {
                els.unmuteIconMobile.style.opacity = isMuted ? '1' : '0';
                els.unmuteIconMobile.style.pointerEvents = isMuted ? 'auto' : 'none';
            }

            audioManager.play('click');
        });

        // Audio event listeners mobile
        if (els.muteBtnMobile) {
            els.muteBtnMobile.addEventListener('click', () => {
                const isMuted = audioManager.toggleMute();

                // Toggle opacity untuk mobile
                els.muteIconMobile.style.opacity = isMuted ? '0' : '1';
                els.muteIconMobile.style.pointerEvents = isMuted ? 'none' : 'auto';
                els.unmuteIconMobile.style.opacity = isMuted ? '1' : '0';
                els.unmuteIconMobile.style.pointerEvents = isMuted ? 'auto' : 'none';

                // Sync dengan desktop
                els.muteIcon.style.opacity = isMuted ? '0' : '1';
                els.muteIcon.style.pointerEvents = isMuted ? 'none' : 'auto';
                els.unmuteIcon.style.opacity = isMuted ? '1' : '0';
                els.unmuteIcon.style.pointerEvents = isMuted ? 'auto' : 'none';

                audioManager.play('click');
            });
        }

        // Global event listeners
        window.addEventListener('load', () => {
            renderBetFromRaw('10000', els.bet);
            if (els.betMobile) {
                renderBetFromRaw('10000', els.betMobile);
            }
            resetTable();
            updateBalanceUI();
            setControls({
                inRound: false
            });

            // Start background music
            audioManager.playMusic();
        });

        // Event listener untuk Deal
        els.dealBtn.addEventListener('click', () => {
            audioManager.play('click');
            startRound();
        });
        if (els.dealBtnMobile) {
            els.dealBtnMobile.addEventListener('click', () => {
                audioManager.play('click');
                startRound();
            });
        }

        // Event listener untuk Reset
        els.resetBtn.addEventListener('click', () => {
            audioManager.play('click');
            resetGame();
        });
        if (els.resetBtnMobile) {
            els.resetBtnMobile.addEventListener('click', () => {
                audioManager.play('click');
                resetGame();
            });
        }

        // Event listener untuk Hit dan Stand
        Array.from(document.querySelectorAll('#hitBtn, #hitBtnMobile')).forEach(b => b.addEventListener('click', () => {
            audioManager.play('click');
            playerHit();
        }));
        Array.from(document.querySelectorAll('#standBtn, #standBtnMobile')).forEach(b => b.addEventListener('click', () => {
            audioManager.play('click');
            playerStand();
        }));

        // Event listener untuk Raise
        els.raiseBtn.addEventListener('click', () => {
            audioManager.play('click');
            raiseBet();
        });
        if (els.raiseBtnMobile) {
            els.raiseBtnMobile.addEventListener('click', () => {
                audioManager.play('click');
                raiseBet();
            });
        }

        // Event listener untuk preset buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('preset-btn')) {
                audioManager.play('click');
                const value = parseInt(e.target.dataset.value);
                const currentValue = getBetNumeric(els.bet);
                const newValue = Math.max(0, currentValue + value);
                renderBetFromRaw(String(newValue), els.bet);
                if (els.betMobile) {
                    renderBetFromRaw(String(newValue), els.betMobile);
                }
                updateRaiseButtonState();

                // Feedback visual
                e.target.classList.add('scale-95');
                setTimeout(() => e.target.classList.remove('scale-95'), 150);
            }
        });

        // fallback helpers (do not remove)
        function numberWithDots(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function renderBetFromRaw(rawDigits, inputEl) {
            try {
                inputEl.dataset.raw = rawDigits;
                const formatted = rawDigits === '' ? '' : numberWithDots(rawDigits);
                inputEl.value = formatted;
            } catch (e) {}
        }

        function getBetNumeric(inputEl) {
            try {
                const raw = (inputEl.dataset.raw || inputEl.value || '').toString().replace(/\D/g, '');
                const num = raw === '' ? 0 : parseInt(raw, 10);
                return Number.isNaN(num) ? 0 : num;
            } catch (e) {
                return 0;
            }
        }
    </script>
</body>

</ht