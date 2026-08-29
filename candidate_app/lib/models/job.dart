class Job {
  const Job({
    required this.id,
    required this.slug,
    required this.title,
    required this.company,
    required this.location,
    required this.type,
    required this.salary,
    required this.deadline,
    required this.logo,
    required this.bookmarked,
  });

  final int id;
  final String slug;
  final String title;
  final String company;
  final String location;
  final String type;
  final String salary;
  final String deadline;
  final String logo;
  final bool bookmarked;

  factory Job.fromJson(Map<String, dynamic> json) => Job(
        id: int.tryParse('${json['id'] ?? 0}') ?? 0,
        slug: '${json['slug'] ?? ''}',
        title: '${json['title'] ?? ''}',
        company: '${json['company_name'] ?? ''}',
        location: '${json['country'] ?? ''}',
        type: '${json['job_type'] ?? ''}',
        salary: '${json['salary'] ?? ''}',
        deadline: '${json['deadline'] ?? ''}',
        logo: '${json['company_logo'] ?? ''}',
        bookmarked: json['bookmarked'] == true || json['bookmarked'] == 1,
      );

  Job copyWith({bool? bookmarked}) => Job(
        id: id,
        slug: slug,
        title: title,
        company: company,
        location: location,
        type: type,
        salary: salary,
        deadline: deadline,
        logo: logo,
        bookmarked: bookmarked ?? this.bookmarked,
      );
}
