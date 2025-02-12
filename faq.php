<?php
session_start();
if (isset($_SESSION['isloggedin'])) {
    $username = $_SESSION['username'];
    $userid = $_SESSION['userid'];
}
include('settings.php');
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Contest - Rules & Regulations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #000000;
        }
        .gradient-border {
            position: relative;
            background: rgba(15, 15, 15, 0.95);
            border-radius: 1rem;
        }
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 1rem; 
            padding: 1px;
            background: linear-gradient(45deg, rgba(59,130,246,0.2), rgba(168,85,247,0.2), rgba(236,72,153,0.2));
            -webkit-mask: 
                linear-gradient(#000 0 0) content-box, 
                linear-gradient(#000 0 0);
            mask: 
                linear-gradient(#000 0 0) content-box, 
                linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }
        .section-active {
            background: rgba(59,130,246,0.1) !important;
            border-color: rgba(59,130,246,0.4) !important;
        }
    </style>
</head>
<body class="bg-black text-gray-200">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <?php include('Layout/header.php'); ?>
        <?php include('Layout/menu.php'); ?>

        <nav class="sticky top-16 z-10 bg-black/80 backdrop-blur-sm mb-8">
            <div class="flex overflow-x-auto scrollbar-hide gap-2 py-3">
                <a href="#deskripsi" class="px-4 py-2 rounded-full bg-gray-900/50 border border-gray-800 hover:bg-gray-800/30 transition-all text-sm font-medium section-nav">Deskripsi</a>
                <a href="#penghargaan" class="px-4 py-2 rounded-full bg-gray-900/50 border border-gray-800 hover:bg-gray-800/30 transition-all text-sm font-medium section-nav">Penghargaan</a>
                <a href="#timeline" class="px-4 py-2 rounded-full bg-gray-900/50 border border-gray-800 hover:bg-gray-800/30 transition-all text-sm font-medium section-nav">Timeline</a>
                <a href="#peraturan" class="px-4 py-2 rounded-full bg-gray-900/50 border border-gray-800 hover:bg-gray-800/30 transition-all text-sm font-medium section-nav">Peraturan</a>
                <a href="#narahubung" class="px-4 py-2 rounded-full bg-gray-900/50 border border-gray-800 hover:bg-gray-800/30 transition-all text-sm font-medium section-nav">Kontak</a>
            </div>
        </nav>

        <main class="space-y-8">
            <section id="deskripsi" class="gradient-border p-8 relative">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-blue-900/20 rounded-xl border border-blue-800/30">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-100">Deskripsi Lomba</h2>
                        <p class="text-sm text-gray-400 mt-1">Tentang kompetisi programming internal</p>
                    </div>
                </div>
                <p class="text-gray-300 leading-relaxed pl-4 border-l-2 border-gray-800/50 ml-3">
                    Internal Contest merupakan kegiatan kompetisi UMNPC (UMN Programming Club) untuk melatih anggota dalam persiapan perlombaan Competitive Programming (CP). Setiap tim (2-3 orang) akan menyelesaikan soal/masalah melalui web kontes dalam waktu tertentu dengan solusi yang cepat dan efisien.
                </p>
            </section>

            <section id="penghargaan" class="gradient-border p-8 relative">
                <div class="flex items-center gap-4 mb-8">
                    <div class="p-3 bg-purple-900/20 rounded-xl border border-purple-800/30">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-100">Penghargaan</h2>
                        <p class="text-sm text-gray-400 mt-1">Hadiah untuk pemenang kompetisi</p>
                    </div>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="group p-6 rounded-xl bg-gradient-to-br from-gray-900/50 to-black/30 border border-gray-800/50 hover:border-blue-500/30 transition-all">
                        <div class="mb-4">
                            <div class="w-fit p-3 bg-emerald-900/20 rounded-lg border border-emerald-800/30">
                                <span class="text-2xl">🥇</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-emerald-400 mb-2">Juara 1</h3>
                        <p class="text-sm text-gray-400">Total hadiah senilai<br>Sejumlah Uang + Sertifikat</p>
                    </div>
                    <div class="group p-6 rounded-xl bg-gradient-to-br from-gray-900/50 to-black/30 border border-gray-800/50 hover:border-purple-500/30 transition-all">
                        <div class="mb-4">
                            <div class="w-fit p-3 bg-purple-900/20 rounded-lg border border-purple-800/30">
                                <span class="text-2xl">🥈</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-purple-400 mb-2">Juara 2</h3>
                        <p class="text-sm text-gray-400">Total hadiah senilai<br>Sejumlah Uang + Sertifikat</p>
                    </div>
                    <div class="group p-6 rounded-xl bg-gradient-to-br from-gray-900/50 to-black/30 border border-gray-800/50 hover:border-amber-500/30 transition-all">
                        <div class="mb-4">
                            <div class="w-fit p-3 bg-amber-900/20 rounded-lg border border-amber-800/30">
                                <span class="text-2xl">🥉</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-amber-400 mb-2">Juara 3</h3>
                        <p class="text-sm text-gray-400">Total hadiah senilai<br>Sejumlah Uang + Sertifikat</p>
                    </div>
                </div>
            </section>

            <section id="timeline" class="gradient-border p-8 relative">
                <div class="flex items-center gap-4 mb-8">
                    <div class="p-3 bg-pink-900/20 rounded-xl border border-pink-800/30">
                        <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-100">Timeline</h2>
                        <p class="text-sm text-gray-400 mt-1">Jadwal pelaksanaan kompetisi</p>
                    </div>
                </div>
                <div class="relative pl-8 ml-3 space-y-10">
                    <div class="relative">
                        <div class="absolute left-[-28px] top-1 w-4 h-4 bg-blue-500 rounded-full border-4 border-black"></div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="px-3 py-1 rounded-full bg-blue-900/20 text-blue-400 text-sm font-medium">16:15</div>
                                <div class="h-px flex-1 bg-gradient-to-r from-blue-500/20 to-transparent"></div>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-100">Pembukaan & Absensi</h3>
                            <p class="text-gray-400 text-sm">Registrasi peserta dan persiapan awal</p>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute left-[-28px] top-1 w-4 h-4 bg-blue-500 rounded-full border-4 border-black"></div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="px-3 py-1 rounded-full bg-blue-900/20 text-blue-400 text-sm font-medium">17:15</div>
                                <div class="h-px flex-1 bg-gradient-to-r from-blue-500/20 to-transparent"></div>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-100">Briefing</h3>
                            <p class="text-gray-400 text-sm">Pengenalan sistem kompetisi dan peraturan</p>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute left-[-28px] top-1 w-4 h-4 bg-blue-500 rounded-full border-4 border-black"></div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="px-3 py-1 rounded-full bg-blue-900/20 text-blue-400 text-sm font-medium">17:30</div>
                                <div class="h-px flex-1 bg-gradient-to-r from-blue-500/20 to-transparent"></div>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-100">Kontes Dimulai</h3>
                            <p class="text-gray-400 text-sm">Pemecahan soal programming selama 3 jam</p>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute left-[-28px] top-1 w-4 h-4 bg-blue-500 rounded-full border-4 border-black"></div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="px-3 py-1 rounded-full bg-blue-900/20 text-blue-400 text-sm font-medium">20:30</div>
                                <div class="h-px flex-1 bg-gradient-to-r from-blue-500/20 to-transparent"></div>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-100">Penutupan</h3>
                            <p class="text-gray-400 text-sm">Pengumuman pemenang dan penyerahan hadiah</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="peraturan" class="gradient-border p-8 relative">
                <div class="flex items-center gap-4 mb-8">
                    <div class="p-3 bg-amber-900/20 rounded-xl border border-amber-800/30">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-100">Peraturan</h2>
                        <p class="text-sm text-gray-400 mt-1">Ketentuan dan aturan kompetisi</p>
                    </div>
                </div>
                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="p-6 rounded-xl bg-gray-900/20 border border-gray-800/50 hover:border-blue-500/30 transition-all">
                        <h3 class="text-lg font-semibold text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Persiapan Kontes
                        </h3>
                        <ul class="space-y-3 text-gray-300 pl-2">
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-blue-400/80"></div>
                                <span>Tim wajib 2-3 orang dengan 1 laptop</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-blue-400/80"></div>
                                <span>Stop kontak & wifi disediakan panitia</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-blue-400/80"></div>
                                <span>Scoreboard dibekukan 1 jam terakhir</span>
                            </li>
                        </ul>
                    </div>
                    <div class="p-6 rounded-xl bg-gray-900/20 border border-gray-800/50 hover:border-purple-500/30 transition-all">
                        <h3 class="text-lg font-semibold text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Saat Kontes
                        </h3>
                        <ul class="space-y-3 text-gray-300 pl-2">
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-purple-400/80"></div>
                                <span>Durasi 3 jam melalui web kontes</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-purple-400/80"></div>
                                <span>Dilarang kerjasama antar tim</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-purple-400/80"></div>
                                <span>Dilarang menggunakan Google/AI</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-purple-400/80"></div>
                                <span>Template code tidak diperbolehkan</span>
                            </li>
                        </ul>
                    </div>
                    <div class="p-6 rounded-xl bg-gray-900/20 border border-gray-800/50 hover:border-amber-500/30 transition-all">
                        <h3 class="text-lg font-semibold text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Teknis Lomba
                        </h3>
                        <ul class="space-y-3 text-gray-300 pl-2">
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-amber-400/80"></div>
                                <span>Bahasa: Python, C/C++, Java, Golang</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-amber-400/80"></div>
                                <span>Program harus terminate dalam batas waktu</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-1.5 h-1.5 mt-2 rounded-full bg-amber-400/80"></div>
                                <span>Jenis kasus uji: Accepted, Compile Error, Wrong Answer, Time Limit, Runtime Error</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="narahubung" class="gradient-border p-8 relative">
                <div class="flex items-center gap-4 mb-8">
                    <div class="p-3 bg-emerald-900/20 rounded-xl border border-emerald-800/30">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-100">Kontak Panitia</h2>
                        <p class="text-sm text-gray-400 mt-1">Hubungi kami jika ada pertanyaan</p>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="group p-6 rounded-xl bg-gray-900/20 border border-gray-800/50 hover:border-blue-500/30 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-900/20 border border-blue-800/30 flex items-center justify-center">
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-100">Welliam P. Hoedoto</h3>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <a href="tel:089525551237" class="flex items-center gap-2 text-gray-400 hover:text-blue-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                0895-2555-1237
                            </a>
                            <a href="https://line.me/ti/p/~wlliampra2" class="flex items-center gap-2 text-gray-400 hover:text-green-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                LINE: wlliampra2
                            </a>
                        </div>
                    </div>
                    <div class="group p-6 rounded-xl bg-gray-900/20 border border-gray-800/50 hover:border-purple-500/30 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-purple-900/20 border border-purple-800/30 flex items-center justify-center">
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-100">Gavriel Donovan</h3>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <a href="tel:085156752086" class="flex items-center gap-2 text-gray-400 hover:text-purple-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                0851-5675-2086
                            </a>
                            <a href="https://line.me/ti/p/~d0n0v4n_123" class="flex items-center gap-2 text-gray-400 hover:text-green-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                LINE: d0n0v4n_123
                            </a>
                        </div>
                    </div>
                    <div class="group p-6 rounded-xl bg-gray-900/20 border border-gray-800/50 hover:border-amber-500/30 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-amber-900/20 border border-amber-800/30 flex items-center justify-center">
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-100">Nicholas Dharma T.</h3>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <a href="tel:088808003798" class="flex items-center gap-2 text-gray-400 hover:text-amber-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                0888-0800-3798
                            </a>
                            <a href="https://line.me/ti/p/~nick99tan" class="flex items-center gap-2 text-gray-400 hover:text-green-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                LINE: nick99tan
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="mt-16 border-t border-gray-800/50 py-8">
            <?php include('Layout/footer.php'); ?>
        </footer>
    </div>

    <script>
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.section-nav');

        window.addEventListener('scroll', () => {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 150;
                const sectionHeight = section.clientHeight;
                if(pageYOffset >= sectionTop && pageYOffset < sectionTop + sectionHeight) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('section-active');
                if(link.getAttribute('href').includes(current)) {
                    link.classList.add('section-active');
                }
            });
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    </script>
</body>
</html>