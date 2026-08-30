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
            return Scaffold(
              body: SafeArea(
                child: Row(
                  children: [
                    NavigationRail(
                      extended: true,
                      backgroundColor: Theme.of(context).colorScheme.surface,
                      selectedIndex: selectedIndex,
                      onDestinationSelected: (index) =>
                          ref.read(shellTabProvider.notifier).setTab(index),
                      leading: const Padding(
                        padding: EdgeInsets.symmetric(vertical: 16),
                        child: _RailBrand(),
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
                    const VerticalDivider(width: 1),
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
            appBar: AppBar(
              title: const Text('Jurnal SMK BPPI'),
              actions: const [
                Padding(
                  padding: EdgeInsets.only(right: 16),
                  child: _AppBarBrand(),
                ),
              ],
            ),
            body: IndexedStack(index: selectedIndex, children: pages),
            bottomNavigationBar: NavigationBar(
              height: 72,
              selectedIndex: selectedIndex,
              onDestinationSelected: (index) =>
                  ref.read(shellTabProvider.notifier).setTab(index),
              destinations: _destinations,
            ),
          );
        },
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
            color: AppColors.primary700,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }
}

class _AppBarBrand extends StatelessWidget {
  const _AppBarBrand();

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/logo.png',
      width: 36,
      height: 36,
      semanticLabel: 'Logo SMK BPPI',
    );
  }
}
