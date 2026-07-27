import Foundation

@MainActor
final class AppSession: ObservableObject {
    @Published var authenticated = KeychainStore.read() != nil
    @Published var deepLink: URL?

    func login(email: String, password: String) async throws {
        let token = try await APIClient.login(email: email, password: password)
        KeychainStore.save(token)
        authenticated = true
    }

    func open(_ url: URL) {
        if url.scheme == "luzicity", url.host == "news", let slug = url.pathComponents.last {
            deepLink = Config.webBaseURL.appending(path: "noticias/\(slug)")
        } else if url.scheme == "https" {
            deepLink = url
        }
    }
}
