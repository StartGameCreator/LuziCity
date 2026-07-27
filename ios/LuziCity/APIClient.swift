import Foundation
import UIKit

enum APIClient {
    struct TokenResponse: Decodable { let token: String }

    static func request(path: String, method: String = "GET", token: String? = nil, json: [String: Any]? = nil) async throws -> (Data, HTTPURLResponse) {
        var request = URLRequest(url: Config.apiBaseURL.appending(path: path))
        request.httpMethod = method
        request.timeoutInterval = 15
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        if let token { request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization") }
        if let json { request.httpBody = try JSONSerialization.data(withJSONObject: json) }
        let (data, response) = try await URLSession.shared.data(for: request)
        guard let http = response as? HTTPURLResponse else { throw URLError(.badServerResponse) }
        return (data, http)
    }

    static func login(email: String, password: String) async throws -> String {
        let (data, response) = try await request(path: "mobile/auth/tokens", method: "POST", json: [
            "email": email, "password": password, "name": "iOS",
            "abilities": ["mobile:read", "mobile:write"], "expires_in_days": 30
        ])
        guard response.statusCode == 201 else { throw URLError(.userAuthenticationRequired) }
        return try JSONDecoder().decode(TokenResponse.self, from: data).token
    }

    static func registerDevice(_ firebaseToken: String) async {
        guard let token = KeychainStore.read() else { return }
        _ = try? await request(path: "mobile/notifications/devices", method: "POST", token: token, json: [
            "token": firebaseToken, "device_name": UIDevice.current.name, "platform": "ios"
        ])
    }
}
