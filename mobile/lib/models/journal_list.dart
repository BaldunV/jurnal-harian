import 'journal.dart';

class JournalPage {
  const JournalPage({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  factory JournalPage.fromJson(Map<String, Object?> json) {
    final rawItems = json['data'];
    final items = rawItems is List
        ? rawItems
              .whereType<Map<Object?, Object?>>()
              .map(
                (item) => Journal.fromJson(
                  item.map((key, value) => MapEntry(key.toString(), value)),
                ),
              )
              .toList()
        : <Journal>[];

    final rawMeta = json['meta'];
    final meta = rawMeta is Map<Object?, Object?>
        ? rawMeta.map((key, value) => MapEntry(key.toString(), value))
        : const <String, Object?>{};

    final currentPage = meta['current_page'];
    final lastPage = meta['last_page'];
    final total = meta['total'];

    return JournalPage(
      items: items,
      currentPage: currentPage is num ? currentPage.toInt() : 1,
      lastPage: lastPage is num ? lastPage.toInt() : 1,
      total: total is num ? total.toInt() : items.length,
    );
  }

  final List<Journal> items;
  final int currentPage;
  final int lastPage;
  final int total;

  bool get hasMore => currentPage < lastPage;
}
