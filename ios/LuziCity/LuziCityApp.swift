import SwiftUI

@main
struct LuziCityApp: App {
    @UIApplicationDelegateAdaptor(AppDelegate.self) private var delegate
    @StateObject private var session = AppSession()

    var body: some Scene {
        WindowGroup {
            Group {
                if session.authenticated {
                    WebPortalView(url: session.deepLink ?? Config.webBaseURL)
                } else {
                    LoginView()
                }
            }
            .environmentObject(session)
            .onOpenURL { session.open($0) }
            .onContinueUserActivity(NSUserActivityTypeBrowsingWeb) {
                if let url = $0.webpageURL { session.open(url) }
            }
            .onReceive(NotificationCenter.default.publisher(for: .openLuziCityURL)) {
                if let url = $0.object as? URL { session.open(url) }
            }
        }
    }
}
