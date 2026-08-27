import 'package:flutter/material.dart';

class BrandLogo extends StatelessWidget {
  const BrandLogo({super.key, this.compact = false});
  final bool compact;
  @override
  Widget build(BuildContext context) => Image.asset(
        'assets/branding/proskill_job_logo.png',
        width: compact ? 130 : 164,
        height: compact ? 26 : 34,
        fit: BoxFit.contain,
        alignment: Alignment.centerLeft,
        filterQuality: FilterQuality.high,
        semanticLabel: 'ProSkill Job',
      );
}
