<?php
// resources.php
require_once __DIR__ . '/includes/header.php';

$typeTab = $_GET['tab'] ?? 'all';
$resources = get_sams_resources();

// Filter resources
$filteredResources = array_filter($resources, function($r) use ($typeTab) {
    if ($typeTab === 'all') return true;
    return $r['resource_type'] === $typeTab;
});
$filteredResources = array_values($filteredResources);

$colors = ['purple', 'cyan', 'pink', 'teal'];
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold font-outfit text-slate-50 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-pink-400 via-rose-400 to-purple-400">
                Resources
            </h1>
            <p class="text-slate-300 text-xs mt-1">Monitor software license pools, graphics hardware, and layout spaces</p>
        </div>
    </div>

    <!-- Category Tabs Navigation -->
    <div class="flex flex-wrap gap-2 pb-2 border-b border-white/5">
        <a href="resources.php?tab=all" class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?php echo $typeTab === 'all' ? 'bg-gradient-to-r from-purple-500 to-pink-500 border-transparent text-white shadow-lg shadow-purple-500/20' : 'bg-slate-900/60 border-white/5 text-slate-400 hover:text-slate-200'; ?>">
            All Resources
        </a>
        <a href="resources.php?tab=software" class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?php echo $typeTab === 'software' ? 'bg-gradient-to-r from-purple-500 to-pink-500 border-transparent text-white shadow-lg shadow-purple-500/20' : 'bg-slate-900/60 border-white/5 text-slate-400 hover:text-slate-200'; ?>">
            Software Licenses
        </a>
        <a href="resources.php?tab=hardware" class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?php echo $typeTab === 'hardware' ? 'bg-gradient-to-r from-purple-500 to-pink-500 border-transparent text-white shadow-lg shadow-purple-500/20' : 'bg-slate-900/60 border-white/5 text-slate-400 hover:text-slate-200'; ?>">
            Hardware PCs
        </a>
        <a href="resources.php?tab=space" class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?php echo $typeTab === 'space' ? 'bg-gradient-to-r from-purple-500 to-pink-500 border-transparent text-white shadow-lg shadow-purple-500/20' : 'bg-slate-900/60 border-white/5 text-slate-400 hover:text-slate-200'; ?>">
            Office Space
        </a>
        <a href="resources.php?tab=equipment" class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?php echo $typeTab === 'equipment' ? 'bg-gradient-to-r from-purple-500 to-pink-500 border-transparent text-white shadow-lg shadow-purple-500/20' : 'bg-slate-900/60 border-white/5 text-slate-400 hover:text-slate-200'; ?>">
            Equipment
        </a>
    </div>

    <!-- Cards Grid -->
    <?php if (count($filteredResources) === 0): ?>
        <div class="text-center py-20 rounded-2xl glass-dark border border-white/5">
            <p class="text-slate-400 text-lg font-medium">No resources found</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($filteredResources as $index => $res): 
                $cardColor = $colors[$index % count($colors)];
                $allocatedQty = $res['total_quantity'] - $res['available_quantity'];
                $utilizationRate = $res['total_quantity'] > 0 ? (int)round(($allocatedQty / $res['total_quantity']) * 100) : 0;
            ?>
                <div class="glass-premium rounded-2xl p-6 hover-lift hover-glow-<?php echo $cardColor; ?> border border-white/5 flex flex-col justify-between transition-all duration-300 group">
                    <div>
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-50 group-hover:text-pink-400 transition-colors leading-tight">
                                    <?php echo htmlspecialchars($res['name']); ?>
                                </h3>
                                <span class="inline-block px-2 py-0.5 mt-1 rounded bg-slate-800 border border-slate-700/50 text-[9px] font-semibold text-slate-400 uppercase tracking-wide">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $res['resource_type'])); ?>
                                </span>
                            </div>
                            <?php echo render_status_badge($res['status'], 'sm'); ?>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-400 leading-normal mb-5"><?php echo htmlspecialchars($res['description']); ?></p>

                        <!-- Utilization ratio progress -->
                        <div class="space-y-1.5 mb-5 text-xs">
                            <div class="flex justify-between items-center text-[10px] text-slate-400 uppercase tracking-wide">
                                <span>Allocated ratio</span>
                                <span class="font-bold text-slate-200"><?php echo $utilizationRate; ?>%</span>
                            </div>
                            <?php echo render_workload_bar($utilizationRate, 'sm'); ?>
                        </div>
                    </div>

                    <!-- Footer Statistics -->
                    <div class="space-y-3 pt-4 border-t border-slate-800/80">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500">Inventory Allocation</span>
                            <span class="font-bold text-slate-200"><?php echo $allocatedQty; ?> / <?php echo $res['total_quantity']; ?> allocated</span>
                        </div>
                        <?php if ($res['cost_per_unit'] > 0): ?>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Unit Valuation</span>
                                <span class="font-mono font-bold text-slate-200">₹<?php echo number_format($res['cost_per_unit']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
