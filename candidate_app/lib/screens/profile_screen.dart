import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:url_launcher/url_launcher.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/brand_logo.dart';
import 'edit_profile_screen.dart';
import 'resume_manager_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({
    super.key,
    required this.state,
    required this.onLogin,
    required this.onOpenJobs,
  });

  final AppState state;
  final VoidCallback onLogin;
  final VoidCallback onOpenJobs;

  @override
  Widget build(BuildContext context) {
    if (!state.loggedIn) return _signedOut(context);
    final user = state.currentUser;
    final remaining = _number(
        state.dashboard['profileComplated'] ?? user['profile_complete']);
    final completion = (100 - remaining).clamp(0, 100);
    final photo = '${user['photo_url'] ?? ''}';
    final contact = user['contactinfo'] is Map
        ? (user['contactinfo'] as Map).cast<String, dynamic>()
        : <String, dynamic>{};
    final location = [user['district'], user['country']]
        .where((value) => value != null && '$value'.trim().isNotEmpty)
        .join(', ');

    return RefreshIndicator(
      onRefresh: state.loadProfile,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(
            parent: BouncingScrollPhysics()),
        padding: const EdgeInsets.fromLTRB(18, 24, 18, 32),
        children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            const BrandLogo(compact: true),
            Row(children: [
              const Text('My Profile',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(width: 6),
              IconButton(
                  tooltip: 'Edit profile',
                  onPressed: () => _openEditProfile(context),
                  icon:
                      const Icon(Icons.edit_outlined, color: AppColors.purple)),
            ]),
          ]),
          const SizedBox(height: 26),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xff151d3d), Color(0xff17102f)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: AppColors.border),
            ),
            child: Column(children: [
              Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
                _Avatar(photo: photo, name: '${user['name'] ?? ''}'),
                const SizedBox(width: 16),
                Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text('${user['name'] ?? 'Candidate'}',
                          style: const TextStyle(
                              fontSize: 23, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 4),
                      Text('${user['title'] ?? 'Job Seeker'}',
                          style: const TextStyle(color: AppColors.muted)),
                      if (location.isNotEmpty) ...[
                        const SizedBox(height: 7),
                        Row(children: [
                          const Icon(Icons.location_on_outlined,
                              size: 16, color: AppColors.muted),
                          const SizedBox(width: 4),
                          Expanded(
                              child: Text(location,
                                  style: const TextStyle(
                                      fontSize: 13, color: AppColors.muted))),
                        ]),
                      ],
                    ])),
                _CompletionRing(value: completion),
              ]),
              const SizedBox(height: 18),
              ClipRRect(
                borderRadius: BorderRadius.circular(5),
                child: LinearProgressIndicator(
                  minHeight: 7,
                  value: completion / 100,
                  backgroundColor: AppColors.border,
                  color: completion >= 80
                      ? const Color(0xff39d3a3)
                      : AppColors.purple,
                ),
              ),
              const SizedBox(height: 9),
              Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                const Text('Profile strength',
                    style: TextStyle(fontSize: 12, color: AppColors.muted)),
                Text(completion >= 80 ? 'Excellent' : 'Complete your profile',
                    style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.purple,
                        fontWeight: FontWeight.w600)),
              ]),
            ]),
          ),
          if (state.profileBusy) ...[
            const SizedBox(height: 14),
            const LinearProgressIndicator(color: AppColors.red),
          ],
          if (state.profileError != null) ...[
            const SizedBox(height: 14),
            _ErrorCard(
                message: state.profileError!, onRetry: state.loadProfile),
          ],
          const SizedBox(height: 18),
          Row(children: [
            _StatCard(
                icon: Icons.send_outlined,
                value: _number(state.dashboard['appliedJobs']),
                label: 'Applied'),
            const SizedBox(width: 10),
            _StatCard(
                icon: Icons.bookmark_border,
                value: _number(state.dashboard['favoriteJobs']),
                label: 'Saved'),
            const SizedBox(width: 10),
            _StatCard(
                icon: Icons.notifications_none,
                value: _number(state.dashboard['notifications']),
                label: 'Alerts'),
          ]),
          const SizedBox(height: 24),
          SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                  style: FilledButton.styleFrom(
                      backgroundColor: AppColors.purple,
                      padding: const EdgeInsets.all(14)),
                  onPressed: () => _openEditProfile(context),
                  icon: const Icon(Icons.edit_outlined),
                  label: const Text('Edit Profile'))),
          const SizedBox(height: 24),
          const _SectionTitle('Career'),
          _MenuCard(children: [
            _MenuItem(
                icon: Icons.description_outlined,
                title: 'My Resumes',
                subtitle: '${state.resumes.length} uploaded',
                onTap: () => _openResumes(context)),
            _MenuItem(
                icon: Icons.work_history_outlined,
                title: 'Applied Jobs',
                subtitle:
                    '${_number(state.dashboard['appliedJobs'])} applications',
                onTap: onOpenJobs),
            _MenuItem(
                icon: Icons.bookmark_outline,
                title: 'Saved Jobs',
                subtitle:
                    '${_number(state.dashboard['favoriteJobs'])} saved jobs',
                onTap: onOpenJobs),
          ]),
          const SizedBox(height: 22),
          const _SectionTitle('Personal information'),
          _MenuCard(children: [
            _InfoRow(
                icon: Icons.email_outlined,
                label: 'Email',
                value: '${user['email'] ?? 'Not added'}'),
            _InfoRow(
                icon: Icons.phone_outlined,
                label: 'Phone',
                value: '${contact['phone'] ?? 'Not added'}'),
            _InfoRow(
                icon: Icons.public_outlined,
                label: 'Location',
                value: location.isEmpty ? 'Not added' : location),
          ]),
          const SizedBox(height: 22),
          const _SectionTitle('Connect with Proskill Job'),
          _SocialLinksCard(onOpen: (url) => _openExternal(context, url)),
          const SizedBox(height: 22),
          const _SectionTitle('Account'),
          _MenuCard(children: [
            _MenuItem(
                icon: Icons.refresh,
                title: 'Refresh profile',
                subtitle: 'Sync the latest account data',
                onTap: state.loadProfile),
            _MenuItem(
                icon: Icons.logout,
                title: 'Log out',
                subtitle: 'Sign out from this device',
                danger: true,
                onTap: () => _confirmLogout(context)),
          ]),
        ],
      ),
    );
  }

  Widget _signedOut(BuildContext context) => Center(
        child: Padding(
          padding: const EdgeInsets.all(30),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Container(
                padding: const EdgeInsets.all(22),
                decoration: const BoxDecoration(
                    color: AppColors.surface, shape: BoxShape.circle),
                child: const Icon(Icons.person_outline,
                    size: 54, color: AppColors.purple)),
            const SizedBox(height: 20),
            const Text('Build your professional profile',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
            const SizedBox(height: 8),
            const Text('Log in to manage your CV, applications and saved jobs.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.muted)),
            const SizedBox(height: 22),
            SizedBox(
                width: double.infinity,
                child: FilledButton(
                    onPressed: onLogin,
                    child: const Padding(
                        padding: EdgeInsets.all(14), child: Text('Log In')))),
          ]),
        ),
      );

  Future<void> _openResumes(BuildContext context) async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => ResumeManagerScreen(state: state)),
    );
    await state.loadProfile();
  }

  Future<void> _openEditProfile(BuildContext context) async {
    await Navigator.push(context,
        MaterialPageRoute(builder: (_) => EditProfileScreen(state: state)));
    await state.loadProfile();
  }

  Future<void> _confirmLogout(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Log out?'),
        content: const Text('You will need to enter your credentials again.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancel')),
          FilledButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Log Out')),
        ],
      ),
    );
    if (confirmed == true) await state.logout();
  }

  Future<void> _openExternal(BuildContext context, String url) async {
    final opened = await launchUrl(
      Uri.parse(url),
      mode: LaunchMode.externalApplication,
    );
    if (!opened && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('লিংকটি এখন খোলা যাচ্ছে না।')),
      );
    }
  }

  int _number(dynamic value) => int.tryParse('${value ?? 0}') ?? 0;
}

