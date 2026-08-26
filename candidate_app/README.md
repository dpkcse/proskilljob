# ProSkill Candidate App

Flutter candidate app for the ProSkill Laravel API.

## One-time setup

1. Install Flutter stable (Flutter 3.22+ / Dart 3.3+), Android Studio and Xcode (for iOS).
2. From this folder, generate platform projects without replacing `lib`:

   `flutter create --platforms=android,ios .`

3. Install packages:

   `flutter pub get`

4. Start Laravel from the repository root:

   `php artisan serve --host=0.0.0.0 --port=8000`

5. Run the app using the appropriate API URL:

   Android emulator:
   `flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api`

   iOS simulator:
   `flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api`

   Physical phone (same Wi-Fi):
   `flutter run --dart-define=API_BASE_URL=http://YOUR_COMPUTER_LAN_IP:8000/api`

   Production:
   `flutter run --release --dart-define=API_BASE_URL=https://YOUR_DOMAIN.com/api`

   When `API_BASE_URL` is omitted, release builds use
   `https://proskilljob.com/api` by default.

## Production requirements

- HTTPS-enabled public Laravel domain
- Laravel `.env` with correct `APP_URL`, database, mail and queue settings
- Sanctum tables migrated and API routes reachable
- Android package ID and iOS bundle ID
- App name, icon, splash screen and privacy policy URL
- Firebase projects/config files if push notifications are enabled
- Apple Developer and Google Play Console accounts for store release

Never place database, mail, Firebase server or Laravel secrets in this app.
