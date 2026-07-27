# LuziCity iOS

Aplicativo SwiftUI da fase 18.3 com autenticação pela API Mobile, token no Keychain, Firebase Cloud Messaging/APNs, Universal Links, deep links e cache offline.

## Gerar o projeto

1. Instale Xcode 16 e [XcodeGen](https://github.com/yonaskolb/XcodeGen).
2. Em `ios`, execute `xcodegen generate`.
3. Abra `LuziCity.xcodeproj`.
4. Selecione a equipe Apple e ajuste o bundle ID.
5. Adicione o `GoogleService-Info.plist` do app iOS em `ios/LuziCity`.
6. Ative Push Notifications e Associated Domains no Apple Developer.

Atualize os domínios em `Config.swift` e `LuziCity.entitlements`. No servidor:

```dotenv
IOS_TEAM_ID=ABCDE12345
IOS_BUNDLE_ID=com.luzicity.app
```

O arquivo de Universal Links estará em `/.well-known/apple-app-site-association`.

## Push

O Firebase entrega o token FCM, associado ao token APNs, e o aplicativo o registra em `/api/v1/mobile/notifications/devices` com plataforma `ios`.

## Offline

O `WKWebView` usa `returnCacheDataElseLoad`. Quando a navegação falha, o aplicativo exibe `offline.html` embarcado.