class _SocialLinksCard extends StatelessWidget {
  const _SocialLinksCard({required this.onOpen});

  final ValueChanged<String> onOpen;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 17),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(17),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _SocialButton(
              label: 'Website',
              color: AppColors.purple,
              icon: const Icon(Icons.language_rounded,
                  color: AppColors.purple, size: 21),
              onTap: () => onOpen('https://proskilljob.com/'),
            ),
            _SocialButton(
              label: 'Facebook',
              color: const Color(0xff1877f2),
              icon: const FaIcon(FontAwesomeIcons.facebookF,
                  color: Color(0xff1877f2), size: 19),
              onTap: () => onOpen('https://www.facebook.com/proskilljob2026/'),
            ),
            _SocialButton(
              label: 'LinkedIn',
              color: const Color(0xff0a66c2),
              icon: const FaIcon(FontAwesomeIcons.linkedinIn,
                  color: Color(0xff0a66c2), size: 20),
              onTap: () =>
                  onOpen('https://www.linkedin.com/company/proskill-job-com'),
            ),
            _SocialButton(
              label: 'Instagram',
              color: const Color(0xffc13584),
              icon: const FaIcon(FontAwesomeIcons.instagram,
                  color: Color(0xffc13584), size: 21),
              onTap: () =>
                  onOpen('https://www.instagram.com/proskilljob02/?hl=en'),
            ),
          ],
        ),
      );
}

