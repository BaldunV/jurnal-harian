import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../../core/theme/design_tokens.dart';
import '../../models/student.dart';
import '../journal/journal_dashboard_screen.dart';
import '../journal/journal_history_screen.dart';
import '../profile/profile_screen.dart';
import '../statistics/statistics_screen.dart';

/// Shared selected-tab index so any screen can switch the bottom navigation
/// (e.g. the dashboard "Lihat semua" history shortcut).
final shellTabProvider = NotifierProvider<ShellTabNotifier, int>(
  ShellTabNotifier.new,
);

class ShellTabNotifier extends Notifier<int> {
  @override
  int build() => 0;

  void setTab(int value) => state = value;
}

class AppShell extends ConsumerWidget {
  const AppShell({required this.student, super.key});

  final Student student;

  static const _destinations = <NavigationDestination>[
    NavigationDestination(
      icon: Icon(LucideIcons.bookOpen),
      selectedIcon: Icon(LucideIcons.bookOpen),
      label: 'Jurnal',
    ),
    NavigationDestination(
      icon: Icon(LucideIcons.history),
      selectedIcon: Icon(LucideIcons.history),
      label: 'Riwayat',
    ),
    NavigationDestination(
      icon: Icon(LucideIcons.pieChart),
      selectedIcon: Icon(LucideIcons.pieChart),
      label: 'Statistik',
    ),
    NavigationDestination(
      icon: Icon(LucideIcons.user),
      selectedIcon: Icon(LucideIcons.user),
      label: 'Profil',
    ),
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final selectedIndex = ref.watch(shellTabProvider);
    final pages = <Widget>[
      JournalDashboardScreen(student: student),
      const JournalHistoryScreen(),
      const StatisticsScreen(),
      const ProfileScreen(),
    ];

    return KeyedSubtree(
      key: const Key('authenticated_app_shell'),
      child: LayoutBuilder(
        builder: (context, constraints) {
          if (constraints.maxWidth >= 720) {
            final extendedRail = constraints.maxWidth >= 1040;
            return Scaffold(
              body: SafeArea(
                child: Row(
                  children: [
                    DecoratedBox(
                      decoration: BoxDecoration(
                        color: Theme.of(context)
                            .colorScheme
                            .surfaceContainerLowest,
                        border: Border(
                          right: BorderSide(
                            color: Theme.of(context).colorScheme.outlineVariant,
                          ),
                        ),
                      ),
                      child: NavigationRail(
                        extended: extendedRail,
                        minExtendedWidth: 244,
                        labelType: extendedRail
                            ? null
                            : NavigationRailLabelType.all,
                        backgroundColor: Colors.transparent,
                        indicatorColor: Theme.of(context)
                            .colorScheme
                            .primaryContainer,
                        selectedIconTheme: IconThemeData(
                          color: Theme.of(context).colorScheme.primary,
                          size: 24,
                        ),
                        selectedLabelTextStyle: Theme.of(context)
                            .textTheme
                            .labelMedium
                            ?.copyWith(
                              color: Theme.of(context).colorScheme.primary,
                              fontWeight: FontWeight.w800,
                            ),
                        selectedIndex: selectedIndex,
                        onDestinationSelected: (index) =>
                            ref.read(shellTabProvider.notifier).setTab(index),
                        leading: Padding(
                          padding: const EdgeInsets.symmetric(vertical: 18),
                          child: extendedRail
                              ? const _RailBrand()
                              : const _RailLogo(),
                        ),
                        destinations: _destinations
                            .map(
                              (destination) => NavigationRailDestination(
                                icon: destination.icon,
                                selectedIcon: destination.selectedIcon,
                                label: Text(destination.label),
                              ),
                            )
                            .toList(),
                      ),
                    ),
                    Expanded(
                      child: IndexedStack(
                        index: selectedIndex,
                        children: pages,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }

          return Scaffold(
            body: IndexedStack(index: selectedIndex, children: pages),
            bottomNavigationBar: _MobileNavigation(
              selectedIndex: selectedIndex,
              onDestinationSelected: (index) =>
                  ref.read(shellTabProvider.notifier).setTab(index),
            ),
          );
        },
      ),
    );
  }
}

class _MobileNavigation extends StatelessWidget {
  const _MobileNavigation({
    required this.selectedIndex,
    required this.onDestinationSelected,
  });

  final int selectedIndex;
  final ValueChanged<int> onDestinationSelected;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;

    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceContainerLowest,
        border: Border(top: BorderSide(color: colors.outlineVariant)),
        boxShadow: <BoxShadow>[
          BoxShadow(
            color: colors.shadow.withValues(alpha: 0.08),
            blurRadius: 24,
            offset: const Offset(0, -8),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(AppRadius.lg),
            child: NavigationBar(
              height: 68,
              backgroundColor: colors.surfaceContainerLowest,
              selectedIndex: selectedIndex,
              onDestinationSelected: onDestinationSelected,
              destinations: AppShell._destinations,
            ),
          ),
        ),
      ),
    );
  }
}

class _RailBrand extends StatelessWidget {
  const _RailBrand();

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Image.asset(
          'assets/images/logo.png',
          width: 40,
          height: 40,
          semanticLabel: 'Logo SMK BPPI',
        ),
        const SizedBox(width: 10),
        Text(
          'Jurnal SMK BPPI',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
            color: Theme.of(context).colorScheme.primary,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }
}

class _RailLogo extends StatelessWidget {
  const _RailLogo();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 48,
      height: 48,
      padding: const EdgeInsets.all(6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(color: Theme.of(context).colorScheme.outlineVariant),
      ),
      child: Image.asset(
        'assets/images/logo.png',
        semanticLabel: 'Logo SMK BPPI',
      ),
    );
  }
}
