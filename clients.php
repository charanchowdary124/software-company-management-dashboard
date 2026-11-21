<?php
// clients.php
require_once __DIR__ . '/includes/header.php';

$searchQuery = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

$clients = get_clients();
$projects = get_projects();

// Helper to count projects per client
$getProjectCount = function($clientId) use ($projects) {
    $count = 0;
    foreach ($projects as $p) {
        if ($p['client_id'] === $clientId) {
            $count++;
        }
    }
    return $count;
};

// Filter clients
$filteredClients = array_filter($clients, function($c) use ($searchQuery, $statusFilter) {
    $matchesSearch = true;
    if ($searchQuery !== '') {
        $searchLower = strtolower($searchQuery);
        $matchesSearch = (
            strpos(strtolower($c['company_name']), $searchLower) !== false ||
            strpos(strtolower($c['contact_name']), $searchLower) !== false ||
            strpos(strtolower($c['industry']), $searchLower) !== false
        );
    }
    
    $matchesStatus = true;
    if ($statusFilter !== 'all') {
        $matchesStatus = ($c['status'] === $statusFilter);
    }
    
    return $matchesSearch && $matchesStatus;
});

$filteredClients = array_values($filteredClients);

// Visual borders
$colors = ['purple', 'cyan', 'pink', 'teal'];
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold font-outfit text-slate-50 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-teal-400 via-cyan-400 to-purple-400">
                Clients
            </h1>
            <p class="text-slate-300 text-xs mt-1">Manage client details, portfolios, and communication channels</p>
        </div>
    </div>

    <!-- Controls Panel -->
    <div class="glass-dark rounded-2xl p-6 border border-white/5 shadow-xl">
        <form action="clients.php" method="GET" id="clients-form" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="col-span-1 md:col-span-8 relative group">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-teal-400 transition-colors"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Search clients by company name, contact, or industry..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-800/50 border border-slate-700/60 rounded-xl focus:outline-none focus:border-teal-500 text-slate-50 placeholder-slate-500 transition-all focus:ring-2 focus:ring-teal-500/20"
                />
            </div>

            <!-- Status filter -->
            <div class="col-span-1 md:col-span-4 relative">
                <select
                    name="status"
                    onchange="document.getElementById('clients-form').submit()"
                    class="w-full pl-4 pr-10 py-2.5 bg-slate-800/50 border border-slate-700/60 rounded-xl text-slate-200 text-sm cursor-pointer appearance-none focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                    <option value="all">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="prospective" <?php echo $statusFilter === 'prospective' ? 'selected' : ''; ?>>Prospective</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
                <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
            </div>
        </form>
    </div>

    <!-- Client Cards Directory Grid -->
    <?php if (count($filteredClients) === 0): ?>
        <div class="text-center py-20 rounded-2xl glass-dark border border-white/5">
            <p class="text-slate-400 text-lg font-medium">No clients found</p>
            <p class="text-slate-500 text-sm mt-1">Try modifying your searches.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($filteredClients as $index => $client): 
                $cardColor = $colors[$index % count($colors)];
                $projCount = $getProjectCount($client['id']);
            ?>
                <div class="glass-premium rounded-2xl p-6 hover-lift hover-glow-<?php echo $cardColor; ?> border border-white/5 flex flex-col justify-between group transition-all duration-300">
                    <div>
                        <!-- Client Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-50 group-hover:text-teal-400 transition-colors leading-tight">
                                    <?php echo htmlspecialchars($client['company_name']); ?>
                                </h3>
                                <span class="inline-block px-2 py-0.5 mt-1 rounded bg-slate-800 border border-slate-700/50 text-[9px] font-semibold text-slate-400 uppercase tracking-wide">
                                    <?php echo htmlspecialchars($client['industry']); ?>
                                </span>
                            </div>
                            <?php echo render_status_badge($client['status'], 'sm'); ?>
                        </div>

                        <!-- Client notes -->
                        <p class="text-xs text-slate-400 leading-normal mb-6 italic">
                            "<?php echo htmlspecialchars($client['notes'] ?? 'No communication logs recorded.'); ?>"
                        </p>

                        <!-- Contact details list -->
                        <div class="space-y-2.5 mb-6 text-xs border-t border-slate-800/80 pt-4">
                            <div class="flex items-center gap-2.5 text-slate-300">
                                <i data-lucide="user" class="w-4 h-4 text-teal-400 flex-shrink-0"></i>
                                <span><?php echo htmlspecialchars($client['contact_name']); ?></span>
                            </div>
                            <div class="flex items-center gap-2.5 text-slate-300">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
                                <span class="truncate"><?php echo htmlspecialchars($client['email']); ?></span>
                            </div>
                            <div class="flex items-center gap-2.5 text-slate-300">
                                <i data-lucide="phone" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
                                <span><?php echo htmlspecialchars($client['phone']); ?></span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-300">
                                <i data-lucide="map-pin" class="w-4 h-4 text-slate-500 mt-0.5 flex-shrink-0"></i>
                                <span class="leading-tight"><?php echo htmlspecialchars($client['address']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Project Stat -->
                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-800/80 text-[10px]">
                        <span class="text-slate-500 uppercase font-semibold tracking-wider">Project Portfolio</span>
                        <span class="font-bold text-teal-400 bg-teal-500/10 px-2.5 py-1 rounded border border-teal-500/20">
                            <?php echo $projCount; ?> active projects
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
