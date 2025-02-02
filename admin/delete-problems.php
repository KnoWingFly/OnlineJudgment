<?php
session_start();
if (!isset($_SESSION['isloggedin']) || $_SESSION['admin'] != true) {
    header("Location: ../login.php");
    exit(0);
}

include('../settings.php');
include('admin-layout.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $problemId = intval($_POST['problem_id']);

    $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
    if (!$cn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    mysqli_begin_transaction($cn);

    try {
        $problemDir = "../problems/" . $problemId;

        // Delete problem from database
        $deleteQuery = "DELETE FROM problems WHERE id = ?";
        $stmt = mysqli_prepare($cn, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $problemId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to delete problem from database");
        }

        // Delete original directory immediately
        if (file_exists($problemDir)) {
            function deleteDirectory($dir)
            {
                if (!file_exists($dir))
                    return true;
                if (!is_dir($dir))
                    return unlink($dir);
                foreach (scandir($dir) as $item) {
                    if ($item == '.' || $item == '..')
                        continue;
                    $path = $dir . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($path)) {
                        deleteDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
                return rmdir($dir);
            }
            if (!deleteDirectory($problemDir)) {
                throw new Exception("Failed to delete problem directory");
            }
        }

        // Get remaining higher IDs in ascending order
        $getHigherIds = "SELECT id FROM problems WHERE id > ? ORDER BY id ASC";
        $stmt = mysqli_prepare($cn, $getHigherIds);
        mysqli_stmt_bind_param($stmt, "i", $problemId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $higherIds = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $higherIds[] = $row['id'];
        }

        // Rename directories in ascending order
        foreach ($higherIds as $oldId) {
            $newId = $oldId - 1;
            $oldDir = "../problems/" . $oldId;
            $newDir = "../problems/" . $newId;

            if (file_exists($oldDir) && !rename($oldDir, $newDir)) {
                throw new Exception("Failed to rename directory from $oldId to $newId");
            }
        }

        // Bulk update IDs in database
        $updateQuery = "UPDATE problems SET id = id - 1 WHERE id > ? ORDER BY id ASC";
        $stmt = mysqli_prepare($cn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "i", $problemId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update problem IDs");
        }

        mysqli_commit($cn);
        $message = "Problem deleted successfully and IDs reordered";
    } catch (Exception $e) {
        mysqli_rollback($cn);
        $message = "Error: " . $e->getMessage();
    }

    mysqli_close($cn);
}

// Fetch remaining problems
$cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
if (!$cn) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT id, title, time_limit, created_at FROM problems ORDER BY id";
$result = mysqli_query($cn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Problems - Admin Panel</title>
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
        <?php if (isset($message)): ?>
            <div
                class="mb-6 p-4 rounded-lg <?= strpos($message, 'Error') !== false ? 'bg-red-900/50 text-red-300' : 'bg-emerald-900/20 text-emerald-400' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-zinc-800 p-8 rounded-xl border border-zinc-700 shadow-xl">
            <div class="mb-8 pb-6 border-b border-zinc-700">
                <h1 class="text-3xl font-bold text-blue-400">Delete Problems</h1>
                <p class="mt-2 text-zinc-400">Manage and remove programming problems</p>

                <!-- Navigation Menu -->
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
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Problems
                    </a>
                    <a href="setting.php"
                        class="inline-flex items-center px-4 py-2 bg-zinc-700 text-zinc-300 rounded-lg hover:bg-zinc-600 transition-colors">
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

            <!-- Problems Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-zinc-900 text-left">
                            <th class="px-6 py-4 text-sm font-semibold text-blue-400">ID</th>
                            <th class="px-6 py-4 text-sm font-semibold text-blue-400">Title</th>
                            <th class="px-6 py-4 text-sm font-semibold text-blue-400">Time Limit</th>
                            <th class="px-6 py-4 text-sm font-semibold text-blue-400">Created At</th>
                            <th class="px-6 py-4 text-sm font-semibold text-blue-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-700">
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-zinc-700/50 transition-colors">
                                <td class="px-6 py-4 text-zinc-300"><?php echo htmlspecialchars($row['id']); ?></td>
                                <td class="px-6 py-4 text-zinc-300"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td class="px-6 py-4 text-zinc-300"><?php echo htmlspecialchars($row['time_limit']); ?> s
                                </td>
                                <td class="px-6 py-4 text-zinc-300"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td class="px-6 py-4">
                                    <button
                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>')"
                                        class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="border-t border-zinc-800 py-6 mt-12">
        <?php include('../Layout/footer.php'); ?>
    </footer>

    <script>
        function confirmDelete(problemId, problemTitle) {
            if (confirm('Are you sure you want to delete the problem "' + problemTitle + '"? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                form.appendChild(actionInput);

                const problemInput = document.createElement('input');
                problemInput.type = 'hidden';
                problemInput.name = 'problem_id';
                problemInput.value = problemId;
                form.appendChild(problemInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>