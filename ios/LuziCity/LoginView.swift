import SwiftUI

struct LoginView: View {
    @EnvironmentObject private var session: AppSession
    @State private var email = ""
    @State private var password = ""
    @State private var loading = false
    @State private var error: String?

    var body: some View {
        Form {
            Text("LuziCity").font(.largeTitle).bold()
            TextField("E-mail", text: $email).textContentType(.emailAddress).textInputAutocapitalization(.never)
            SecureField("Senha", text: $password).textContentType(.password)
            if let error { Text(error).foregroundStyle(.red) }
            Button(loading ? "Entrando…" : "Entrar") {
                loading = true
                Task {
                    do { try await session.login(email: email, password: password) }
                    catch { self.error = "Não foi possível entrar. Verifique os dados e a conexão." }
                    loading = false
                }
            }.disabled(loading || email.isEmpty || password.isEmpty)
        }
    }
}
