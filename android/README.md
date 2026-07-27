# LuziCity Android

Aplicativo nativo Kotlin da fase 18.2, com login pela API mobile, push FCM, App Links/deep links e cache offline.

## Configuração

Crie `~/.gradle/gradle.properties` ou `android/gradle.properties` local com:

```properties
LUZICITY_API_URL=https://seu-dominio.example
LUZICITY_WEB_URL=https://seu-dominio.example
FIREBASE_API_KEY=
FIREBASE_APP_ID=
FIREBASE_PROJECT_ID=
FIREBASE_SENDER_ID=
```

Troque `luzicity.com` no `AndroidManifest.xml` pelo domínio de produção. No servidor, configure:

```dotenv
ANDROID_APP_PACKAGE=com.luzicity.app
ANDROID_SHA256_FINGERPRINTS=AA:BB:CC:...
```

O arquivo de associação será publicado em `/.well-known/assetlinks.json`.

## Build

Abra a pasta `android` no Android Studio, instale o SDK 35 e gere uma chave de assinatura. O APK/AAB de release somente deve ser criado após preencher Firebase, domínio e fingerprint SHA-256.

## Comportamento offline

O WebView preserva recursos HTTP já visitados no cache. Se a navegação principal falhar, exibe `assets/offline.html`; após reconectar, reabrir um link ou o aplicativo volta ao conteúdo online.
