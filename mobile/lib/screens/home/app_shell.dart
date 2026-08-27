import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../models/student.dart';
import '../../providers/session_provider.dart';

class AppShell extends ConsumerStatefulWidget {
  const AppShell({required this.student, super.key});

  final Student student;

  @override
  ConsumerState<AppShell> createState() => _AppShellState();
}

class _AppShellState extends ConsumerState<AppShell> {
  static const _destinations = <NavigationDestination>[
    NavigationDestination(
      icon: Icon(Icons.home_outlined),
      selectedIcon: Icon(Icons.home_rounded),
      label: 'Beranda',
    ),
    NavigationDestination(
      icon: Icon(Icons.edit_note_outlined),
      selectedIcon: Icon(Icons.edit_note_rounded),
      label: 'Jurnal',
    ),
    NavigationDestination(
      icon: Icon(Icons.insights_outlined),
      selectedIcon: Icon(Icons.insights_rounded),
      label: 'Statistik',
    ),
    NavigationDestination(
      icon: Icon(Icons.person_outline_rounded),
      selectedIcon: Icon(Icons.person_rounded),
      label: 'Profil',
    ),
  ];

  static const _titles = <String>['Beranda', 'Jurnal', 'Statistik', 'Profil'];

  int _selectedIndex = 0;
  bool _isLoggingOut = false;

  @override
  Widget build(BuildContext context) {
    final pages = <Widget>[
      _HomePage(student: widget.student),
      const _ComingSoonPage(
        icon: Icons.edit_note_rounded,
        title: 'Jurnal harian',
        description:
            'Pencatatan tujuh kebiasaan akan hadir pada tahap berikutnya.',
      ),
      const _ComingSoonPage(
        icon: Icons.insights_rounded,
        title: 'Statistik kebiasaan',
        description:
            'Ringkasan kemajuan akan ditampilkan dari data resmi sekolah.',
      ),
      _ProfilePage(
        student: widget.student,
        isLoggingOut: _isLoggingOut,
        onLogout: _logout,
      ),
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
                      selectedIndex: _selectedIndex,
                      onDestinationSelected: _selectDestination,
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
                      child: Column(
                        children: [
                          _PageHeader(title: _titles[_selectedIndex]),
                          Expanded(
                            child: IndexedStack(
                              index: _selectedIndex,
                              children: pages,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          }

          return Scaffold(
            appBar: AppBar(
              title: Text(_titles[_selectedIndex]),
              actions: const [
                Padding(
                  padding: EdgeInsets.only(right: 16),
                  child: _AppBarBrand(),
                ),
              ],
            ),
            body: IndexedStack(index: _selectedIndex, children: pages),
            bottomNavigationBar: NavigationBar(
              selectedIndex: _selectedIndex,
              onDestinationSelected: _selectDestination,
              destinations: _destinations,
            ),
          );
        },
      ),
    );
  }

  void _selectDestination(int index) {
    setState(() => _selectedIndex = index);
  }

  Future<void> _logout() async {
    if (_isLoggingOut) {
      return;
    }

    setState(() => _isLoggingOut = true);
    try {
      await ref.read(sessionControllerProvider.notifier).logout();
    } on Object {
      if (mounted) {
        setState(() => _isLoggingOut = false);
      }
    }
  }
}

class _HomePage extends StatelessWidget {
  const _HomePage({required this.student});

  final Student student;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final firstName = student.name.trim().split(RegExp(r'\s+')).first;

