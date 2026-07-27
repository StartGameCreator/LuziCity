import SwiftUI
import WebKit

struct WebPortalView: UIViewRepresentable {
    let url: URL

    func makeCoordinator() -> Coordinator { Coordinator() }
    func makeUIView(context: Context) -> WKWebView {
        let configuration = WKWebViewConfiguration()
        configuration.websiteDataStore = .default()
        let view = WKWebView(frame: .zero, configuration: configuration)
        view.navigationDelegate = context.coordinator
        view.load(URLRequest(url: url, cachePolicy: .returnCacheDataElseLoad, timeoutInterval: 15))
        return view
    }
    func updateUIView(_ view: WKWebView, context: Context) {
        if view.url != url { view.load(URLRequest(url: url, cachePolicy: .returnCacheDataElseLoad)) }
    }

    final class Coordinator: NSObject, WKNavigationDelegate {
        func webView(_ webView: WKWebView, didFail navigation: WKNavigation!, withError error: Error) { showOffline(webView) }
        func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) { showOffline(webView) }
        private func showOffline(_ webView: WKWebView) {
            guard let file = Bundle.main.url(forResource: "offline", withExtension: "html") else { return }
            webView.loadFileURL(file, allowingReadAccessTo: file.deletingLastPathComponent())
        }
    }
}