class _SocialButton extends StatelessWidget {
  const _SocialButton({
    required this.label,
    required this.color,
    required this.icon,
    required this.onTap,
  });

  final String label;
  final Color color;
  final Widget icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Semantics(
        button: true,
        label: 'Open Proskill Job $label',
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 3),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 45,
                  height: 45,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: color.withValues(alpha: .1),
                    border: Border.all(color: color.withValues(alpha: .25)),
                  ),
                  child: icon,
                ),
                const SizedBox(height: 7),
                Text(label,
                    style: const TextStyle(
                        color: AppColors.muted, fontSize: 10.5)),
              ],
            ),
          ),
        ),
      );
}

class _Avatar extends StatelessWidget {
  const _Avatar({required this.photo, required this.name});
  final String photo;
  final String name;
  @override
  Widget build(BuildContext context) {
    final valid = photo.startsWith('http');
    return CircleAvatar(
        radius: 38,
        backgroundColor: AppColors.purple,
        backgroundImage: valid
            ? NetworkImage(photo.replaceFirst('127.0.0.1', '10.0.2.2'))
            : null,
        child: valid
            ? null
            : Text(name.isEmpty ? 'P' : name[0].toUpperCase(),
                style: const TextStyle(
                    fontSize: 28, fontWeight: FontWeight.w800)));
  }
}

class _CompletionRing extends StatelessWidget {
  const _CompletionRing({required this.value});
  final int value;
  @override
  Widget build(BuildContext context) => SizedBox(
      width: 58,
      height: 58,
      child: Stack(alignment: Alignment.center, children: [
        SizedBox(
            width: 58,
            height: 58,
            child: CircularProgressIndicator(
                value: value / 100,
                strokeWidth: 5,
                backgroundColor: AppColors.border,
                color: AppColors.purple)),
        Text('$value%',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
      ]));
}

class _StatCard extends StatelessWidget {
  const _StatCard(
      {required this.icon, required this.value, required this.label});
  final IconData icon;
  final int value;
  final String label;
  @override
  Widget build(BuildContext context) => Expanded(
      child: Container(
          padding: const EdgeInsets.symmetric(vertical: 15),
          decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(15),
              border: Border.all(color: AppColors.border)),
          child: Column(children: [
            Icon(icon, color: AppColors.red, size: 21),
            const SizedBox(height: 6),
            Text('$value',
                style:
                    const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
            Text(label,
                style: const TextStyle(fontSize: 12, color: AppColors.muted))
          ])));
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);
  final String text;
  @override
  Widget build(BuildContext context) => Padding(
      padding: const EdgeInsets.only(left: 3, bottom: 10),
      child: Text(text,
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)));
}

class _MenuCard extends StatelessWidget {
  const _MenuCard({required this.children});
  final List<Widget> children;
  @override
  Widget build(BuildContext context) => Container(
      decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(17),
          border: Border.all(color: AppColors.border)),
      child: Column(children: [
        for (var i = 0; i < children.length; i++) ...[
          children[i],
          if (i < children.length - 1)
            const Divider(height: 1, indent: 58, color: AppColors.border)
        ]
      ]));
}

class _MenuItem extends StatelessWidget {
  const _MenuItem(
      {required this.icon,
      required this.title,
      required this.subtitle,
      required this.onTap,
      this.danger = false});
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  final bool danger;
  @override
  Widget build(BuildContext context) => ListTile(
      onTap: onTap,
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 3),
      leading: Icon(icon, color: danger ? AppColors.red : AppColors.purple),
      title: Text(title,
          style: TextStyle(
              color: danger ? AppColors.red : AppColors.text,
              fontWeight: FontWeight.w600)),
      subtitle: Text(subtitle,
          style: const TextStyle(fontSize: 12, color: AppColors.muted)),
      trailing: const Icon(Icons.chevron_right, color: AppColors.muted));
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(
      {required this.icon, required this.label, required this.value});
  final IconData icon;
  final String label;
  final String value;
  @override
  Widget build(BuildContext context) => Padding(
      padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 14),
      child: Row(children: [
        Icon(icon, color: AppColors.purple),
        const SizedBox(width: 15),
        Expanded(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label,
              style: const TextStyle(fontSize: 12, color: AppColors.muted)),
          const SizedBox(height: 2),
          Text(value, maxLines: 1, overflow: TextOverflow.ellipsis)
        ])),
      ]));
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;
  @override
  Widget build(BuildContext context) => Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
          color: const Color(0xff351625),
          borderRadius: BorderRadius.circular(12)),
      child: Row(children: [
        const Icon(Icons.error_outline, color: AppColors.red),
        const SizedBox(width: 10),
        Expanded(child: Text(message, style: const TextStyle(fontSize: 12))),
        TextButton(onPressed: onRetry, child: const Text('Retry'))
      ]));
}
