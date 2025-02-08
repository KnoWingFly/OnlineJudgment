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
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-black text-gray-200 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <?php include('Layout/header.php'); ?>
        <?php include('Layout/menu.php'); ?>

        <!-- Main Content -->
        <main class="m space-y-8">
            <!-- Deskripsi Lomba -->
            <section id="deskripsi" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-2xl font-bold text-blue-400 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    DESKRIPSI LOMBA
                </h2>
                <p class="text-gray-300 leading-relaxed">
                    Internal Contest merupakan kegiatan kompetisi UMNPC (UMN Programming Club) untuk melatih anggota dalam persiapan perlombaan Competitive Programming (CP). Setiap tim (2-3 orang) akan menyelesaikan soal/masalah melalui web kontes dalam waktu tertentu dengan solusi yang cepat dan efisien.
                </p>
            </section>

            <!-- Penghargaan -->
            <section id="penghargaan" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-2xl font-bold text-blue-400 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    PENGHARGAAN
                </h2>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4 text-center hover:bg-blue-900/20 transition-all">
                        <div class="text-xl font-bold text-emerald-400">Juara 1</div>
                        <p class="text-sm text-gray-300 mt-2">Sejumlah uang + Sertifikat</p>
                    </div>
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4 text-center hover:bg-blue-900/20 transition-all">
                        <div class="text-xl font-bold text-purple-400">Juara 2</div>
                        <p class="text-sm text-gray-300 mt-2">Sejumlah uang + Sertifikat</p>
                    </div>
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4 text-center hover:bg-blue-900/20 transition-all">
                        <div class="text-xl font-bold text-amber-400">Juara 3</div>
                        <p class="text-sm text-gray-300 mt-2">Sejumlah uang + Sertifikat</p>
                    </div>
                </div>
            </section>

            <!-- Timeline -->
            <section id="timeline" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-2xl font-bold text-blue-400 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    TIMELINE
                </h2>
                <div class="space-y-4 border-l-2 border-gray-700 pl-4">
                    <div class="relative pl-6 pb-4">
                        <span class="absolute left-[-13px] top-0 w-4 h-4 bg-blue-500 rounded-full"></span>
                        <h3 class="text-lg font-semibold text-gray-200">16.15 - 17.15</h3>
                        <p class="text-gray-400">Pembukaan ruangan & absensi</p>
                    </div>
                    <div class="relative pl-6 pb-4">
                        <span class="absolute left-[-13px] top-0 w-4 h-4 bg-blue-500 rounded-full"></span>
                        <h3 class="text-lg font-semibold text-gray-200">17.15 - 17.30</h3>
                        <p class="text-gray-400">Pembacaan aturan & persiapan</p>
                    </div>
                    <div class="relative pl-6 pb-4">
                        <span class="absolute left-[-13px] top-0 w-4 h-4 bg-blue-500 rounded-full"></span>
                        <h3 class="text-lg font-semibold text-gray-200">17.30 - 20.30</h3>
                        <p class="text-gray-400">Kontes berlangsung</p>
                    </div>
                    <div class="relative pl-6">
                        <span class="absolute left-[-13px] top-0 w-4 h-4 bg-blue-500 rounded-full"></span>
                        <h3 class="text-lg font-semibold text-gray-200">20.30 - 20.50</h3>
                        <p class="text-gray-400">Penutupan</p>
                    </div>
                </div>
            </section>

            <!-- Peraturan & Teknis -->
            <section id="peraturan" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-2xl font-bold text-blue-400 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    PERATURAN & TEKNIS LOMBA
                </h2>
                <div class="space-y-4">
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-200 mb-3">Persiapan Kontes</h3>
                        <ul class="space-y-2 text-gray-300 text-sm">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Tim wajib 2-3 orang dengan 1 laptop
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Stop kontak & wifi disediakan panitia
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Scoreboard dibekukan 1 jam terakhir
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-200 mb-3">Saat Kontes</h3>
                        <ul class="space-y-2 text-gray-300 text-sm">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Durasi 3 jam melalui web kontes
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Dilarang kerjasama antar tim
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Dilarang menggunakan Google/AI
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Template code tidak diperbolehkan
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-200 mb-3">Teknis Lomba</h3>
                        <ul class="space-y-2 text-gray-300 text-sm">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Bahasa: Python, C/C++, Java, Golang
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Program harus terminate dalam batas waktu
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Jenis kasus uji: Accepted, Compile Error, Wrong Answer, Time Limit, Runtime Error
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Klarifikasi & Penilaian -->
            <section id="penilaian" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-2xl font-bold text-blue-400 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    KLARIFIKASI & PENILAIAN
                </h2>
                <div class="space-y-4">
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-200 mb-3">Klarifikasi</h3>
                        <ul class="space-y-2 text-gray-300 text-sm list-disc pl-5">
                            <li>Jawaban berupa: Ya/Tidak/Deskripsi soal/Tidak valid/Tidak ada komentar</li>
                            <li>Format soal: Judul, deskripsi, batasan, input/output, contoh</li>
                        </ul>
                    </div>
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-200 mb-3">Sistem Penilaian</h3>
                        <ul class="space-y-2 text-gray-300 text-sm list-disc pl-5">
                            <li>Prioritas: Jumlah soal → Waktu terakhir + penalti</li>
                            <li>Penalti: 30 menit/submission salah</li>
                            <li>Contoh: 2 submission salah → +60 menit</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Narahubung -->
            <section id="narahubung" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-2xl font-bold text-blue-400 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    NARAHUBUNG
                </h2>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4 hover:bg-blue-900/10 transition-all">
                        <h3 class="font-semibold text-gray-200 mb-2">Welliam Prasetio Hoedoto</h3>
                        <p class="text-sm text-gray-400">
                            <span class="block">089525551237</span>
                            <span class="block">LINE: wlliampra2</span>
                        </p>
                    </div>
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4 hover:bg-blue-900/10 transition-all">
                        <h3 class="font-semibold text-gray-200 mb-2">Gavriel Donovan</h3>
                        <p class="text-sm text-gray-400">
                            <span class="block">085156752086</span>
                            <span class="block">LINE: d0n0v4n_123</span>
                        </p>
                    </div>
                    <div class="bg-gray-700/30 border border-gray-600 rounded-lg p-4 hover:bg-blue-900/10 transition-all">
                        <h3 class="font-semibold text-gray-200 mb-2">Nicholas Dharma T.</h3>
                        <p class="text-sm text-gray-400">
                            <span class="block">088808003798</span>
                            <span class="block">LINE: nick99tan</span>
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="mt-12 py-6 border-t border-gray-700 text-center text-gray-400">
            <?php include('Layout/footer.php'); ?>
        </footer>
    </div>

    <script>
        // Smooth scroll functionality
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>