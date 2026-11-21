<?php
// teams.php
require_once __DIR__ . '/includes/header.php';

$teams = get_teams();
$colors = ['cyan', 'purple', 'pink', 'teal'];
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold font-outfit text-slate-50 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 via-blue-400 to-indigo-400">
                Teams
            </h1>
            <p class="text-slate-300 text-xs mt-1">Browse functional design and construction oversight teams</p>
        </div>
    </div>

    <!-- Teams Grid -->
    <?php if (count($teams) === 0): ?>
        <div class="text-center py-20 rounded-2xl glass-dark border border-white/5">
            <p class="text-slate-400 text-lg font-medium">No team allocations found</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php foreach ($teams as $idx => $team): 
                $cardColor = $colors[$idx % count($colors)];
                $lead = $team['team_lead'];
                $proj = $team['project'];
                $members = $team['members'] ?? [];
            ?>
                <div class="glass-premium rounded-2xl p-6 hover-glow-<?php echo $cardColor; ?> border border-white/5 flex flex-col justify-between transition-all duration-300 group">
                    <div>
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-50 group-hover:text-cyan-400 transition-colors leading-tight">
                                    <?php echo htmlspecialchars($team['name']); ?>
                                </h3>
                                <p class="text-xs text-slate-400 mt-1 leading-normal"><?php echo htmlspecialchars($team['description']); ?></p>
                            </div>
                        </div>

                        <!-- Project Link -->
                        <div class="mb-5 p-3 rounded-xl bg-slate-900/60 border border-white/5 flex items-center gap-2">
                            <i data-lucide="folder-kanban" class="w-4 h-4 text-cyan-400 flex-shrink-0"></i>
                            <div class="text-[10px]">
                                <p class="text-slate-500 leading-none">Linked Project</p>
                                <p class="font-bold text-slate-200 mt-1">
                                    <?php echo htmlspecialchars($proj['name'] ?? 'Direct Core Operations'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Team Lead info -->
                        <div class="mb-6 border-t border-slate-800/80 pt-4">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Team Coordinator</h4>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl overflow-hidden border border-cyan-500/20 relative flex-shrink-0">
                                    <?php if ($lead['profile_photo_url']): ?>
                                        <img src="<?php echo htmlspecialchars($lead['profile_photo_url']); ?>" alt="Lead avatar" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-slate-800 flex items-center justify-center text-slate-200 font-bold text-sm">
                                            <?php echo $lead['first_name'][0] . $lead['last_name'][0]; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-200"><?php echo htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']); ?></p>
                                    <p class="text-[10px] text-cyan-400 font-medium"><?php echo htmlspecialchars($lead['designation'] ?? 'Lead Architect'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Members list -->
                        <div class="border-t border-slate-800/80 pt-4">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Allocated Members (<?php echo count($members); ?>)</h4>
                            <div class="space-y-3">
                                <?php foreach ($members as $mem): 
                                    $emp = $mem['employee'];
                                ?>
                                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-900/20 border border-white/5 hover:bg-slate-900/40 transition-colors">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg overflow-hidden border border-white/10 relative flex-shrink-0">
                                                <?php if ($emp['profile_photo_url']): ?>
                                                    <img src="<?php echo htmlspecialchars($emp['profile_photo_url']); ?>" alt="Member avatar" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-full h-full bg-slate-800 flex items-center justify-center text-slate-200 font-bold text-xs">
                                                        <?php echo $emp['first_name'][0] . $emp['last_name'][0]; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-200 leading-tight"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></p>
                                                <p class="text-[9px] text-slate-500 mt-0.5"><?php echo htmlspecialchars($mem['role']); ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] font-bold text-cyan-400"><?php echo $emp['workload_percentage']; ?>% workload</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
