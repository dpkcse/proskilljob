class JobCategory {
  const JobCategory({required this.id, required this.name});

  final int id;
  final String name;

  factory JobCategory.fromJson(Map<String, dynamic> json) => JobCategory(
        id: int.tryParse('${json['id'] ?? 0}') ?? 0,
        name: '${json['name'] ?? ''}',
      );
}
