import 'package:flutter/material.dart';

class AppColors {
  const AppColors._();
  static const background = Color(0xff100a12);
  static const surface = Color(0xff1b1020);
  static const surfaceSoft = Color(0xff26172b);
  static const border = Color(0xff49324d);
  static const text = Color(0xfff7f7fb);
  static const muted = Color(0xffa7acc0);
  static const primary = Color(0xff67256a);
  static const secondary = Color(0xffffc107);
  static const onSecondary = Color(0xff231900);
  static const danger = Color(0xffff4d67);

  // Compatibility aliases used throughout the existing widgets.
  static const purple = primary;
  static const red = secondary;
}

ThemeData buildAppTheme() => ThemeData(
      brightness: Brightness.dark,
      useMaterial3: true,
      scaffoldBackgroundColor: AppColors.background,
      colorScheme: const ColorScheme.dark(
          primary: AppColors.primary,
          secondary: AppColors.secondary,
          surface: AppColors.surface),
      appBarTheme: const AppBarTheme(
          backgroundColor: Colors.transparent,
          foregroundColor: AppColors.text,
          elevation: 0),
      textTheme: const TextTheme(
        headlineLarge: TextStyle(
            color: AppColors.text, fontWeight: FontWeight.w800, height: 1.08),
        titleLarge:
            TextStyle(color: AppColors.text, fontWeight: FontWeight.w700),
        bodyLarge: TextStyle(color: AppColors.text, height: 1.45),
        bodyMedium: TextStyle(color: AppColors.muted, height: 1.4),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surfaceSoft,
        hintStyle: const TextStyle(color: AppColors.muted),
        labelStyle: const TextStyle(color: AppColors.muted),
        border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: AppColors.border)),
        enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: AppColors.border)),
        focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
      ),
      cardTheme: CardThemeData(
        color: AppColors.surface,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppColors.border)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(foregroundColor: Colors.white),
      ),
      navigationBarTheme: const NavigationBarThemeData(
        backgroundColor: AppColors.background,
        indicatorColor: Colors.transparent,
      ),
    );
