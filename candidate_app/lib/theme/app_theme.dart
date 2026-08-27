import 'package:flutter/material.dart';

class AppColors {
  const AppColors._();
  static const background = Color(0xff070b20);
  static const surface = Color(0xff10182f);
  static const surfaceSoft = Color(0xff151d36);
  static const border = Color(0xff27314b);
  static const text = Color(0xfff7f7fb);
  static const muted = Color(0xffa7acc0);
  static const red = Color(0xffff003d);
  static const purple = Color(0xff7434f4);
}

ThemeData buildAppTheme() => ThemeData(
      brightness: Brightness.dark,
      useMaterial3: true,
      scaffoldBackgroundColor: AppColors.background,
      colorScheme: const ColorScheme.dark(
          primary: AppColors.red,
          secondary: AppColors.purple,
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
            borderSide: const BorderSide(color: AppColors.purple, width: 1.5)),
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
