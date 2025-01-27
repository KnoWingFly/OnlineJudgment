<!-- menu.php -->
<div class="w-full flex justify-center px-4 py-2">
    <div class="w-full max-w-5xl bg-black rounded-[40px] border border-white p-4">
        <nav class="flex justify-center items-center">
            <ul class="flex items-center gap-2">
                <?php if (!isset($_SESSION['isloggedin'])): ?>
                    <li>
                        <a href="/login.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                            Login
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['isloggedin'])): ?>
                    <li>
                        <a href="/index.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                            Problems
                        </a>
                    </li>
                    <li>
                        <a href="/submissions.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                            Submissions
                        </a>
                    </li>
                    <li>
                        <a href="/scoreboard.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                            Scoreboard
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="/faq.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                        FAQ
                    </a>
                </li>

                <?php if (isset($_SESSION['isloggedin'])): ?>
                    <li>
                        <a href="/chat.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                            Chat
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin'])): ?>
                    <li>
                        <a href="/admin/admin.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                            Admin
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['isloggedin'])): ?>
                    <li>
                        <a href="/logout.php" class="inline-block px-4 py-2 text-white hover:bg-white/10 rounded-[20px] transition-colors">
                            Logout
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- <script src="https://cdn.tailwindcss.com"></script> -->