    return ListView(
      key: const PageStorageKey<String>('home_page'),
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
      children: [
        Text(
          'Halo, $firstName.',
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        const SizedBox(height: 8),
        Text(
          'Sesi akun sekolahmu sudah tervalidasi. Jurnal akan selalu mengikuti tanggal resmi sistem sekolah.',
          style: Theme.of(context).textTheme.bodyLarge,
        ),
        const SizedBox(height: 28),
        DecoratedBox(
          decoration: BoxDecoration(
            color: colors.primaryContainer,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 25,
                  backgroundColor: colors.primary,
                  foregroundColor: colors.onPrimary,
                  child: Text(
                    _initials(student.name),
                    style: Theme.of(context).textTheme.titleMedium
                        ?.copyWith(color: colors.onPrimary),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        student.name,
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(color: colors.onPrimaryContainer),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${student.className}  •  NIS ${student.nis}',
                        style: Theme.of(context).textTheme.bodyMedium
                            ?.copyWith(color: colors.onPrimaryContainer),
                      ),
                    ],
                  ),
                ),
                Icon(
                  Icons.verified_user_rounded,
                  color: colors.onPrimaryContainer,
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 28),
        Text(
          'Akses siswa siap',
          style: Theme.of(context).textTheme.titleMedium,
        ),
        const SizedBox(height: 8),
        Text(
          'Gunakan navigasi di bawah untuk berpindah antarbagian. Data jurnal, statistik, dan profil akan disambungkan pada tahap fiturnya masing-masing.',
          style: Theme.of(context).textTheme.bodyMedium,
        ),
      ],
    );
  }
}

class _ProfilePage extends StatelessWidget {
  const _ProfilePage({
    required this.student,
    required this.isLoggingOut,
    required this.onLogout,
  });

  final Student student;
  final bool isLoggingOut;
  final VoidCallback onLogout;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;

    return ListView(
      key: const PageStorageKey<String>('profile_page'),
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
      children: [
        CircleAvatar(
          radius: 38,
          backgroundColor: colors.primaryContainer,
          foregroundColor: colors.onPrimaryContainer,
          child: Text(
            _initials(student.name),
            style: Theme.of(context).textTheme.headlineSmall
                ?.copyWith(color: colors.onPrimaryContainer),
          ),
        ),
        const SizedBox(height: 16),
        Text(
          student.name,
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        const SizedBox(height: 4),
        Text(
          student.className,
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.bodyLarge,
        ),
        const SizedBox(height: 32),
        _ProfileRow(label: 'NIS', value: student.nis),
        const Divider(height: 1),
        _ProfileRow(label: 'Kelas', value: student.className),
        const Divider(height: 1),
        _ProfileRow(label: 'Peran', value: 'Siswa'),
        const SizedBox(height: 32),
        OutlinedButton.icon(
          key: const Key('logout_button'),
          onPressed: isLoggingOut ? null : onLogout,
          style: OutlinedButton.styleFrom(
            foregroundColor: colors.error,
            side: BorderSide(color: colors.error),
          ),
          icon: isLoggingOut
              ? const SizedBox.square(
                  dimension: 18,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : const Icon(Icons.logout_rounded),
          label: Text(isLoggingOut ? 'Sedang keluar' : 'Keluar dari akun'),
        ),
      ],
    );
  }
}

class _ProfileRow extends StatelessWidget {
  const _ProfileRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Row(
        children: [
          SizedBox(
            width: 92,
            child: Text(label, style: Theme.of(context).textTheme.bodyMedium),
          ),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: Theme.of(context).textTheme.titleMedium,
            ),
          ),
        ],
      ),
    );
  }
}

class _ComingSoonPage extends StatelessWidget {
  const _ComingSoonPage({
    required this.icon,
    required this.title,
    required this.description,
  });

  final IconData icon;
  final String title;
  final String description;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;

    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(32),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Column(
            children: [
              Icon(icon, size: 64, color: colors.primary),
              const SizedBox(height: 24),
              Text(
                title,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.headlineSmall,
              ),
              const SizedBox(height: 10),
              Text(
                description,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodyLarge,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PageHeader extends StatelessWidget {
  const _PageHeader({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 72,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24),
        child: Row(
          children: [
            Text(title, style: Theme.of(context).textTheme.titleLarge),
            const Spacer(),
            const _AppBarBrand(),
          ],
        ),
      ),
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
        Text('Jurnal SMK BPPI', style: Theme.of(context).textTheme.titleMedium),
      ],
    );
  }
}

String _initials(String name) {
  final parts = name
      .trim()
      .split(RegExp(r'\s+'))
      .where((part) => part.isNotEmpty)
      .toList();
  if (parts.isEmpty) {
    return 'S';
  }
  if (parts.length == 1) {
    return parts.first.substring(0, 1).toUpperCase();
  }

  return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
}
