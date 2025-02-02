<?php
/*
 * @copyright (c) 2008 Nicolo John Davis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

session_start();
if (!isset($_SESSION['isloggedin']) || !isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

include('../settings.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
    if (!$cn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $allowed_settings = ['start_time', 'end_time', 'leader_interval', 'chat_interval'];
    $error = false;

    foreach ($allowed_settings as $key) {
        if (isset($_POST[$key])) {
            $safe_key = mysqli_real_escape_string($cn, $key);
            $value = $_POST[$key];

            if (in_array($key, ['start_time', 'end_time'])) {
                $datetime = str_replace('T', ' ', $value);
                if (!strtotime($datetime)) {
                    $error_message = "Invalid datetime format for $key";
                    $error = true;
                    break;
                }
                $safe_value = mysqli_real_escape_string($cn, $datetime);
            } elseif (!is_numeric($value) || $value < 1000) {
                $error_message = "$key must be at least 1000 milliseconds";
                $error = true;
                break;
            } else {
                $safe_value = mysqli_real_escape_string($cn, $value);
            }

            mysqli_query($cn, "UPDATE system_settings SET setting_value = '$safe_value' 
                             WHERE setting_key = '$safe_key'");
        }
    }

    if (!$error) {
        $success_message = "Settings updated successfully!";
        generateSettingsFile($cn);
    }

    mysqli_close($cn);
}

function generateSettingsFile($cn)
{
    global $DBUSER, $DBPASS, $DBNAME, $points, $CODEDIR, $PROBLEMDIR;

    $result = mysqli_query($cn, "SELECT setting_key, setting_value FROM system_settings");
    $settings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $content = "<?php\n\n";
    $content .= "/*\n* @copyright (c) 2008 Nicolo John Davis\n";
    $content .= "* @license http://opensource.org/licenses/gpl-license.php GNU Public License\n*/\n\n";

    $content .= "\$DBUSER = '$DBUSER';\n";
    $content .= "\$DBPASS = '$DBPASS';\n";
    $content .= "\$DBNAME = '$DBNAME';\n\n";
    $content .= "\$points = array(" . implode(',', $points) . ");\n\n";
    $content .= "\$startTime = date_create('{$settings['start_time']}');\n";
    $content .= "\$endTime = date_create('{$settings['end_time']}');\n\n";
    $content .= "\$getLeaderInterval = {$settings['leader_interval']};\n";
    $content .= "\$getChatInterval = {$settings['chat_interval']};\n\n";
    $content .= "ini_set('display_errors', false);\n\n";
    $content .= "\$time = date_create();\n";
    $content .= "\$running = \$time >= \$startTime && \$time <= \$endTime;\n";
    $content .= "\$CODEDIR = '$CODEDIR';\n";
    $content .= "\$PROBLEMDIR = '$PROBLEMDIR';\n";
    $content .= "?>";

    file_put_contents('../settings.php', $content);
}

// Fetch current settings
$cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
$result = mysqli_query($cn, "SELECT setting_key, setting_value, setting_type FROM system_settings 
                           WHERE setting_key IN ('start_time', 'end_time', 'leader_interval', 'chat_interval') 
                           ORDER BY FIELD(setting_key, 'start_time', 'end_time', 'leader_interval', 'chat_interval')");
$settings = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_close($cn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contest Settings - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: #09090b;
        }

        .code-font {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
    <?php include('../timer.php'); ?>
</head>

<body class="bg-zinc-900 text-zinc-100 min-h-screen">
    <!-- Header -->
    <?php include('../Layout/header.php'); ?>
    <?php include('../Layout/menu.php'); ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (isset($success_message)): ?>
            <div class="mb-6 p-4 rounded-lg bg-emerald-900/20 text-emerald-400">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-900/50 text-red-300">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-zinc-800 p-8 rounded-xl border border-zinc-700 shadow-xl">
            <div class="mb-8 pb-6 border-b border-zinc-700">
                <h1 class="text-3xl font-bold text-blue-400">Contest Settings</h1>
                <p class="mt-2 text-zinc-400">Manage contest timing and intervals</p>


                <div class="mt-6 flex flex-wrap gap-4">
                    <a href="problems.php"
                        class="inline-flex items-center px-4 py-2 bg-zinc-700 text-zinc-300 rounded-lg hover:bg-zinc-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Add Problems
                    </a>
                    <a href="delete-problems.php"
                        class="inline-flex items-center px-4 py-2 bg-zinc-700 text-zinc-300 rounded-lg hover:bg-zinc-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Problems
                    </a>
                    <a href="setting.php"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Contest Settings
                    </a>
                </div>

            </div>

            <div class="space-y-8">
                <div class="bg-amber-900/20 text-amber-400 p-4 rounded-lg border border-amber-700/50">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="font-medium">Important Time Setting Information</p>
                            <p class="mt-1 text-sm">There is a 10-hour time offset in the system. When setting contest
                                times:</p>
                            <p class="text-sm"><strong>Subtract 10 hours from your intended time.</strong></p>
                            <p class="mt-2 text-sm bg-amber-900/30 p-2 rounded">Example: If you want the contest to
                                start at 15:00 (3 PM), set the time to 05:00 (5 AM)</p>
                        </div>
                    </div>
                </div>

                <form method="post" class="space-y-8">
                    <div class="bg-zinc-900 p-6 rounded-lg border border-zinc-700">
                        <h2 class="text-xl font-semibold text-blue-400 mb-6">Timing Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($settings as $setting): ?>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-300 mb-2">
                                        <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                                    </label>

                                    <?php if ($setting['setting_type'] === 'datetime'): ?>
                                        <input type="datetime-local" name="<?= htmlspecialchars($setting['setting_key']) ?>"
                                            value="<?= htmlspecialchars(str_replace(' ', 'T', $setting['setting_value'])) ?>"
                                            class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                            required>
                                        <p class="mt-2 text-sm text-zinc-500">
                                            Format: YYYY-MM-DD HH:MM:SS
                                        </p>
                                    <?php else: ?>
                                        <input type="number" name="<?= htmlspecialchars($setting['setting_key']) ?>"
                                            value="<?= htmlspecialchars($setting['setting_value']) ?>" min="1000"
                                            class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                            required>
                                        <p class="mt-2 text-sm text-zinc-500">
                                            Interval in milliseconds (minimum 1000)
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" name="update_settings"
                            class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="border-t border-zinc-800 py-6 mt-12">
        <?php include('../Layout/footer.php'); ?>
    </footer>
</body>

</html>